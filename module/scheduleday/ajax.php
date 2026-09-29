<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| GET SINGLE SCHEDULE DAY
|--------------------------------------------------------------------------
*/

if (isset($_POST['ID']) && $_POST['ID'] != '') {

    $ID = (int)$_POST['ID'];


    if ($ID <= 0) {

        echo json_encode([
            "error" => "Invalid schedule day ID."
        ]);

        exit;
    }


    $mydb->setQuery("

        SELECT

            id,

            name,

            description

        FROM tblscheduleday

        WHERE id = '".$ID."'

        LIMIT 1

    ");


    $result = $mydb->loadSingleResult();


    if (!$result) {

        echo json_encode([
            "error" => "Schedule day not found."
        ]);

        exit;
    }


    echo json_encode([

        "ID" => $result->id,

        "NAME" => $result->name,

        "DESCRIPTION" => $result->description

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

            name LIKE '%".$search."%'

            OR description LIKE '%".$search."%'

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

    FROM tblscheduleday

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

    FROM tblscheduleday

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
*/

$orderCols = [
    1 => 'name',
    2 => 'description'
];

$orderSql = "ORDER BY name ASC";

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
| GET SCHEDULE DAYS
|--------------------------------------------------------------------------
*/

$mydb->setQuery("

    SELECT

        id,

        name,

        description

    FROM tblscheduleday

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


    /*
    |--------------------------------------------------------------------------
    | NAME
    |--------------------------------------------------------------------------
    */

    $name = htmlspecialchars(
        (string)$row->name,
        ENT_QUOTES,
        'UTF-8'
    );



    /*
    |--------------------------------------------------------------------------
    | DESCRIPTION
    |--------------------------------------------------------------------------
    */

    $description = '';

    if (!empty($row->description)) {

        $description = htmlspecialchars(
            $row->description,
            ENT_QUOTES,
            'UTF-8'
        );

    }



    /*
    |--------------------------------------------------------------------------
    | ACTION
    |--------------------------------------------------------------------------
    */

    $action = '

        <div class="btn-group">

            <a href="index.php?view=view&id='.(int)$row->id.'" class="btn btn-info btn-xs" title="View">
                <i class="fa fa-eye"></i>
            </a>

            <button type="button" class="btn btn-warning btn-xs editEntry" ID="'.(int)$row->id.'" title="Edit">
                <i class="fa fa-edit"></i>
            </button>

            <a href="print.php?id='.(int)$row->id.'" target="_blank" class="btn btn-success btn-xs" title="Print">
                <i class="fa fa-print"></i>
            </a>

            <button type="button" class="btn btn-danger btn-xs deleteEntry" ID="'.(int)$row->id.'" title="Delete">
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

        $name,

        $description,

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