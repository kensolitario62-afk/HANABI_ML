<?php
// Solitario Solutions

require_once("../../include/initialize.php");
require_once("config.php");

confirm_logged_in();

global $mydb;

header('Content-Type: application/json; charset=utf-8');

$table = isset($_GET['t']) ? $_GET['t'] : (isset($_POST['t']) ? $_POST['t'] : '');
$cfg   = generic_table_config($table);

if (!$cfg) {
	echo json_encode(array('error' => 'Unknown table'));
	exit;
}

$rec     = new GenericRecord($table);
$columns = generic_visible_columns($rec, $rec->columns());
$fields  = array();
foreach ($columns as $c) { $fields[] = $c->Field; }

if (isset($_POST['record_id'])) {
	// single record for the Edit modal - still reads every real column
	// (including the PK) since the form needs record_pk regardless.
	$row = $rec->single($_POST['record_id']);
	$output = array();
	if ($row) {
		foreach ($rec->fieldNames() as $f) {
			$output[$f] = isset($row->$f) ? $row->$f : '';
		}
	}
	echo json_encode($output);
	exit;
}

$where = "";

if (isset($_POST["search"]["value"]) && $_POST["search"]["value"] !== '') {
	$term = $mydb->escape_value($_POST["search"]["value"]);
	$likeParts = array();
	foreach ($fields as $f) {
		$likeParts[] = "`".$f."` LIKE '%".$term."%'";
	}
	if (!empty($likeParts)) {
		$where = " WHERE (".join(" OR ", $likeParts).")";
	}
}

/* Column 0 is the "#" column, so real fields start at index 1. */
$orderSql = " ORDER BY `".$rec->pk."` DESC";

if (isset($_POST["order"][0]["column"]) && isset($fields[intval($_POST['order']['0']['column']) - 1])) {
	$orderField = $fields[intval($_POST['order']['0']['column']) - 1];
	$orderDir   = (isset($_POST['order']['0']['dir']) && strtolower($_POST['order']['0']['dir']) == 'asc') ? 'ASC' : 'DESC';
	$orderSql   = " ORDER BY `".$orderField."` ".$orderDir;
}

$mydb->setQuery("SELECT COUNT(*) AS total FROM `".$table."`");
$totalRow = $mydb->loadSingleResult();
$totalRecords = $totalRow ? intval($totalRow->total) : 0;

$mydb->setQuery("SELECT COUNT(*) AS total FROM `".$table."`".$where);
$filteredRow = $mydb->loadSingleResult();
$filteredRecords = $filteredRow ? intval($filteredRow->total) : 0;

$query = "SELECT * FROM `".$table."`".$where.$orderSql;

if (isset($_POST["length"]) && $_POST["length"] != -1) {
	$query .= " LIMIT " . intval($_POST['start']) . ", " . intval($_POST['length']);
}

$mydb->setQuery($query);
$rows = $mydb->loadResultList();

if (!$rows) {
	$rows = array();
}

$data = array();
$i = intval(isset($_POST['start']) ? $_POST['start'] : 0) + 1;
foreach ($rows as $row) {
	$sub_array = array();
	$sub_array[] = $i;
	foreach ($fields as $f) {
		$value = isset($row->$f) ? $row->$f : '';
		$shown = htmlspecialchars((string)generic_fk_label($f, $value, $table));

		if (strtoupper($f) === 'STATUS' && $shown !== '') {
			$good = array('active', 'enrolled', 'completed', 'passed', 'paid');
			$bad  = array('dropped', 'failed');
			$low  = strtolower($shown);
			$cls  = in_array($low, $good) ? 'badge-success' : (in_array($low, $bad) ? 'badge-danger' : 'badge-secondary');
			$shown = '<span class="badge '.$cls.'">'.$shown.'</span>';
		}

		$sub_array[] = $shown;
	}
	$pkVal = isset($row->{$rec->pk}) ? $row->{$rec->pk} : '';

	// Only tables flagged has_view (currently just Sections) get the
	// eye-icon View button - everything else keeps the plain Edit/Delete
	// pair it always had.
	$viewBtn = !empty($cfg['has_view'])
		? '<a href="index.php?t='.urlencode($table).'&view=view&id='.$pkVal.'" class="btn btn-info btn-xs" title="View"><span class="fa fa-eye"></span></a>'
		: '';

	$sub_array[] = '
		<div class="btn-group">
			'.$viewBtn.'
			<button type="button" class="btn btn-warning btn-xs editEntry" data-id="'.$pkVal.'" title="Edit"><span class="fa fa-edit"></span></button>
			<button type="button" class="btn btn-danger btn-xs deleteEntry" data-id="'.$pkVal.'" title="Delete"><span class="fa fa-trash"></span></button>
		</div>';
	$data[] = $sub_array;
	$i = $i + 1;
}

$output = array(
	'data'            => $data,
	'recordsTotal'    => $totalRecords,
	'recordsFiltered' => $filteredRecords
);
echo json_encode($output);
?>