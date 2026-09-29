<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;

header('Content-Type: application/json; charset=utf-8');


/* =========================================================
   GET INSTRUCTOR DATA FOR EDIT
   ========================================================= */

if (
    isset($_POST['action']) &&
    $_POST['action'] == 'get'
) {

    $id = isset($_POST['ID'])
        ? intval($_POST['ID'])
        : 0;

    if ($id <= 0) {

        echo json_encode([
            "success" => false,
            "message" => "Invalid instructor ID."
        ]);

        exit;
    }


    $mydb->setQuery("
        SELECT
            id,
            instructor_id,
            name,
            description
        FROM tblinstructor
        WHERE id = {$id}
        LIMIT 1
    ");

    $row = $mydb->loadSingleResult();


    if ($row) {

        echo json_encode([
            "success" => true,
            "data" => [
                "id" => $row->id,
                "instructor_id" => $row->instructor_id,
                "name" => $row->name,
                "description" => $row->description
            ]
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Instructor not found."
        ]);
    }

    exit;
}


/* =========================================================
   DATATABLE VARIABLES
   ========================================================= */

$draw = isset($_POST['draw'])
    ? intval($_POST['draw'])
    : 1;

$start = isset($_POST['start'])
    ? intval($_POST['start'])
    : 0;

$length = isset($_POST['length'])
    ? intval($_POST['length'])
    : 10;

$search = isset($_POST['search']['value'])
    ? trim($_POST['search']['value'])
    : '';


/* =========================================================
   TOTAL RECORDS
   ========================================================= */

$mydb->setQuery("
    SELECT COUNT(*) AS total
    FROM tblinstructor
");

$totalResult = $mydb->loadSingleResult();

$totalRecords = $totalResult
    ? intval($totalResult->total)
    : 0;


/* =========================================================
   SEARCH
   ========================================================= */

$where = "";

if ($search != '') {

    $searchSafe = $mydb->escape_value($search);

    $where = "
        WHERE
            instructor_id LIKE '%{$searchSafe}%'
            OR name LIKE '%{$searchSafe}%'
            OR description LIKE '%{$searchSafe}%'
    ";
}


/* =========================================================
   FILTERED RECORDS
   ========================================================= */

$mydb->setQuery("
    SELECT COUNT(*) AS total
    FROM tblinstructor
    {$where}
");

$filteredResult = $mydb->loadSingleResult();

$filteredRecords = $filteredResult
    ? intval($filteredResult->total)
    : 0;


/* =========================================================
   ORDER
   ========================================================= */

$orderColumns = [
    1 => 'instructor_id',
    2 => 'name',
    3 => 'description'
];

$orderBy  = 'name';
$orderDir = 'ASC';

if (isset($_POST['order'][0]['column'])) {

    $col = intval($_POST['order'][0]['column']);

    if (isset($orderColumns[$col])) {
        $orderBy = $orderColumns[$col];
    }
}

if (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) == 'desc') {
    $orderDir = 'DESC';
}

$limitSql = ($length > 0) ? "LIMIT {$start}, {$length}" : "";


/* =========================================================
   GET INSTRUCTORS
   ========================================================= */

$mydb->setQuery("
    SELECT
        id,
        instructor_id,
        name,
        description
    FROM tblinstructor
    {$where}
    ORDER BY {$orderBy} {$orderDir}
    {$limitSql}
");

$rows = $mydb->loadResultList();

if (!$rows) {
    $rows = [];
}


$data = [];

$number = $start + 1;


foreach ($rows as $row) {

    $id = intval($row->id);

    $instructorID = htmlspecialchars(
        $row->instructor_id ?? '',
        ENT_QUOTES,
        'UTF-8'
    );

    $name = htmlspecialchars(
        $row->name ?? '',
        ENT_QUOTES,
        'UTF-8'
    );

    $description = htmlspecialchars(
        $row->description ?? '',
        ENT_QUOTES,
        'UTF-8'
    );


    /* =====================================================
       ACTION BUTTONS
       ===================================================== */

    $action = '
        <div class="btn-group">
            <a href="index.php?view=view&id=' . $id . '" class="btn btn-info btn-xs" title="View"><i class="fas fa-eye"></i></a>
            <button type="button" class="btn btn-warning btn-xs editEntry" data-id="' . $id . '" title="Edit"><i class="fas fa-edit"></i></button>
            <a href="print.php?id=' . $id . '" target="_blank" class="btn btn-success btn-xs" title="Print"><i class="fas fa-print"></i></a>
            <button type="button" class="btn btn-danger btn-xs deleteEntry" data-id="' . $id . '" title="Delete"><i class="fas fa-trash"></i></button>
        </div>
    ';


    $data[] = [

        $number,
        $instructorID,
        $name,
        $description,
        $action

    ];


    $number++;
}


/* =========================================================
   DATATABLE RESPONSE
   ========================================================= */

echo json_encode([

    "draw" => $draw,

    "recordsTotal" => $totalRecords,

    "recordsFiltered" => $filteredRecords,

    "data" => $data

]);

?>