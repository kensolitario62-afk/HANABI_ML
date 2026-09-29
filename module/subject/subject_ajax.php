<?php

require_once("../../include/initialize.php");
global $mydb;


/* =========================================================
   GET SINGLE SUBJECT FOR EDIT
   ========================================================= */

if (isset($_POST['SUBJECT_ID'])) {

    $output = array();

    $SUBJECT_ID = intval($_POST['SUBJECT_ID']);

    $query = "SELECT *
              FROM tblsubjects
              WHERE SUBJECT_ID = '".$SUBJECT_ID."'
              LIMIT 1";

    $mydb->setQuery($query);
    $result = $mydb->loadResultList();

    foreach ($result as $row) {

        $output["SUBJECT_ID"]   = $row->SUBJECT_ID;
        $output["SUBJECT_CODE"] = $row->SUBJECT_CODE;
        $output["SUBJECT_NAME"] = $row->SUBJECT_NAME;
        $output["UNITS"]        = $row->UNITS;
        $output["PRICE_PER_UNIT"] = $row->PRICE_PER_UNIT;
        $output["COURSE_ID"]    = $row->COURSE_ID;
        $output["YEAR_LEVEL"]   = $row->YEAR_LEVEL;
        $output["SEMESTER"]     = $row->SEMESTER;
    }

    echo json_encode($output);
    exit;
}


/* =========================================================
   DATATABLE SUBJECT LIST
   ========================================================= */

$output = array();

$query = "SELECT 
            s.*,
            c.COURSE_CODE,
            c.COURSE_NAME
          FROM tblsubjects s
          LEFT JOIN tblcourses c 
          ON c.COURSE_ID = s.COURSE_ID";


/* =========================================================
   SEARCH
   ========================================================= */

if (isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '') {

    $search = $mydb->escape_value($_POST["search"]["value"]);

    $query .= " WHERE
                s.SUBJECT_NAME LIKE '%".$search."%'
                OR s.SUBJECT_CODE LIKE '%".$search."%'
                OR c.COURSE_CODE LIKE '%".$search."%'
                OR c.COURSE_NAME LIKE '%".$search."%'
                OR s.YEAR_LEVEL LIKE '%".$search."%'
                OR s.SEMESTER LIKE '%".$search."%'";
}


/* =========================================================
   ORDERING
   ========================================================= */

$orderColumns = array(
    0 => 's.SUBJECT_ID',
    1 => 's.SUBJECT_CODE',
    2 => 's.SUBJECT_NAME',
    3 => 's.UNITS',
    4 => 'c.COURSE_CODE',
    5 => 's.YEAR_LEVEL',
    6 => 's.SEMESTER'
);

if (isset($_POST["order"])) {

    $columnIndex = intval($_POST['order'][0]['column']);

    $direction = strtoupper($_POST['order'][0]['dir']);

    if ($direction != 'ASC' && $direction != 'DESC') {
        $direction = 'ASC';
    }

    if (isset($orderColumns[$columnIndex])) {

        $query .= " ORDER BY "
                . $orderColumns[$columnIndex]
                . " "
                . $direction;
    }

} else {

    $query .= " ORDER BY s.SUBJECT_NAME ASC";
}


/* =========================================================
   LIMIT
   ========================================================= */

if (isset($_POST["length"]) && $_POST["length"] != -1) {

    $start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = intval($_POST['length']);

    $query .= " LIMIT ".$start.", ".$length;
}


/* =========================================================
   GET SUBJECTS
   ========================================================= */

$mydb->setQuery($query);

$cur = $mydb->loadResultList();

$data = array();

$filtered_rows = $mydb->num_rows();

$i = 1;


/* =========================================================
   BUILD DATATABLE ROWS
   ========================================================= */

foreach ($cur as $result) {

    $sub_array = array();

    $sub_array[] = $i;

    $sub_array[] = $result->SUBJECT_CODE;

    $sub_array[] = $result->SUBJECT_NAME;

    $sub_array[] = $result->UNITS;

    $sub_array[] = isset($result->COURSE_CODE)
                    ? $result->COURSE_CODE
                    : '';

    $sub_array[] = $result->YEAR_LEVEL;

    $sub_array[] = $result->SEMESTER;


    /* =====================================================
       ACTION BUTTONS
       ===================================================== */

    $sub_array[] = '
        <div class="btn-group">
            <button type="button" name="update" SUBJECT_ID="'.$result->SUBJECT_ID.'" class="btn btn-warning btn-xs editEntry" title="Edit"><span class="fa fa-edit"></span></button>
            <a href="index.php?view=view&id='.$result->SUBJECT_ID.'" class="btn btn-info btn-xs" title="View"><span class="fa fa-eye"></span></a>
            <a href="print.php?id='.$result->SUBJECT_ID.'" target="_blank" class="btn btn-success btn-xs" title="Print"><span class="fa fa-print"></span></a>
            <button type="button" name="delete" SUBJECT_ID="'.$result->SUBJECT_ID.'" class="btn btn-danger btn-xs deleteEntry" title="Delete"><span class="fa fa-trash"></span></button>
        </div>
    ';


    $data[] = $sub_array;

    $i++;
}


/* =========================================================
   TOTAL RECORDS
   ========================================================= */

function get_total_all_records()
{
    global $mydb;

    $statement = "SELECT * FROM tblsubjects";

    $mydb->setQuery($statement);

    return $mydb->num_rows();
}


/* =========================================================
   DATATABLE OUTPUT
   ========================================================= */

$output = array(

    "data" => $data,

    "recordsTotal" => get_total_all_records(),

    "recordsFiltered" => get_total_all_records()

);

echo json_encode($output);

?>