<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| GET SINGLE PATIENT
|--------------------------------------------------------------------------
*/

if (isset($_POST['PATIENT_ID']) && $_POST['PATIENT_ID'] != '') {

    $PATIENT_ID = (int)$_POST['PATIENT_ID'];


    if ($PATIENT_ID <= 0) {

        echo json_encode([
            "error" => "Invalid patient ID."
        ]);

        exit;
    }


    $mydb->setQuery("

        SELECT

            PATIENT_ID,

            FNAME,

            MNAME,

            LNAME,

            SEX,

            BDAY,

            AGE,

            CONTACT_NO,

            ADDRESS,

            STATUS

        FROM tblpatients

        WHERE PATIENT_ID = '".$PATIENT_ID."'

        LIMIT 1

    ");


    $result = $mydb->loadSingleResult();


    if (!$result) {

        echo json_encode([
            "error" => "Patient not found."
        ]);

        exit;
    }


    echo json_encode([

        "PATIENT_ID" => $result->PATIENT_ID,

        "FNAME" => $result->FNAME,

        "MNAME" => $result->MNAME,

        "LNAME" => $result->LNAME,

        "SEX" => $result->SEX,

        "BDAY" => $result->BDAY !== null ? $result->BDAY : '',

        "AGE" => $result->AGE !== null ? $result->AGE : '',

        "CONTACT_NO" => $result->CONTACT_NO,

        "ADDRESS" => $result->ADDRESS,

        "STATUS" => $result->STATUS

    ]);

    exit;
}



/*
|--------------------------------------------------------------------------
| DATATABLE VARIABLES
|--------------------------------------------------------------------------
*/

$draw = isset($_POST['draw'])
    ? (int)$_POST['draw']
    : 0;


$start = isset($_POST['start'])
    ? (int)$_POST['start']
    : 0;


$length = isset($_POST['length'])
    ? (int)$_POST['length']
    : 10;

if ($start < 0) {
    $start = 0;
}


$search = '';


if (
    isset($_POST['search']) &&
    isset($_POST['search']['value'])
) {

    $search = trim($_POST['search']['value']);

}



/*
|--------------------------------------------------------------------------
| SEARCH CONDITION
|--------------------------------------------------------------------------
*/

$where = '';


if ($search != '') {

    $search = $mydb->escape_value($search);


    $where = "

        WHERE

            LNAME LIKE '%".$search."%'

            OR FNAME LIKE '%".$search."%'

            OR CONTACT_NO LIKE '%".$search."%'

    ";

}



/*
|--------------------------------------------------------------------------
| TOTAL RECORDS (UNFILTERED)
|--------------------------------------------------------------------------
*/

$mydb->setQuery("

    SELECT

        COUNT(*) AS total

    FROM tblpatients

");


$totalResult = $mydb->loadSingleResult();


$recordsTotal = $totalResult
    ? (int)$totalResult->total
    : 0;



/*
|--------------------------------------------------------------------------
| FILTERED RECORDS
|--------------------------------------------------------------------------
*/

$mydb->setQuery("

    SELECT

        COUNT(*) AS total

    FROM tblpatients

    ".$where."

");


$filteredResult = $mydb->loadSingleResult();


$recordsFiltered = $filteredResult
    ? (int)$filteredResult->total
    : 0;



/*
|--------------------------------------------------------------------------
| ORDER + LIMIT
|--------------------------------------------------------------------------
|
| Column order must be whitelisted - the previous version built
| "ORDER BY {raw column index} {raw dir}" directly from user
| input, which is a SQL injection vector (dir was never checked
| against asc/desc before being concatenated in).
|
*/

$orderCols = [
    1 => 'LNAME',
    2 => 'FNAME',
    3 => 'SEX',
    4 => 'CONTACT_NO',
    5 => 'STATUS'
];

$orderSql = "ORDER BY LNAME ASC";

if (isset($_POST['order'][0]['column'])) {

    $ci  = (int)$_POST['order'][0]['column'];

    $dir = (
        isset($_POST['order'][0]['dir']) &&
        strtolower($_POST['order'][0]['dir']) == 'desc'
    ) ? 'DESC' : 'ASC';

    if (isset($orderCols[$ci])) {
        $orderSql = "ORDER BY " . $orderCols[$ci] . " " . $dir;
    }
}

$limitSql = ($length > 0)
    ? "LIMIT " . $start . ", " . $length
    : "";



/*
|--------------------------------------------------------------------------
| GET PATIENTS
|--------------------------------------------------------------------------
*/

$mydb->setQuery("

    SELECT

        PATIENT_ID,

        FNAME,

        LNAME,

        SEX,

        CONTACT_NO,

        STATUS

    FROM tblpatients

    ".$where."

    ".$orderSql."

    ".$limitSql."

");


$rows = $mydb->loadResultList();

if (!$rows) {
    $rows = [];
}



/*
|--------------------------------------------------------------------------
| CREATE DATA
|--------------------------------------------------------------------------
*/

$data = [];

$number = $start + 1;


foreach ($rows as $row) {


    $lname = htmlspecialchars((string)$row->LNAME, ENT_QUOTES, 'UTF-8');

    $fname = htmlspecialchars((string)$row->FNAME, ENT_QUOTES, 'UTF-8');

    $sex = htmlspecialchars((string)$row->SEX, ENT_QUOTES, 'UTF-8');

    $contact = htmlspecialchars((string)$row->CONTACT_NO, ENT_QUOTES, 'UTF-8');



    /*
    |--------------------------------------------------------------------------
    | STATUS BADGE
    |--------------------------------------------------------------------------
    */

    $statusRaw = (string)$row->STATUS;

    $badgeClass = ($statusRaw == 'Active') ? 'badge-success' : 'badge-secondary';

    $status = '<span class="badge '.$badgeClass.'">'.htmlspecialchars($statusRaw, ENT_QUOTES, 'UTF-8').'</span>';



    /*
    |--------------------------------------------------------------------------
    | ACTION
    |--------------------------------------------------------------------------
    */

    $action = '

        <div class="btn-group">

            <a href="index.php?view=view&id='.(int)$row->PATIENT_ID.'" class="btn btn-info btn-xs" title="View">
                <i class="fa fa-eye"></i>
            </a>

            <button type="button" class="btn btn-warning btn-xs editEntry" PATIENT_ID="'.(int)$row->PATIENT_ID.'" title="Edit">
                <i class="fa fa-edit"></i>
            </button>

            <button type="button" class="btn btn-danger btn-xs deleteEntry" PATIENT_ID="'.(int)$row->PATIENT_ID.'" title="Delete">
                <i class="fa fa-trash"></i>
            </button>

        </div>

    ';



    /*
    |--------------------------------------------------------------------------
    | ADD ROW
    |--------------------------------------------------------------------------
    */

    $data[] = [

        $number,

        $lname,

        $fname,

        $sex,

        $contact,

        $status,

        $action

    ];


    $number++;

}



/*
|--------------------------------------------------------------------------
| RETURN JSON
|--------------------------------------------------------------------------
*/

echo json_encode([

    "draw" => $draw,

    "recordsTotal" => $recordsTotal,

    "recordsFiltered" => $recordsFiltered,

    "data" => $data

]);


exit;

?>