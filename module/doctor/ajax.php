<?php
require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;

header('Content-Type: application/json; charset=utf-8');

/* index.php's editEntry click handler posts {UID: <doctor's row id>} -
   the attribute is named "UID" but the value it actually carries is
   DOCTOR_ID, matching the pattern used across the other modules in
   this project (the attribute name is generic, the value is always
   "this row's primary key"). */
if (isset($_POST['UID'])) {

	$output = array();
	$mydb->setQuery("SELECT * FROM `tbldoctors` WHERE DOCTOR_ID = '".intval($_POST["UID"])."' LIMIT 1");
	$result = $mydb->loadResultList();

	if ($result) {
		foreach ($result as $row) {
			$output["DOCTOR_ID"]      = $row->DOCTOR_ID;
			$output["UID"]            = $row->UID;
			$output["FULLNAME"]       = $row->FULLNAME;
			$output["SPECIALIZATION"] = $row->SPECIALIZATION;
			$output["LICENSE_NO"]     = $row->LICENSE_NO;
			$output["CONTACT_NO"]     = $row->CONTACT_NO;
			$output["SCHEDULE_DAYS"]  = $row->SCHEDULE_DAYS;
			$output["SCHEDULE_TIME"]  = $row->SCHEDULE_TIME;
			$output["STATUS"]         = $row->STATUS;
		}
	}
	echo json_encode($output);
	exit;
}


/* -----------------------------------------------------------------
   DataTables list
   ----------------------------------------------------------------- */

$where = "";

if (isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '') {
	$s = $mydb->escape_value($_POST["search"]["value"]);
	$where = " WHERE `FULLNAME` LIKE '%".$s."%' OR `SPECIALIZATION` LIKE '%".$s."%' ";
}

/* Only whitelisted columns can be sorted (index 0 is "#", last is "Action"). */
$orderCols = array(
	1 => 'FULLNAME',
	2 => 'SPECIALIZATION',
	3 => 'CONTACT_NO',
	4 => 'SCHEDULE_DAYS',
	5 => 'STATUS'
);

$orderSql = " ORDER BY `FULLNAME` ASC ";

if (isset($_POST['order'][0]['column'])) {
	$ci  = intval($_POST['order'][0]['column']);
	$dir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) == 'desc') ? 'DESC' : 'ASC';
	if (isset($orderCols[$ci])) {
		$orderSql = " ORDER BY `".$orderCols[$ci]."` ".$dir." ";
	}
}

$start  = isset($_POST['start'])  ? max(0, intval($_POST['start'])) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;

$limitSql = ($length > 0) ? " LIMIT ".$start.", ".$length." " : "";

$mydb->setQuery("SELECT COUNT(*) AS total FROM `tbldoctors`");
$tRow = $mydb->loadSingleResult();
$recordsTotal = $tRow ? intval($tRow->total) : 0;

$mydb->setQuery("SELECT COUNT(*) AS total FROM `tbldoctors`".$where);
$fRow = $mydb->loadSingleResult();
$recordsFiltered = $fRow ? intval($fRow->total) : 0;

$mydb->setQuery("SELECT * FROM `tbldoctors`".$where.$orderSql.$limitSql);
$cur = $mydb->loadResultList();

if (!$cur) {
	$cur = array();
}

$data = array();
$i = $start + 1;

foreach ($cur as $result) {

	$sub_array = array();

	$sub_array[] = $i;
	$sub_array[] = htmlspecialchars($result->FULLNAME);
	$sub_array[] = htmlspecialchars($result->SPECIALIZATION);
	$sub_array[] = htmlspecialchars($result->CONTACT_NO);
	$sub_array[] = htmlspecialchars(trim($result->SCHEDULE_DAYS.' '.$result->SCHEDULE_TIME));

	if ($result->STATUS == 'Active') {
		$sub_array[] = '<span class="badge badge-success">Active</span>';
	} else {
		$sub_array[] = '<span class="badge badge-secondary">Inactive</span>';
	}

	$sub_array[] = '
	<div class="btn-group">
		<a href="view.php?id='.$result->DOCTOR_ID.'" class="btn btn-info btn-xs" title="View"><span class="fa fa-eye"></span></a>
		<button type="button" name="update" UID="'.$result->DOCTOR_ID.'" class="btn btn-warning btn-xs editEntry" title="Edit"><span class="fa fa-edit"></span></button>
		<button type="button" DID="'.$result->DOCTOR_ID.'" class="btn btn-danger btn-xs deleteEntry" title="Delete"><span class="fa fa-trash"></span></button>
	</div>';

	$data[] = $sub_array;
	$i++;
}

echo json_encode(array(
	'draw'            => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
	'data'            => $data,
	'recordsTotal'    => $recordsTotal,
	'recordsFiltered' => $recordsFiltered
));
?>