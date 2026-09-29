<?php
// Solitario Solution - Admin view of a single doctor: full profile + activity
require_once("../../include/initialize.php");
if (!isset($_SESSION['UID'])) {
  redirect(WEB_ROOT."login.php");
}
global $mydb;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$mydb->setQuery("SELECT d.*, u.USERNAME, u.STATUSACTIVE AS ACCOUNT_ACTIVE 
  FROM `tbldoctors` d 
  LEFT JOIN `tblusers` u ON u.UID = d.UID 
  WHERE d.DOCTOR_ID = '".$id."' LIMIT 1");
$rows = $mydb->loadResultList();
$doc = ($rows && count($rows) >= 1) ? $rows[0] : null;

$visits = array();
if ($doc) {
  $mydb->setQuery("SELECT v.VISIT_ID, v.VISIT_DATE, v.CHIEF_COMPLAINT, v.DIAGNOSIS, v.NOTES, 
      p.PATIENT_ID, p.FNAME, p.MNAME, p.LNAME 
    FROM `tblvisits` v 
    JOIN `tblpatients` p ON p.PATIENT_ID = v.PATIENT_ID 
    WHERE v.DOCTOR_ID = '".$id."' 
    ORDER BY v.VISIT_DATE DESC, v.VISIT_ID DESC");
  $visits = $mydb->loadResultList();
  if (!$visits) { $visits = array(); }
}

$title   = "Doctor Profile";
$content = 'view_content.php';

require_once("../../theme/template.php");
?>