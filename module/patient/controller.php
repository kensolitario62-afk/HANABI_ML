<?php

require_once("../../include/initialize.php");

confirm_logged_in();

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';

switch ($action) {

    case 'add':
        doInsert();
        break;

    case 'edit':
        doEdit();
        break;

    case 'delete':
        doDelete();
        break;

    case 'update_visit':
        doUpdateVisit();
        break;

    default:
        redirect('index.php');
        break;
}


/*
|--------------------------------------------------------------------------
| ADD PATIENT
|--------------------------------------------------------------------------
*/

function doInsert()
{
    $patient = new Patient();

    $FNAME = isset($_POST['FNAME']) ? trim($_POST['FNAME']) : '';
    $LNAME = isset($_POST['LNAME']) ? trim($_POST['LNAME']) : '';

    if ($FNAME == '' || $LNAME == '') {

        message("First and last name are required.", "error");

        redirect('index.php');

        return;
    }

    $patient->FNAME      = $FNAME;
    $patient->MNAME      = isset($_POST['MNAME']) ? trim($_POST['MNAME']) : '';
    $patient->LNAME      = $LNAME;
    $patient->SEX        = isset($_POST['SEX']) ? $_POST['SEX'] : '';
    $patient->BDAY       = (isset($_POST['BDAY']) && $_POST['BDAY'] != '') ? $_POST['BDAY'] : null;
    $patient->AGE        = (isset($_POST['AGE']) && $_POST['AGE'] != '') ? intval($_POST['AGE']) : null;
    $patient->CONTACT_NO = isset($_POST['CONTACT_NO']) ? trim($_POST['CONTACT_NO']) : '';
    $patient->ADDRESS    = isset($_POST['ADDRESS']) ? trim($_POST['ADDRESS']) : '';
    $patient->STATUS     = isset($_POST['STATUS']) ? $_POST['STATUS'] : 'Active';
    $patient->ADDEDBY    = isset($_SESSION['UID']) ? intval($_SESSION['UID']) : null;
    $patient->DATEADDED  = date('Y-m-d');

    if ($patient->create()) {

        message("New patient record has been created successfully!", "success");

    } else {

        message("The patient record could not be created.", "error");
    }

    redirect('index.php');
}


/*
|--------------------------------------------------------------------------
| EDIT PATIENT
|--------------------------------------------------------------------------
*/

function doEdit()
{
    $patient = new Patient();

    $PATIENT_ID = isset($_POST['PATIENT_ID']) ? intval($_POST['PATIENT_ID']) : 0;

    $FNAME1 = isset($_POST['FNAME1']) ? trim($_POST['FNAME1']) : '';
    $LNAME1 = isset($_POST['LNAME1']) ? trim($_POST['LNAME1']) : '';

    if ($PATIENT_ID <= 0) {

        message("Invalid patient ID.", "error");

        redirect('index.php');

        return;
    }

    if ($FNAME1 == '' || $LNAME1 == '') {

        message("First and last name are required.", "error");

        redirect('index.php');

        return;
    }

    $patient->FNAME      = $FNAME1;
    $patient->MNAME      = isset($_POST['MNAME1']) ? trim($_POST['MNAME1']) : '';
    $patient->LNAME      = $LNAME1;
    $patient->SEX        = isset($_POST['SEX1']) ? $_POST['SEX1'] : '';
    $patient->BDAY       = (isset($_POST['BDAY1']) && $_POST['BDAY1'] != '') ? $_POST['BDAY1'] : null;
    $patient->AGE        = (isset($_POST['AGE1']) && $_POST['AGE1'] != '') ? intval($_POST['AGE1']) : null;
    $patient->CONTACT_NO = isset($_POST['CONTACT_NO1']) ? trim($_POST['CONTACT_NO1']) : '';
    $patient->ADDRESS    = isset($_POST['ADDRESS1']) ? trim($_POST['ADDRESS1']) : '';
    $patient->STATUS     = isset($_POST['STATUS1']) ? $_POST['STATUS1'] : 'Active';

    if ($patient->update($PATIENT_ID)) {

        message("Patient record has been updated successfully!", "success");

    } else {

        message("The patient record could not be updated.", "error");
    }

    redirect('index.php');
}


/*
|--------------------------------------------------------------------------
| DELETE PATIENT
|--------------------------------------------------------------------------
*/

function doDelete()
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {

        message("Invalid patient ID.", "error");

        redirect('index.php');

        return;
    }

    $patient = new Patient();

    if ($patient->delete($id)) {

        message("Patient record has been deleted.", "success");

    } else {

        message("The patient record could not be deleted.", "error");
    }

    redirect('index.php');
}


/*
|--------------------------------------------------------------------------
| UPDATE VISIT (Diagnosis + Notes)
|--------------------------------------------------------------------------
|
| Fills in Diagnosis + Notes on a visit that was recorded with only a
| Chief Complaint (e.g. from the Student list's "Record Consultation"
| modal). Reached from the patient's View page.
|
*/

function doUpdateVisit()
{
    $VISIT_ID   = isset($_POST['VISIT_ID']) ? intval($_POST['VISIT_ID']) : 0;
    $PATIENT_ID = isset($_POST['PATIENT_ID']) ? intval($_POST['PATIENT_ID']) : 0;

    if ($VISIT_ID <= 0) {

        message("That visit record could not be found.", "error");

        redirect('index.php?view=view&id='.$PATIENT_ID);

        return;
    }

    $visit = new Visit();
    $visit->DIAGNOSIS = isset($_POST['DIAGNOSIS']) ? trim($_POST['DIAGNOSIS']) : '';
    $visit->NOTES     = isset($_POST['NOTES']) ? trim($_POST['NOTES']) : '';

    if ($visit->update($VISIT_ID)) {

        message("Visit record updated.", "success");

    } else {

        message("The visit record could not be updated.", "error");
    }

    redirect('index.php?view=view&id='.$PATIENT_ID);
}

?>