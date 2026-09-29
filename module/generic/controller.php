<?php
// Solitario Solutions

require_once("../../include/initialize.php");
require_once("config.php");

confirm_logged_in();

$table = isset($_GET['t']) ? $_GET['t'] : '';
$cfg   = generic_table_config($table);

if (!$cfg) {
	message("Unknown table.", "error");
	redirect(WEB_ROOT."module/error/index.php?view=list");
	exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$backUrl = 'index.php?t='.urlencode($table);

switch ($action) {
	case 'add':
		doInsert($table, $cfg, $backUrl);
		break;
	case 'edit':
		doEdit($table, $cfg, $backUrl);
		break;
	case 'delete':
		doDelete($table, $cfg, $backUrl);
		break;
}

/* Turns a raw PDO error message into something a non-developer can act
   on, for the couple of failure types most likely to happen from this
   form (a required dropdown left blank, a duplicate unique value). Falls
   back to a generic message for anything else rather than showing raw
   SQL/driver text. */
function generic_friendly_error($rawError) {
	if ($rawError === '') { return ''; }
	if (stripos($rawError, 'foreign key constraint') !== false) {
		return " Please make sure every dropdown field has a valid selection.";
	}
	if (stripos($rawError, 'Duplicate entry') !== false) {
		return " A record with that value already exists.";
	}
	return "";
}

function doInsert($table, $cfg, $backUrl) {
	global $mydb;
	$rec = new GenericRecord($table);
	$data = $_POST;
	unset($data['save']);

	$ok = $rec->create($data);
	if ($ok) {
		message($cfg['title']." record has been added successfully!", "success");
	} else {
		message("No ".$cfg['title']." record has been created.".generic_friendly_error($mydb->error_msg), "error");
	}
	redirect($backUrl);
}

function doEdit($table, $cfg, $backUrl) {
	global $mydb;
	$rec = new GenericRecord($table);
	$id  = isset($_POST['record_pk']) ? $_POST['record_pk'] : '';
	$data = $_POST;
	unset($data['edit'], $data['record_pk']);

	if ($id === '') {
		message("Could not determine which record to update.", "error");
		redirect($backUrl);
		return;
	}

	$ok = $rec->update($id, $data);
	if ($ok) {
		message($cfg['title']." record has been updated successfully!", "success");
	} else {
		message("No ".$cfg['title']." record has been updated.".generic_friendly_error($mydb->error_msg), "error");
	}
	redirect($backUrl);
}

function doDelete($table, $cfg, $backUrl) {
	global $mydb;
	$rec = new GenericRecord($table);
	$id  = isset($_GET['id']) ? $_GET['id'] : '';

	if ($id !== '') {
		$ok = $rec->delete($id);
		if (!$ok) {
			message($cfg['title']." record could not be deleted.".generic_friendly_error($mydb->error_msg), "error");
			redirect($backUrl);
			return;
		}
	}
	message($cfg['title']." record has been deleted.", "success");
	redirect($backUrl);
}
?>