<?php
//  Enrollment controller
require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;

/* The minimum amount that has to be paid before a student can be
   enrolled - NOT the full amount due. The remaining balance can
   still be paid later, even after the student is already enrolled. */
define('REGISTRATION_FEE', 1000);

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';

switch ($action) {
	case 'assign' :
		doAssign();
		break;

	case 'section' :
		doSectioning();
		break;

	case 'pay' :
		doPayment();
		break;

	case 'enroll' :
		doEnroll();
		break;

	case 'edit' :
		doEdit();
		break;

	case 'verify_payment' :
		doVerifyPayment();
		break;

	case 'delete' :
		doDelete();
		break;
}


/* ---------------------------------------------------------------------
   Confirms that a section really belongs to the given course and school
   year.
   --------------------------------------------------------------------- */
function sectionBelongsTo($sectionId, $courseId, $syId) {
	global $mydb;
	$mydb->setQuery("SELECT SECTION_ID FROM `tblsections` 
		WHERE SECTION_ID = '".intval($sectionId)."' 
		  AND COURSE_ID  = '".intval($courseId)."' 
		  AND SY_ID      = '".intval($syId)."' 
		LIMIT 1");
	return ($mydb->num_rows() >= 1);
}


/* ---------------------------------------------------------------------
   STAGE 1 -> 2: ASSIGN SUBJECTS
   Replaces the enrollment's subject list with whatever was checked, then
   recalculates AMOUNT_DUE from each subject's unit price. Re-running this
   later (to add/drop a subject) only recalculates the amount - it does
   not undo Sectioning/Payment progress already made.
   --------------------------------------------------------------------- */
function doAssign() {

	global $mydb;

	$EID = isset($_POST['A_EID']) ? intval($_POST['A_EID']) : 0;
	$subjectIds = isset($_POST['SUBJECTS']) ? $_POST['SUBJECTS'] : array();

	if ($EID <= 0) {
		message("That enrollment record could not be found.", "error");
		redirect('index.php');
		return;
	}

	if (count($subjectIds) < 1) {
		message("Please select at least one subject.", "error");
		redirect('index.php');
		return;
	}

	/* Wipe and rebuild the subject list for this enrollment. */
	$mydb->InsertThis("DELETE FROM `tblenrollment_details` WHERE ENROLLMENT_ID = '".$EID."'");

	$totalDue = 0;
	foreach ($subjectIds as $sid) {
		$sid = intval($sid);
		if ($sid <= 0) { continue; }

		$mydb->setQuery("SELECT SUBJECT_ID, UNITS, PRICE_PER_UNIT FROM `tblsubjects` WHERE SUBJECT_ID = '".$sid."' LIMIT 1");
		$rows = $mydb->loadResultList();
		if (count($rows) < 1) { continue; }

		$mydb->InsertThis("INSERT INTO `tblenrollment_details` (ENROLLMENT_ID, SUBJECT_ID) VALUES ('".$EID."', '".$sid."')");
		$totalDue += ($rows[0]->UNITS * $rows[0]->PRICE_PER_UNIT);
	}

	/* Only auto-advance the status the first time a record is assigned.
	   If it is already further along (Sectioned, Paid, etc.), editing the
	   subject list here should not roll that progress back. */
	$mydb->setQuery("SELECT STATUS FROM `tblenrollment` WHERE ENROLLMENT_ID = '".$EID."' LIMIT 1");
	$cur = $mydb->loadResultList();
	$statusSql = '';
	if (count($cur) >= 1 && $cur[0]->STATUS == 'Registered') {
		$statusSql = ", `STATUS` = 'Assigned'";
	}

	$sql = "UPDATE `tblenrollment` SET `AMOUNT_DUE` = '".$totalDue."' ".$statusSql." WHERE `ENROLLMENT_ID` = '".$EID."'";

	if ($mydb->InsertThis($sql)) {
		message("Subjects assigned. Amount due: PHP ".number_format($totalDue, 2).".", "success");
	} else {
		message("The subject list could not be saved.", "error");
	}
	redirect('index.php');
}


/* ---------------------------------------------------------------------
   STAGE 2 -> 3: SECTIONING
   Only meaningful once subjects have been assigned.
   --------------------------------------------------------------------- */
function doSectioning() {

	global $mydb;

	$EID     = isset($_POST['SEC_EID'])     ? intval($_POST['SEC_EID'])     : 0;
	$SECTION = isset($_POST['SEC_SECTION']) ? intval($_POST['SEC_SECTION']) : 0;

	if ($EID <= 0 || $SECTION <= 0) {
		message("Please choose a section.", "error");
		redirect('index.php');
		return;
	}

	$mydb->setQuery("SELECT COURSE_ID, SY_ID, STATUS FROM `tblenrollment` WHERE ENROLLMENT_ID = '".$EID."' LIMIT 1");
	$rows = $mydb->loadResultList();
	if (count($rows) < 1) {
		message("That enrollment record no longer exists.", "error");
		redirect('index.php');
		return;
	}
	$rec = $rows[0];

	if ($rec->STATUS == 'Registered') {
		message("Assign subjects to this student before sectioning.", "error");
		redirect('index.php');
		return;
	}

	if (!sectionBelongsTo($SECTION, $rec->COURSE_ID, $rec->SY_ID)) {
		message("That section does not belong to this student's course and academic year.", "error");
		redirect('index.php');
		return;
	}

	/* Sectioning does not finalize enrollment by itself anymore - Payment
	   and Enroll still have to happen. Only move the status forward if it
	   has not already progressed past this point. */
	$statusSql = '';
	if ($rec->STATUS == 'Assigned') {
		$statusSql = ", `STATUS` = 'Sectioned'";
	}

	$sql = "UPDATE `tblenrollment` SET `SECTION_ID` = '".$SECTION."' ".$statusSql." WHERE `ENROLLMENT_ID` = '".$EID."'";

	if ($mydb->InsertThis($sql)) {
		message("Section saved.", "success");
	} else {
		message("Sectioning could not be saved.", "error");
	}
	redirect('index.php');
}


/* ---------------------------------------------------------------------
   STAGE 4: PAYMENT
   Logs a payment, updates the running total, and flips the record to
   Paid once the balance reaches zero.
   --------------------------------------------------------------------- */
function doPayment() {

	global $mydb;

	$EID     = isset($_POST['P_EID'])     ? intval($_POST['P_EID'])              : 0;
	$AMOUNT  = isset($_POST['P_AMOUNT'])  ? floatval($_POST['P_AMOUNT'])         : 0;
	$TYPE    = isset($_POST['P_TYPE'])    ? trim($_POST['P_TYPE'])               : 'Tuition';
	$OR      = isset($_POST['P_OR'])      ? trim($_POST['P_OR'])                 : '';
	$CASHIER = isset($_POST['P_CASHIER']) ? trim($_POST['P_CASHIER'])            : '';

	if ($TYPE !== 'Registration' && $TYPE !== 'Tuition') {
		$TYPE = 'Tuition';
	}

	if ($EID <= 0 || $AMOUNT <= 0 || $CASHIER == '') {
		message("Please fill in the amount and cashier name.", "error");
		redirect('index.php');
		return;
	}

	$mydb->setQuery("SELECT STATUS, AMOUNT_DUE, AMOUNT_PAID, REG_FEE_PAID FROM `tblenrollment` WHERE ENROLLMENT_ID = '".$EID."' LIMIT 1");
	$rows = $mydb->loadResultList();
	if (count($rows) < 1) {
		message("That enrollment record no longer exists.", "error");
		redirect('index.php');
		return;
	}
	$rec = $rows[0];

	if ($rec->STATUS == 'Registered' || $rec->STATUS == 'Assigned') {
		message("Complete sectioning before recording a payment.", "error");
		redirect('index.php');
		return;
	}

	// Registration Fee is a flat cap - never let any single payment push
	// it past REGISTRATION_FEE, no matter how it's entered.
	if ($TYPE == 'Registration') {
		$remaining = max(0, REGISTRATION_FEE - $rec->REG_FEE_PAID);
		if ($AMOUNT > $remaining) {
			message("The registration fee balance is only PHP ".number_format($remaining, 2).". Enter an amount at or below that, or record the rest under Tuition.", "error");
			redirect('index.php');
			return;
		}
	}

	$mydb->InsertThis("INSERT INTO `tblpayments` (ENROLLMENT_ID, AMOUNT, PAYMENT_TYPE, OR_NUMBER, CASHIER, DATE_PAID) 
		VALUES ('".$EID."', '".$AMOUNT."', '".$mydb->escape_value($TYPE)."', '".$mydb->escape_value($OR)."', '".$mydb->escape_value($CASHIER)."', CURDATE())");

	if ($TYPE == 'Registration') {

		/* Registration Fee ledger. This is the only thing that unlocks
		   enrollment - it is completely independent of how much tuition
		   has been paid. */
		$newRegPaid = $rec->REG_FEE_PAID + $AMOUNT;

		$statusSql = '';
		if ($newRegPaid >= REGISTRATION_FEE && $rec->STATUS == 'Sectioned') {
			$statusSql = ", `STATUS` = 'Paid'";
		}

		$sql = "UPDATE `tblenrollment` SET `REG_FEE_PAID` = '".$newRegPaid."' ".$statusSql." WHERE `ENROLLMENT_ID` = '".$EID."'";

		if ($mydb->InsertThis($sql)) {
			if ($newRegPaid >= REGISTRATION_FEE && $rec->STATUS == 'Sectioned') {
				message("Registration fee paid in full - the student can now be enrolled. Tuition balance can still be settled anytime.", "success");
			} else {
				$remaining = max(0, REGISTRATION_FEE - $newRegPaid);
				message("Registration payment recorded. Remaining registration balance: PHP ".number_format($remaining, 2).".", "success");
			}
		} else {
			message("The payment could not be saved.", "error");
		}

	} else {

		/* Tuition/units ledger. Paying this down never gates enrollment -
		   it can be settled any time, before or after the student is
		   already enrolled. */
		$newPaid = $rec->AMOUNT_PAID + $AMOUNT;

		$sql = "UPDATE `tblenrollment` SET `AMOUNT_PAID` = '".$newPaid."' WHERE `ENROLLMENT_ID` = '".$EID."'";

		if ($mydb->InsertThis($sql)) {
			if ($newPaid >= $rec->AMOUNT_DUE) {
				message("Tuition payment recorded. Tuition balance is now fully paid.", "success");
			} else {
				$balance = $rec->AMOUNT_DUE - $newPaid;
				message("Tuition payment recorded. Remaining tuition balance: PHP ".number_format($balance, 2).".", "success");
			}
		} else {
			message("The payment could not be saved.", "error");
		}
	}

	redirect('index.php');
}


/* ---------------------------------------------------------------------
   STAGE 5 -> 6: ENROLL
   The final step. Locked until the balance is fully paid.
   --------------------------------------------------------------------- */
function doEnroll() {

	global $mydb;

	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

	if ($id <= 0) {
		message("No enrollment record selected.", "error");
		redirect('index.php');
		return;
	}

	$mydb->setQuery("SELECT STATUS FROM `tblenrollment` WHERE ENROLLMENT_ID = '".$id."' LIMIT 1");
	$rows = $mydb->loadResultList();
	if (count($rows) < 1) {
		message("That enrollment record no longer exists.", "error");
		redirect('index.php');
		return;
	}

	if ($rows[0]->STATUS != 'Paid') {
		message("This student has to pay first before they can be enrolled.", "error");
		redirect('index.php');
		return;
	}

	$sql = "UPDATE `tblenrollment` SET `STATUS` = 'Enrolled', `DATE_ENROLLED` = CURDATE() WHERE `ENROLLMENT_ID` = '".$id."'";

	if ($mydb->InsertThis($sql)) {
		message("Student is now officially enrolled.", "success");
	} else {
		message("Enrollment could not be finalized.", "error");
	}
	redirect('index.php');
}


/* ---------------------------------------------------------------------
   EDIT an existing enrollment record
   --------------------------------------------------------------------- */
function doEdit() {

	global $mydb;

	$EID        = isset($_POST['E_EID'])            ? intval($_POST['E_EID'])           : 0;
	$SY_ID      = isset($_POST['E_SY'])             ? intval($_POST['E_SY'])            : 0;
	$COURSE_ID  = isset($_POST['E_COURSE'])         ? intval($_POST['E_COURSE'])        : 0;
	$SECTION_ID = isset($_POST['E_SECTION'])        ? intval($_POST['E_SECTION'])       : 0;
	$YEAR_LEVEL = isset($_POST['E_YEARLEVEL'])      ? trim($_POST['E_YEARLEVEL'])       : '';
	$SEMESTER   = isset($_POST['E_SEMESTER'])       ? trim($_POST['E_SEMESTER'])        : '';
	$CATEGORY   = isset($_POST['E_CATEGORY'])       ? trim($_POST['E_CATEGORY'])        : 'New';
	$CURRICULUM = isset($_POST['E_CURRICULUM'])     ? trim($_POST['E_CURRICULUM'])      : '';
	$STATUS     = isset($_POST['E_STATUS'])         ? trim($_POST['E_STATUS'])          : 'Registered';
	$RESERVED   = isset($_POST['E_DATE_RESERVED'])  ? trim($_POST['E_DATE_RESERVED'])   : '';
	$ENROLLED   = isset($_POST['E_DATE_ENROLLED'])  ? trim($_POST['E_DATE_ENROLLED'])   : '';

	if ($EID <= 0 || $SY_ID <= 0 || $COURSE_ID <= 0 || $YEAR_LEVEL == '' || $SEMESTER == '') {
		message("Please complete all the required fields.", "error");
		redirect('index.php');
		return;
	}

	if ($SECTION_ID > 0 && !sectionBelongsTo($SECTION_ID, $COURSE_ID, $SY_ID)) {
		message("That section does not belong to the selected course and academic year.", "error");
		redirect('index.php');
		return;
	}

	$mydb->setQuery("SELECT S_ID FROM `tblenrollment` WHERE ENROLLMENT_ID = '".$EID."' LIMIT 1");
	$owner = $mydb->loadResultList();
	if (count($owner) < 1) {
		message("That enrollment record no longer exists.", "error");
		redirect('index.php');
		return;
	}
	$S_ID = $owner[0]->S_ID;

	$mydb->setQuery("SELECT ENROLLMENT_ID FROM `tblenrollment` 
		WHERE S_ID     = '".$S_ID."' 
		  AND SY_ID    = '".$SY_ID."' 
		  AND SEMESTER = '".$mydb->escape_value($SEMESTER)."' 
		  AND ENROLLMENT_ID <> '".$EID."' 
		LIMIT 1");
	if ($mydb->num_rows() >= 1) {
		message("This student already has another record for that academic year and semester.", "error");
		redirect('index.php');
		return;
	}

	$sectionSql    = ($SECTION_ID > 0) ? "'".$SECTION_ID."'" : "NULL";
	$curriculumSql = ($CURRICULUM == '') ? "NULL" : "'".$mydb->escape_value($CURRICULUM)."'";
	$reservedSql   = ($RESERVED == '')   ? "NULL" : "'".$mydb->escape_value($RESERVED)."'";
	$enrolledSql   = ($ENROLLED == '')   ? "NULL" : "'".$mydb->escape_value($ENROLLED)."'";

	$sql = "UPDATE `tblenrollment` SET 
			`COURSE_ID`     = '".$COURSE_ID."', 
			`SECTION_ID`    = ".$sectionSql.", 
			`SY_ID`         = '".$SY_ID."', 
			`YEAR_LEVEL`    = '".$mydb->escape_value($YEAR_LEVEL)."', 
			`SEMESTER`      = '".$mydb->escape_value($SEMESTER)."', 
			`CATEGORY`      = '".$mydb->escape_value($CATEGORY)."', 
			`CURRICULUM_YR` = ".$curriculumSql.", 
			`DATE_RESERVED` = ".$reservedSql.", 
			`DATE_ENROLLED` = ".$enrolledSql.", 
			`STATUS`        = '".$mydb->escape_value($STATUS)."' 
		WHERE `ENROLLMENT_ID` = '".$EID."'";

	if ($mydb->InsertThis($sql)) {
		message("Enrollment record updated.", "success");
	} else {
		message("The enrollment record could not be updated.", "error");
	}
	redirect('index.php');
}


/* ---------------------------------------------------------------------
   Approve or reject a payment a student submitted through the Student
   Portal. Approving is the ONLY point where an online submission
   actually moves REG_FEE_PAID/AMOUNT_PAID - the portal itself never
   touches those columns, so a student can't grant themselves
   enrollment/payment status just by submitting the form.
   --------------------------------------------------------------------- */
function doVerifyPayment() {

	global $mydb;

	$PID      = isset($_GET['pid'])      ? intval($_GET['pid'])      : 0;
	$DECISION = isset($_GET['decision']) ? trim($_GET['decision'])  : '';

	if ($PID <= 0 || ($DECISION !== 'approve' && $DECISION !== 'reject')) {
		message("Invalid verification request.", "error");
		redirect('index.php');
		return;
	}

	$mydb->setQuery("SELECT p.*, e.STATUS AS ENROLL_STATUS, e.AMOUNT_DUE, e.AMOUNT_PAID, e.REG_FEE_PAID 
		FROM `tblpayments` p 
		JOIN `tblenrollment` e ON e.ENROLLMENT_ID = p.ENROLLMENT_ID 
		WHERE p.PAYMENT_ID = '".$PID."' LIMIT 1");
	$rows = $mydb->loadResultList();

	if (count($rows) < 1) {
		message("That payment submission no longer exists.", "error");
		redirect('index.php');
		return;
	}
	$pay = $rows[0];

	if ($pay->PAY_STATUS !== 'Pending') {
		message("That payment has already been ".strtolower($pay->PAY_STATUS).".", "error");
		redirect('index.php');
		return;
	}

	$verifiedBy = isset($_SESSION['UID']) ? intval($_SESSION['UID']) : null;
	$verifiedBySql = $verifiedBy ? "'".$verifiedBy."'" : "NULL";

	if ($DECISION === 'reject') {

		$mydb->InsertThis("UPDATE `tblpayments` SET `PAY_STATUS` = 'Rejected', `VERIFIED_BY` = ".$verifiedBySql.", `VERIFIED_ON` = NOW() WHERE `PAYMENT_ID` = '".$PID."'");
		message("Payment submission rejected.", "info");
		redirect('index.php');
		return;
	}

	// Approve: mark verified, then apply to the correct ledger - same
	// rules as a counter payment (doPayment above).
	$mydb->InsertThis("UPDATE `tblpayments` SET `PAY_STATUS` = 'Verified', `VERIFIED_BY` = ".$verifiedBySql.", `VERIFIED_ON` = NOW() WHERE `PAYMENT_ID` = '".$PID."'");

	if ($pay->PAYMENT_TYPE === 'Registration') {

		// Never let approval push REG_FEE_PAID past the flat cap - even
		// though this submission was valid when the student sent it, a
		// separate pending submission for the same student may have
		// already been approved in the meantime. Apply only what's
		// still needed and say so.
		$remaining = max(0, REGISTRATION_FEE - $pay->REG_FEE_PAID);
		$applied = min($pay->AMOUNT, $remaining);
		$newRegPaid = $pay->REG_FEE_PAID + $applied;

		$statusSql = '';
		if ($newRegPaid >= REGISTRATION_FEE && $pay->ENROLL_STATUS == 'Sectioned') {
			$statusSql = ", `STATUS` = 'Paid'";
		}
		$mydb->InsertThis("UPDATE `tblenrollment` SET `REG_FEE_PAID` = '".$newRegPaid."' ".$statusSql." WHERE `ENROLLMENT_ID` = '".intval($pay->ENROLLMENT_ID)."'");

		if ($applied < $pay->AMOUNT) {
			$unapplied = $pay->AMOUNT - $applied;
			message("Payment verified. Registration fee was already covered by another payment, so only PHP ".number_format($applied, 2)." was applied - PHP ".number_format($unapplied, 2)." was not credited anywhere. Please follow up with the student about the difference.", "success");
			redirect('index.php');
			return;
		}

	} else {

		$newPaid = $pay->AMOUNT_PAID + $pay->AMOUNT;
		$mydb->InsertThis("UPDATE `tblenrollment` SET `AMOUNT_PAID` = '".$newPaid."' WHERE `ENROLLMENT_ID` = '".intval($pay->ENROLLMENT_ID)."'");
	}

	message("Payment verified and applied to the student's balance.", "success");
	redirect('index.php');
}


function doDelete() {

	global $mydb;

	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

	if ($id <= 0) {
		message("No enrollment record selected.", "error");
		redirect('index.php');
		return;
	}

	$mydb->setQuery("SELECT DETAIL_ID FROM `tblenrollment_details` WHERE ENROLLMENT_ID = '".$id."'");
	$subjects = $mydb->num_rows();
	$mydb->setQuery("SELECT GRADE_ID FROM `tblgrades` WHERE ENROLLMENT_ID = '".$id."'");
	$grades = $mydb->num_rows();
	$mydb->setQuery("SELECT PAYMENT_ID FROM `tblpayments` WHERE ENROLLMENT_ID = '".$id."'");
	$payments = $mydb->num_rows();

	$mydb->InsertThis("DELETE FROM `tblenrollment_details` WHERE ENROLLMENT_ID = '".$id."'");
	$mydb->InsertThis("DELETE FROM `tblgrades` WHERE ENROLLMENT_ID = '".$id."'");
	$mydb->InsertThis("DELETE FROM `tblpayments` WHERE ENROLLMENT_ID = '".$id."'");

	if ($mydb->InsertThis("DELETE FROM `tblenrollment` WHERE `ENROLLMENT_ID` = '".$id."'")) {
		$extra = '';
		if ($subjects > 0 || $grades > 0 || $payments > 0) {
			$extra = " ".$subjects." subject(s), ".$grades." grade(s), and ".$payments." payment record(s) were removed with it.";
		}
		message("Enrollment record deleted.".$extra, "info");
	} else {
		message("The enrollment record could not be deleted.", "error");
	}
	redirect('index.php');
}
?>