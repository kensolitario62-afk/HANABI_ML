<?php

require_once("../../include/initialize.php");

global $mydb;

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| GET SINGLE RECORD FOR EDIT
|--------------------------------------------------------------------------
| course_id comes from the Section so the Edit modal can rebuild
| Course -> Section -> Subject in order.
|--------------------------------------------------------------------------
*/

if (isset($_POST['ID']) && $_POST['ID'] != '') {

    $ID = (int)$_POST['ID'];

    $mydb->setQuery("
        SELECT
            ss.id,
            ss.department_id,
            sec.COURSE_ID AS course_id,
            ss.section_id,
            ss.subject_id,
            ss.classroom_id,
            ss.day_id,
            ss.time_id,
            ss.instructor_id,
            ss.semester,
            ss.school_year

        FROM tblsetschedule ss

        LEFT JOIN tblsections sec
            ON sec.SECTION_ID = ss.section_id

        WHERE ss.id = {$ID}

        LIMIT 1
    ");

    $row = $mydb->loadSingleResult();

    if ($row) {

        echo json_encode(array(
            "status" => "success",
            "data"   => $row
        ));

    } else {

        echo json_encode(array(
            "status"  => "error",
            "message" => "Schedule record not found."
        ));

    }

    exit;
}


/*
|--------------------------------------------------------------------------
| DATATABLE VALUES
|--------------------------------------------------------------------------
*/

$draw   = isset($_POST['draw'])   ? (int)$_POST['draw']   : 0;
$start  = isset($_POST['start'])  ? (int)$_POST['start']  : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;

$search = '';

if (isset($_POST['search']) && isset($_POST['search']['value'])) {
    $search = trim($_POST['search']['value']);
}


/*
|--------------------------------------------------------------------------
| TOTAL RECORDS
|--------------------------------------------------------------------------
*/

$mydb->setQuery("
    SELECT COUNT(*) AS total
    FROM tblsetschedule
");

$totalResult = $mydb->loadSingleResult();

$recordsTotal = $totalResult ? (int)$totalResult->total : 0;


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$where = '';

if ($search != '') {

    $search = $mydb->escape_value($search);

    $where = "
        WHERE
            sub.SUBJECT_CODE LIKE '%{$search}%'
            OR sub.SUBJECT_NAME LIKE '%{$search}%'
            OR d.name LIKE '%{$search}%'
            OR sec.SECTION_NAME LIKE '%{$search}%'
            OR cse.COURSE_CODE LIKE '%{$search}%'
            OR cse.COURSE_NAME LIKE '%{$search}%'
            OR c.name LIKE '%{$search}%'
            OR sd.name LIKE '%{$search}%'
            OR i.name LIKE '%{$search}%'
            OR ss.semester LIKE '%{$search}%'
            OR ss.school_year LIKE '%{$search}%'
    ";

}


/*
|--------------------------------------------------------------------------
| FILTERED RECORDS
|--------------------------------------------------------------------------
*/

$mydb->setQuery("
    SELECT COUNT(*) AS total

    FROM tblsetschedule ss

    LEFT JOIN tbldepartment d
        ON d.id = ss.department_id

    LEFT JOIN tblsections sec
        ON sec.SECTION_ID = ss.section_id

    LEFT JOIN tblcourses cse
        ON cse.COURSE_ID = sec.COURSE_ID

    LEFT JOIN tblsubjects sub
        ON sub.SUBJECT_ID = ss.subject_id

    LEFT JOIN tblclassroom c
        ON c.id = ss.classroom_id

    LEFT JOIN tblscheduleday sd
        ON sd.id = ss.day_id

    LEFT JOIN tblinstructor i
        ON i.id = ss.instructor_id

    {$where}
");

$filteredResult = $mydb->loadSingleResult();

$recordsFiltered = $filteredResult ? (int)$filteredResult->total : 0;


/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$orderColumn    = 1;
$orderDirection = "ASC";

if (isset($_POST['order'][0]['column'])) {
    $orderColumn = (int)$_POST['order'][0]['column'];
}

if (isset($_POST['order'][0]['dir'])) {
    $orderDirection = strtoupper($_POST['order'][0]['dir']);
}

if (!in_array($orderDirection, array("ASC", "DESC"))) {
    $orderDirection = "ASC";
}

$orderColumns = array(
    0 => "ss.id",
    1 => "st.time_start",
    2 => "sd.name",
    3 => "sub.SUBJECT_CODE",
    4 => "sub.SUBJECT_NAME",
    5 => "sub.UNITS",
    6 => "c.name",
    7 => "i.name",
    8 => "sec.SECTION_NAME",
    9 => "ss.id"
);

if (!isset($orderColumns[$orderColumn])) {
    $orderColumn = 1;
}

$orderBy = $orderColumns[$orderColumn];


/*
|--------------------------------------------------------------------------
| LIMIT
|--------------------------------------------------------------------------
*/

$limitSql = '';

if ($length != -1) {
    $limitSql = "LIMIT " . $start . ", " . $length;
}


/*
|--------------------------------------------------------------------------
| GET DATA
|--------------------------------------------------------------------------
*/

$mydb->setQuery("

    SELECT

        ss.id,

        d.name AS DEPARTMENT_NAME,

        sec.SECTION_ID,
        sec.SECTION_NAME,
        sec.YEAR_LEVEL,

        cse.COURSE_ID,
        cse.COURSE_CODE,
        cse.COURSE_NAME,

        sub.SUBJECT_CODE,
        sub.SUBJECT_NAME,
        sub.UNITS,

        c.name AS CLASSROOM_NAME,

        sd.name AS DAY_NAME,

        st.time_start,
        st.time_end,

        i.name AS INSTRUCTOR_NAME,

        ss.semester,
        ss.school_year

    FROM tblsetschedule ss

    LEFT JOIN tbldepartment d
        ON d.id = ss.department_id

    LEFT JOIN tblsections sec
        ON sec.SECTION_ID = ss.section_id

    LEFT JOIN tblcourses cse
        ON cse.COURSE_ID = sec.COURSE_ID

    LEFT JOIN tblsubjects sub
        ON sub.SUBJECT_ID = ss.subject_id

    LEFT JOIN tblclassroom c
        ON c.id = ss.classroom_id

    LEFT JOIN tblscheduleday sd
        ON sd.id = ss.day_id

    LEFT JOIN tblscheduletime st
        ON st.id = ss.time_id

    LEFT JOIN tblinstructor i
        ON i.id = ss.instructor_id

    {$where}

    ORDER BY
        {$orderBy} {$orderDirection}

    {$limitSql}

");

$rows = $mydb->loadResultList();

$data = array();


/*
|--------------------------------------------------------------------------
| BUILD DATATABLE DATA
|--------------------------------------------------------------------------
*/

foreach ($rows as $row) {

    if (!empty($row->time_start) && !empty($row->time_end)) {

        $time = date("h:i A", strtotime($row->time_start))
            . " - "
            . date("h:i A", strtotime($row->time_end));

    } else {

        $time = "Not Set";

    }

    $day        = !empty($row->DAY_NAME)        ? htmlspecialchars($row->DAY_NAME)        : "Not Set";
    $code       = !empty($row->SUBJECT_CODE)    ? htmlspecialchars($row->SUBJECT_CODE)    : "N/A";
    $subject    = !empty($row->SUBJECT_NAME)    ? htmlspecialchars($row->SUBJECT_NAME)    : "N/A";
    $unit       = $row->UNITS !== null          ? htmlspecialchars($row->UNITS)           : "0";
    $room       = !empty($row->CLASSROOM_NAME)  ? htmlspecialchars($row->CLASSROOM_NAME)  : "Not Assigned";
    $instructor = !empty($row->INSTRUCTOR_NAME) ? htmlspecialchars($row->INSTRUCTOR_NAME) : "Not Assigned";
    $section    = !empty($row->SECTION_NAME)    ? htmlspecialchars($row->SECTION_NAME)    : "N/A";

    $rowId = (int)$row->id;

    $action = '

        <div class="btn-group">

            <a
                href="' . WEB_ROOT . 'module/setschedule/index.php?view=view&id=' . $rowId . '"
                class="btn btn-info btn-sm"
                title="View"
            >
                <i class="fa fa-eye"></i>
            </a>

            <button
                type="button"
                class="btn btn-primary btn-sm editEntry"
                ID="' . $rowId . '"
                title="Edit"
            >
                <i class="fa fa-edit"></i>
            </button>

            <button
                type="button"
                class="btn btn-danger btn-sm deleteEntry"
                ID="' . $rowId . '"
                title="Delete"
            >
                <i class="fa fa-trash"></i>
            </button>

        </div>

    ';

    $data[] = array(

        $start + count($data) + 1,   /* # column: running row number */

        $time,
        $day,
        $code,
        $subject,
        $unit,
        $room,
        $instructor,
        $section,
        $action

    );

}


/*
|--------------------------------------------------------------------------
| JSON RESPONSE
|--------------------------------------------------------------------------
*/

echo json_encode(array(
    "draw"            => $draw,
    "recordsTotal"    => $recordsTotal,
    "recordsFiltered" => $recordsFiltered,
    "data"            => $data
));

exit;

?>