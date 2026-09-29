<?php

require_once("../../include/initialize.php");
/* Every action here changes data, so a login is required. */
confirm_logged_in();

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';

switch ($action) {
	case 'add' :
		doInsert();
		break;

	case 'edit' :
		doEdit();
		break;

	case 'delete' :
		doDelete();
		break;

	case 'consult' :
		doConsult();
		break;
}

function doInsert(){
	$doctor = new DoctorProfile();

	$FULLNAME = trim($_POST['FULLNAME']);
	if ($FULLNAME == '') {
		message("Full name is required!", "error");
		redirect('index.php');
		return;
	}

	$doctor->FULLNAME       = $FULLNAME;
	$doctor->SPECIALIZATION = isset($_POST['SPECIALIZATION']) ? trim($_POST['SPECIALIZATION']) : '';
	$doctor->LICENSE_NO     = isset($_POST['LICENSE_NO']) ? trim($_POST['LICENSE_NO']) : '';
	$doctor->CONTACT_NO     = isset($_POST['CONTACT_NO']) ? trim($_POST['CONTACT_NO']) : '';
	$doctor->SCHEDULE_DAYS  = isset($_POST['SCHEDULE_DAYS']) ? trim($_POST['SCHEDULE_DAYS']) : '';
	$doctor->SCHEDULE_TIME  = isset($_POST['SCHEDULE_TIME']) ? trim($_POST['SCHEDULE_TIME']) : '';
	$doctor->STATUS         = isset($_POST['STATUS']) ? $_POST['STATUS'] : 'Active';

	if (isset($_POST['UID']) && $_POST['UID'] !== '') {
		$doctor->UID = intval($_POST['UID']);
	}
	if (isset($_SESSION['UID'])) {
		$doctor->ADDEDBY = intval($_SESSION['UID']);
	}
	$doctor->DATEADDED = date('Y-m-d');

	$istrue = $doctor->create();
	if ($istrue == true) {
		message("Doctor profile [". $FULLNAME ."] has been created successfully!", "success");
	} else {
		message("No doctor profile has been created.", "error");
	}
	redirect('index.php');
}

function doEdit(){
	$doctor = new DoctorProfile();

	$DOCTOR_ID = $_POST['DOCTOR_ID'];
	$FULLNAME  = trim($_POST['FULLNAME']);
	if ($FULLNAME == '') {
		message("Full name is required!", "error");
		redirect('index.php');
		return;
	}

	$doctor->FULLNAME       = $FULLNAME;
	$doctor->SPECIALIZATION = isset($_POST['SPECIALIZATION']) ? trim($_POST['SPECIALIZATION']) : '';
	$doctor->LICENSE_NO     = isset($_POST['LICENSE_NO']) ? trim($_POST['LICENSE_NO']) : '';
	$doctor->CONTACT_NO     = isset($_POST['CONTACT_NO']) ? trim($_POST['CONTACT_NO']) : '';
	$doctor->SCHEDULE_DAYS  = isset($_POST['SCHEDULE_DAYS']) ? trim($_POST['SCHEDULE_DAYS']) : '';
	$doctor->SCHEDULE_TIME  = isset($_POST['SCHEDULE_TIME']) ? trim($_POST['SCHEDULE_TIME']) : '';
	$doctor->STATUS         = isset($_POST['STATUS']) ? $_POST['STATUS'] : 'Active';

	if (isset($_POST['UID']) && $_POST['UID'] !== '') {
		$doctor->UID = intval($_POST['UID']);
	}

	$istrue = $doctor->update($DOCTOR_ID);
	if ($istrue == true) {
		message("Doctor profile has been updated successfully!", "success");
	} else {
		message("No doctor profile has been updated.", "error");
	}
	redirect('index.php');
}

function doDelete(){
	$id = $_GET['id'];
	$doctor = new DoctorProfile();
	$doctor->delete($id);

	message("Doctor profile deleted!", "success");
	redirect('index.php');
}

/* HIPANAO SOLUTIONS - DOCTOR CONSULTATION
   Submitted from the "Record Consultation" modal on the Student list
   (module/student), Doctor accounts only. Answers one question -
   "Ano ang sakit?" (Chief Complaint) - for a student, then:
     1. Reuses that student's existing Patient record (tblpatients),
        or creates one from the student's own details if this is
        their first visit.
     2. Adds a new Visit record (tblvisits) with the complaint.
     3. Sends the doctor to that patient's View page (module/patient),
        where the visit now shows up with its own "View"/history. */
function doConsult(){
	global $mydb;

	if (!isset($_SESSION['UID'])) {
		redirect(WEB_ROOT.'login.php');
		return;
	}

	$S_ID            = isset($_POST['S_ID']) ? intval($_POST['S_ID']) : 0;
	$CHIEF_COMPLAINT = isset($_POST['CHIEF_COMPLAINT']) ? trim($_POST['CHIEF_COMPLAINT']) : '';
	$NOTES           = isset($_POST['NOTES']) ? trim($_POST['NOTES']) : '';

	/* Where to send the doctor back to if something below goes wrong.
	   The Consultation Roster (module/moduledoctor) passes its own URL
	   here; if it's missing (e.g. an older/other form posting here),
	   fall back to the Student module like before. */
	$returnUrl = (isset($_POST['RETURN_URL']) && trim($_POST['RETURN_URL']) != '')
		? trim($_POST['RETURN_URL'])
		: WEB_ROOT.'module/student/index.php';

	if ($S_ID <= 0 || $CHIEF_COMPLAINT == '') {
		message("Please select a student and answer what the chief complaint is.", "error");
		redirect($returnUrl);
		return;
	}

	$mydb->setQuery("SELECT * FROM `tblstudent` WHERE S_ID = '".$S_ID."' LIMIT 1");
	$student = $mydb->loadSingleResult();
	if (!$student) {
		message("Student record not found.", "error");
		redirect($returnUrl);
		return;
	}

	// Reuse this student's existing patient record, if there is one.
	$mydb->setQuery("SELECT PATIENT_ID FROM `tblpatients` WHERE S_ID = '".$S_ID."' LIMIT 1");
	$existing = $mydb->loadSingleResult();

	if ($existing) {
		$patientId = $existing->PATIENT_ID;
	} else {
		$patient = new Patient();
		$patient->S_ID  = $S_ID;
		$patient->FNAME = $student->FNAME;
		$patient->MNAME = $student->MNAME;
		$patient->LNAME = $student->LNAME;

		if ($student->SEX == 'Male' || $student->SEX == 'Female') {
			$patient->SEX = $student->SEX;
		}
		if (!empty($student->BDAY) && $student->BDAY != '0000-00-00') {
			$patient->BDAY = $student->BDAY;
		}
		if (!empty($student->AGE)) {
			$patient->AGE = intval($student->AGE);
		}
		if (!empty($student->CONTACT_NO)) {
			$patient->CONTACT_NO = $student->CONTACT_NO;
		}
		if (!empty($student->HOME_ADD)) {
			$patient->ADDRESS = $student->HOME_ADD;
		}
		$patient->STATUS    = 'Active';
		$patient->ADDEDBY   = intval($_SESSION['UID']);
		$patient->DATEADDED = date('Y-m-d');

		$istrue = $patient->create();
		if (!$istrue) {
			message("Could not create a patient record for this student.", "error");
			redirect($returnUrl);
			return;
		}
		$patientId = $mydb->insert_id();
	}

	// Link the visit to the logged-in doctor's own profile, if they have one.
	$doctorProfile = (new DoctorProfile())->profile_for_uid(intval($_SESSION['UID']));

	$visit = new Visit();
	$visit->PATIENT_ID = $patientId;
	if ($doctorProfile) {
		$visit->DOCTOR_ID = $doctorProfile->DOCTOR_ID;
	}
	$visit->VISIT_DATE      = date('Y-m-d');
	$visit->CHIEF_COMPLAINT = $CHIEF_COMPLAINT;
	$visit->NOTES           = $NOTES;
	$visit->ENCODED_BY      = intval($_SESSION['UID']);
	$visit->DATEADDED       = date('Y-m-d');
	$visit->create();

	message("Consultation recorded! [".$student->FNAME.' '.$student->LNAME."] has been added to Patients.", "success");
	redirect(WEB_ROOT.'module/patient/index.php?view=view&id='.$patientId);
}
?>