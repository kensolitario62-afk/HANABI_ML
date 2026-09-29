<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;


/*
|--------------------------------------------------------------------------
| GET ONE DEPARTMENT
|--------------------------------------------------------------------------
|
| Used by the Edit modal.
|
*/

if (isset($_POST['ID']) && $_POST['ID'] != '') {

    $ID = (int)$_POST['ID'];


    $output = array(

        "status" => "error",

        "message" => "Department not found.",

        "data" => array(

            "id" => "",

            "name" => "",

            "description" => ""

        )

    );


    $query = "
        SELECT
            id,
            name,
            description
        FROM tbldepartment
        WHERE id = {$ID}
        LIMIT 1
    ";


    $mydb->setQuery($query);


    $result = $mydb->loadSingleResult();


    if ($result) {

        $output = array(

            "status" => "success",

            "message" => "Department loaded successfully.",

            "data" => array(

                "id" => (int)$result->id,

                "name" => $result->name,

                "description" => $result->description

            )

        );

    }


    header(
        'Content-Type: application/json; charset=utf-8'
    );


    echo json_encode($output);

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



/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = '';

if (
    isset($_POST['search']['value']) &&
    $_POST['search']['value'] != ''
) {

    $search = trim(
        $_POST['search']['value']
    );

}



/*
|--------------------------------------------------------------------------
| TOTAL RECORDS
|--------------------------------------------------------------------------
*/

$mydb->setQuery("
    SELECT COUNT(*) AS total
    FROM tbldepartment
");


$total = $mydb->loadSingleResult();


$recordsTotal = $total
    ? (int)$total->total
    : 0;



/*
|--------------------------------------------------------------------------
| SEARCH CONDITION
|--------------------------------------------------------------------------
*/

$where = '';


if ($search != '') {

    $searchSafe = $mydb->escape_value($search);


    $where = "
        WHERE
            name LIKE '%{$searchSafe}%'
            OR description LIKE '%{$searchSafe}%'
    ";

}



/*
|--------------------------------------------------------------------------
| FILTERED RECORDS
|--------------------------------------------------------------------------
*/

$mydb->setQuery("
    SELECT COUNT(*) AS total
    FROM tbldepartment
    {$where}
");


$filtered = $mydb->loadSingleResult();


$recordsFiltered = $filtered
    ? (int)$filtered->total
    : 0;



/*
|--------------------------------------------------------------------------
| ALLOWED ORDER COLUMNS
|--------------------------------------------------------------------------
*/

$allowed_columns = array(

    0 => 'id',

    1 => 'name',

    2 => 'description'

);


$orderColumn = 1;

$orderDir = 'asc';


if (isset($_POST['order'][0]['column'])) {

    $requestedColumn =
        (int)$_POST['order'][0]['column'];


    if (
        isset(
            $allowed_columns[$requestedColumn]
        )
    ) {

        $orderColumn =
            $requestedColumn;

    }

}


if (isset($_POST['order'][0]['dir'])) {

    $requestedDir =
        strtolower(
            $_POST['order'][0]['dir']
        );


    if (
        $requestedDir == 'asc' ||
        $requestedDir == 'desc'
    ) {

        $orderDir =
            $requestedDir;

    }

}



/*
|--------------------------------------------------------------------------
| MAIN QUERY
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        id,
        name,
        description
    FROM tbldepartment
    {$where}

    ORDER BY
        {$allowed_columns[$orderColumn]}
        {$orderDir}
";



/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/

if ($length != -1) {

    if ($start < 0) {

        $start = 0;

    }


    if ($length < 1) {

        $length = 10;

    }


    $query .= "
        LIMIT {$start}, {$length}
    ";

}



/*
|--------------------------------------------------------------------------
| EXECUTE
|--------------------------------------------------------------------------
*/

$mydb->setQuery($query);


$cur = $mydb->loadResultList();

if (!$cur) {
    $cur = array();
}



/*
|--------------------------------------------------------------------------
| BUILD DATA
|--------------------------------------------------------------------------
*/

$data = array();


$number = $start + 1;


foreach ($cur as $result) {


    $name = htmlspecialchars(

        $result->name,

        ENT_QUOTES,

        'UTF-8'

    );


    $description = htmlspecialchars(

        $result->description,

        ENT_QUOTES,

        'UTF-8'

    );


    $id = (int)$result->id;


    $sub_array = array();


    /*
    | Number
    */

    $sub_array[] = $number;


    /*
    | Department Name
    */

    $sub_array[] = $name;


    /*
    | Description
    */

    $sub_array[] = $description;


    /*
    | Action
    */

    $sub_array[] = '
        <div class="btn-group">
            <a href="index.php?view=view&id=' . $id . '" class="btn btn-info btn-xs" title="View"><span class="fa fa-eye"></span></a>
            <button type="button" class="btn btn-warning btn-xs editDepartment" data-id="' . $id . '" title="Edit"><span class="fa fa-edit"></span></button>
            <a href="print.php?id=' . $id . '" target="_blank" class="btn btn-success btn-xs" title="Print"><span class="fa fa-print"></span></a>
            <button type="button" class="btn btn-danger btn-xs deleteDepartment" data-id="' . $id . '" title="Delete"><span class="fa fa-trash"></span></button>
        </div>
    ';


    $data[] = $sub_array;


    $number++;

}



/*
|--------------------------------------------------------------------------
| DATATABLE JSON
|--------------------------------------------------------------------------
*/

$output = array(

    "draw" => $draw,

    "recordsTotal" => $recordsTotal,

    "recordsFiltered" => $recordsFiltered,

    "data" => $data

);


header(
    'Content-Type: application/json; charset=utf-8'
);


echo json_encode($output);


exit;

?>