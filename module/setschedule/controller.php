<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;

$action = isset($_GET['action']) ? trim($_GET['action']) : '';

switch ($action) {

    case 'add':
        doInsert();
        break;

    case 'edit':
        doUpdate();
        break;

    case 'delete':
        doDelete();
        break;

    case 'get_sections':
        getSections();
        break;

    case 'get_subjects':
        getSubjects();
        break;

    default:
        redirect("index.php");
        break;
}


/*
|--------------------------------------------------------------------------
| ADD SET SCHEDULE
|--------------------------------------------------------------------------
*/

function doInsert()
{
    global $mydb;

    $department_id = isset($_POST['DEPARTMENT_ID'])
        ? intval($_POST['DEPARTMENT_ID'])
        : 0;

    $course_id = isset($_POST['COURSE_ID'])
        ? intval($_POST['COURSE_ID'])
        : 0;

    $section_id = isset($_POST['SECTION_ID'])
        ? intval($_POST['SECTION_ID'])
        : 0;

    $subject_id = isset($_POST['SUBJECT_ID'])
        ? intval($_POST['SUBJECT_ID'])
        : 0;

    $classroom_id = isset($_POST['CLASSROOM_ID'])
        ? intval($_POST['CLASSROOM_ID'])
        : 0;

    $day_id = isset($_POST['DAY_ID'])
        ? intval($_POST['DAY_ID'])
        : 0;

    $time_id = isset($_POST['TIME_ID'])
        ? intval($_POST['TIME_ID'])
        : 0;

    $instructor_id = isset($_POST['INSTRUCTOR_ID'])
        ? intval($_POST['INSTRUCTOR_ID'])
        : 0;

    $semester = isset($_POST['SEMESTER'])
        ? trim($_POST['SEMESTER'])
        : '';

    $school_year = isset($_POST['SCHOOL_YEAR'])
        ? trim($_POST['SCHOOL_YEAR'])
        : '';


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $department_id <= 0 ||
        $course_id <= 0 ||
        $section_id <= 0 ||
        $subject_id <= 0 ||
        $classroom_id <= 0 ||
        $day_id <= 0 ||
        $time_id <= 0 ||
        $semester == '' ||
        $school_year == ''
    ) {

        message(
            "Please complete all required Schedule fields.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SECTION
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT
            s.SECTION_ID,
            s.COURSE_ID,
            s.YEAR_LEVEL,
            sy.SCHOOL_YEAR
        FROM tblsections s
        LEFT JOIN tblschoolyear sy
            ON sy.SY_ID = s.SY_ID
        WHERE s.SECTION_ID = {$section_id}
        LIMIT 1
    ");

    $section = $mydb->loadSingleResult();


    if (!$section) {

        message(
            "Selected section was not found.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | MAKE SURE COURSE MATCHES SECTION
    |--------------------------------------------------------------------------
    */

    if (intval($section->COURSE_ID) != $course_id) {

        message(
            "The selected Course and Section do not match.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | USE SECTION SCHOOL YEAR
    |--------------------------------------------------------------------------
    */

    if (!empty($section->SCHOOL_YEAR)) {
        $school_year = $section->SCHOOL_YEAR;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SUBJECT
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT SUBJECT_ID
        FROM tblsubjects
        WHERE SUBJECT_ID = {$subject_id}
        AND COURSE_ID = {$course_id}
        AND YEAR_LEVEL = '" . $mydb->escape_value($section->YEAR_LEVEL) . "'
        AND SEMESTER = '" . $mydb->escape_value($semester) . "'
        LIMIT 1
    ");

    $subject = $mydb->loadSingleResult();


    if (!$subject) {

        message(
            "The selected Subject does not belong to the selected Course, Year Level, or Semester.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE SUBJECT FOR SECTION
    |--------------------------------------------------------------------------
    */

    $safeSemester = $mydb->escape_value($semester);
    $safeSchoolYear = $mydb->escape_value($school_year);

    $mydb->setQuery("
        SELECT id
        FROM tblsetschedule
        WHERE section_id = {$section_id}
        AND subject_id = {$subject_id}
        AND semester = '{$safeSemester}'
        AND school_year = '{$safeSchoolYear}'
        LIMIT 1
    ");

    $duplicate = $mydb->loadSingleResult();


    if ($duplicate) {

        message(
            "This subject is already scheduled for the selected section.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | GET SELECTED TIME
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT time_start, time_end
        FROM tblscheduletime
        WHERE id = {$time_id}
        LIMIT 1
    ");

    $selectedTime = $mydb->loadSingleResult();


    if (!$selectedTime) {

        message(
            "Selected Schedule Time was not found.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK CLASSROOM CONFLICT
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT
            ss.id
        FROM tblsetschedule ss
        INNER JOIN tblscheduletime st
            ON st.id = ss.time_id
        WHERE ss.classroom_id = {$classroom_id}
        AND ss.day_id = {$day_id}
        AND ss.semester = '{$safeSemester}'
        AND ss.school_year = '{$safeSchoolYear}'
        AND st.time_start < '{$selectedTime->time_end}'
        AND st.time_end > '{$selectedTime->time_start}'
        LIMIT 1
    ");

    $roomConflict = $mydb->loadSingleResult();


    if ($roomConflict) {

        message(
            "Schedule conflict: the selected classroom is already occupied at this time.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK INSTRUCTOR CONFLICT
    |--------------------------------------------------------------------------
    */

    if ($instructor_id > 0) {

        $mydb->setQuery("
            SELECT
                ss.id
            FROM tblsetschedule ss
            INNER JOIN tblscheduletime st
                ON st.id = ss.time_id
            WHERE ss.instructor_id = {$instructor_id}
            AND ss.day_id = {$day_id}
            AND ss.semester = '{$safeSemester}'
            AND ss.school_year = '{$safeSchoolYear}'
            AND st.time_start < '{$selectedTime->time_end}'
            AND st.time_end > '{$selectedTime->time_start}'
            LIMIT 1
        ");

        $instructorConflict = $mydb->loadSingleResult();


        if ($instructorConflict) {

            message(
                "Schedule conflict: the selected instructor already has a class at this time.",
                "error"
            );

            redirect("index.php");

            return;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SECTION CONFLICT
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT
            ss.id
        FROM tblsetschedule ss
        INNER JOIN tblscheduletime st
            ON st.id = ss.time_id
        WHERE ss.section_id = {$section_id}
        AND ss.day_id = {$day_id}
        AND ss.semester = '{$safeSemester}'
        AND ss.school_year = '{$safeSchoolYear}'
        AND st.time_start < '{$selectedTime->time_end}'
        AND st.time_end > '{$selectedTime->time_start}'
        LIMIT 1
    ");

    $sectionConflict = $mydb->loadSingleResult();


    if ($sectionConflict) {

        message(
            "Schedule conflict: the selected section already has a class at this time.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    $query = "
        INSERT INTO tblsetschedule
        (
            department_id,
            section_id,
            subject_id,
            classroom_id,
            day_id,
            time_id,
            instructor_id,
            semester,
            school_year
        )
        VALUES
        (
            {$department_id},
            {$section_id},
            {$subject_id},
            {$classroom_id},
            {$day_id},
            {$time_id},
            " . ($instructor_id > 0 ? $instructor_id : "NULL") . ",
            '{$safeSemester}',
            '{$safeSchoolYear}'
        )
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Schedule has been added successfully.",
            "success"
        );

    } else {

        message(
            "Failed to add Schedule.",
            "error"
        );
    }


    redirect("index.php");
}


/*
|--------------------------------------------------------------------------
| UPDATE SET SCHEDULE
|--------------------------------------------------------------------------
*/

function doUpdate()
{
    global $mydb;

    $id = isset($_POST['ID'])
        ? intval($_POST['ID'])
        : 0;

    $department_id = isset($_POST['DEPARTMENT_ID'])
        ? intval($_POST['DEPARTMENT_ID'])
        : 0;

    $course_id = isset($_POST['COURSE_ID'])
        ? intval($_POST['COURSE_ID'])
        : 0;

    $section_id = isset($_POST['SECTION_ID'])
        ? intval($_POST['SECTION_ID'])
        : 0;

    $subject_id = isset($_POST['SUBJECT_ID'])
        ? intval($_POST['SUBJECT_ID'])
        : 0;

    $classroom_id = isset($_POST['CLASSROOM_ID'])
        ? intval($_POST['CLASSROOM_ID'])
        : 0;

    $day_id = isset($_POST['DAY_ID'])
        ? intval($_POST['DAY_ID'])
        : 0;

    $time_id = isset($_POST['TIME_ID'])
        ? intval($_POST['TIME_ID'])
        : 0;

    $instructor_id = isset($_POST['INSTRUCTOR_ID'])
        ? intval($_POST['INSTRUCTOR_ID'])
        : 0;

    $semester = isset($_POST['SEMESTER'])
        ? trim($_POST['SEMESTER'])
        : '';

    $school_year = isset($_POST['SCHOOL_YEAR'])
        ? trim($_POST['SCHOOL_YEAR'])
        : '';


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($id <= 0) {

        message(
            "Invalid Schedule ID.",
            "error"
        );

        redirect("index.php");

        return;
    }


    if (
        $department_id <= 0 ||
        $course_id <= 0 ||
        $section_id <= 0 ||
        $subject_id <= 0 ||
        $classroom_id <= 0 ||
        $day_id <= 0 ||
        $time_id <= 0 ||
        $semester == '' ||
        $school_year == ''
    ) {

        message(
            "Please complete all required Schedule fields.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK EXISTING RECORD
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblsetschedule
        WHERE id = {$id}
        LIMIT 1
    ");

    $record = $mydb->loadSingleResult();


    if (!$record) {

        message(
            "Schedule record not found.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SECTION
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT
            s.SECTION_ID,
            s.COURSE_ID,
            s.YEAR_LEVEL,
            sy.SCHOOL_YEAR
        FROM tblsections s
        LEFT JOIN tblschoolyear sy
            ON sy.SY_ID = s.SY_ID
        WHERE s.SECTION_ID = {$section_id}
        LIMIT 1
    ");

    $section = $mydb->loadSingleResult();


    if (!$section) {

        message(
            "Selected section was not found.",
            "error"
        );

        redirect("index.php");

        return;
    }


    if (intval($section->COURSE_ID) != $course_id) {

        message(
            "The selected Course and Section do not match.",
            "error"
        );

        redirect("index.php");

        return;
    }


    if (!empty($section->SCHOOL_YEAR)) {
        $school_year = $section->SCHOOL_YEAR;
    }


    $safeSemester = $mydb->escape_value($semester);
    $safeSchoolYear = $mydb->escape_value($school_year);
    $safeYearLevel = $mydb->escape_value($section->YEAR_LEVEL);


    /*
    |--------------------------------------------------------------------------
    | CHECK SUBJECT
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT SUBJECT_ID
        FROM tblsubjects
        WHERE SUBJECT_ID = {$subject_id}
        AND COURSE_ID = {$course_id}
        AND YEAR_LEVEL = '{$safeYearLevel}'
        AND SEMESTER = '{$safeSemester}'
        LIMIT 1
    ");

    $subject = $mydb->loadSingleResult();


    if (!$subject) {

        message(
            "The selected Subject does not match the Course, Year Level, or Semester.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblsetschedule
        WHERE section_id = {$section_id}
        AND subject_id = {$subject_id}
        AND semester = '{$safeSemester}'
        AND school_year = '{$safeSchoolYear}'
        AND id != {$id}
        LIMIT 1
    ");

    $duplicate = $mydb->loadSingleResult();


    if ($duplicate) {

        message(
            "This subject is already scheduled for the selected section.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | GET TIME
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT time_start, time_end
        FROM tblscheduletime
        WHERE id = {$time_id}
        LIMIT 1
    ");

    $selectedTime = $mydb->loadSingleResult();


    if (!$selectedTime) {

        message(
            "Selected Schedule Time was not found.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CLASSROOM CONFLICT
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT ss.id
        FROM tblsetschedule ss
        INNER JOIN tblscheduletime st
            ON st.id = ss.time_id
        WHERE ss.classroom_id = {$classroom_id}
        AND ss.day_id = {$day_id}
        AND ss.semester = '{$safeSemester}'
        AND ss.school_year = '{$safeSchoolYear}'
        AND ss.id != {$id}
        AND st.time_start < '{$selectedTime->time_end}'
        AND st.time_end > '{$selectedTime->time_start}'
        LIMIT 1
    ");

    $roomConflict = $mydb->loadSingleResult();


    if ($roomConflict) {

        message(
            "Schedule conflict: the selected classroom is already occupied at this time.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | INSTRUCTOR CONFLICT
    |--------------------------------------------------------------------------
    */

    if ($instructor_id > 0) {

        $mydb->setQuery("
            SELECT ss.id
            FROM tblsetschedule ss
            INNER JOIN tblscheduletime st
                ON st.id = ss.time_id
            WHERE ss.instructor_id = {$instructor_id}
            AND ss.day_id = {$day_id}
            AND ss.semester = '{$safeSemester}'
            AND ss.school_year = '{$safeSchoolYear}'
            AND ss.id != {$id}
            AND st.time_start < '{$selectedTime->time_end}'
            AND st.time_end > '{$selectedTime->time_start}'
            LIMIT 1
        ");

        $instructorConflict = $mydb->loadSingleResult();


        if ($instructorConflict) {

            message(
                "Schedule conflict: the selected instructor already has a class at this time.",
                "error"
            );

            redirect("index.php");

            return;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SECTION CONFLICT
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT ss.id
        FROM tblsetschedule ss
        INNER JOIN tblscheduletime st
            ON st.id = ss.time_id
        WHERE ss.section_id = {$section_id}
        AND ss.day_id = {$day_id}
        AND ss.semester = '{$safeSemester}'
        AND ss.school_year = '{$safeSchoolYear}'
        AND ss.id != {$id}
        AND st.time_start < '{$selectedTime->time_end}'
        AND st.time_end > '{$selectedTime->time_start}'
        LIMIT 1
    ");

    $sectionConflict = $mydb->loadSingleResult();


    if ($sectionConflict) {

        message(
            "Schedule conflict: the selected section already has a class at this time.",
            "error"
        );

        redirect("index.php");

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    $query = "
        UPDATE tblsetschedule
        SET
            department_id = {$department_id},
            section_id = {$section_id},
            subject_id = {$subject_id},
            classroom_id = {$classroom_id},
            day_id = {$day_id},
            time_id = {$time_id},
            instructor_id = " . ($instructor_id > 0 ? $instructor_id : "NULL") . ",
            semester = '{$safeSemester}',
            school_year = '{$safeSchoolYear}'
        WHERE id = {$id}
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Schedule has been updated successfully.",
            "success"
        );

    } else {

        message(
            "Failed to update Schedule.",
            "error"
        );
    }


    redirect("index.php");
}


/*
|--------------------------------------------------------------------------
| DELETE SET SCHEDULE
|--------------------------------------------------------------------------
*/

function doDelete()
{
    global $mydb;

    $id = isset($_GET['id'])
        ? intval($_GET['id'])
        : 0;


    if ($id <= 0) {

        message(
            "Invalid Schedule ID.",
            "error"
        );

        redirect("index.php");

        return;
    }


    $mydb->setQuery("
        SELECT id
        FROM tblsetschedule
        WHERE id = {$id}
        LIMIT 1
    ");

    $record = $mydb->loadSingleResult();


    if (!$record) {

        message(
            "Schedule record not found.",
            "error"
        );

        redirect("index.php");

        return;
    }


    $query = "
        DELETE FROM tblsetschedule
        WHERE id = {$id}
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Schedule has been deleted successfully.",
            "success"
        );

    } else {

        message(
            "Failed to delete Schedule.",
            "error"
        );
    }


    redirect("index.php");
}


/*
|--------------------------------------------------------------------------
| GET SECTIONS BY COURSE
|--------------------------------------------------------------------------
*/

function getSections()
{
    global $mydb;

    $course_id = isset($_GET['course_id'])
        ? intval($_GET['course_id'])
        : 0;


    header('Content-Type: application/json');


    if ($course_id <= 0) {

        echo json_encode(array());

        exit;
    }


    $mydb->setQuery("
        SELECT
            s.SECTION_ID,
            s.SECTION_NAME,
            s.YEAR_LEVEL,
            s.COURSE_ID,
            s.SY_ID,
            sy.SCHOOL_YEAR
        FROM tblsections s
        LEFT JOIN tblschoolyear sy
            ON sy.SY_ID = s.SY_ID
        WHERE s.COURSE_ID = {$course_id}
        ORDER BY
            s.YEAR_LEVEL ASC,
            s.SECTION_NAME ASC
    ");


    $rows = $mydb->loadResultList();

    $data = array();


    foreach ($rows as $row) {

        $data[] = array(
            'SECTION_ID' => intval($row->SECTION_ID),
            'SECTION_NAME' => $row->SECTION_NAME,
            'YEAR_LEVEL' => $row->YEAR_LEVEL,
            'SCHOOL_YEAR' => $row->SCHOOL_YEAR
        );
    }


    echo json_encode($data);

    exit;
}


/*
|--------------------------------------------------------------------------
| GET SUBJECTS BY COURSE + YEAR LEVEL + SEMESTER
|--------------------------------------------------------------------------
*/

function getSubjects()
{
    global $mydb;

    $course_id = isset($_GET['course_id'])
        ? intval($_GET['course_id'])
        : 0;

    $year_level = isset($_GET['year_level'])
        ? trim($_GET['year_level'])
        : '';

    $semester = isset($_GET['semester'])
        ? trim($_GET['semester'])
        : '';


    header('Content-Type: application/json');


    if (
        $course_id <= 0 ||
        $year_level == '' ||
        $semester == ''
    ) {

        echo json_encode(array());

        exit;
    }


    $year_level = $mydb->escape_value($year_level);
    $semester = $mydb->escape_value($semester);


    $mydb->setQuery("
        SELECT
            SUBJECT_ID,
            SUBJECT_CODE,
            SUBJECT_NAME,
            UNITS
        FROM tblsubjects
        WHERE COURSE_ID = {$course_id}
        AND YEAR_LEVEL = '{$year_level}'
        AND SEMESTER = '{$semester}'
        ORDER BY SUBJECT_CODE ASC
    ");


    $rows = $mydb->loadResultList();

    $data = array();


    foreach ($rows as $row) {

        $data[] = array(
            'SUBJECT_ID' => intval($row->SUBJECT_ID),
            'SUBJECT_CODE' => $row->SUBJECT_CODE,
            'SUBJECT_NAME' => $row->SUBJECT_NAME,
            'UNITS' => intval($row->UNITS)
        );
    }


    echo json_encode($data);

    exit;
}

?>