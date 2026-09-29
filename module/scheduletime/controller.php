<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {

    case 'add':
        doInsert();
        break;

    case 'edit':
        doEdit();
        break;

    case 'delete':
        doDelete();
        break;

    default:
        redirect(WEB_ROOT . "module/scheduletime/");
        break;
}


/*
|--------------------------------------------------------------------------
| ADD SCHEDULE TIME
|--------------------------------------------------------------------------
*/

function doInsert()
{
    global $mydb;

    $time_start = isset($_POST['TIME_START'])
        ? trim($_POST['TIME_START'])
        : '';

    $time_end = isset($_POST['TIME_END'])
        ? trim($_POST['TIME_END'])
        : '';

    $description = isset($_POST['DESCRIPTION'])
        ? trim($_POST['DESCRIPTION'])
        : '';


    if ($time_start == '' || $time_end == '') {

        message(
            "Start time and end time are required.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduletime/"
        );

        return;
    }


    if ($time_start >= $time_end) {

        message(
            "End time must be later than start time.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduletime/"
        );

        return;
    }


    $time_start = $mydb->escape_value($time_start);
    $time_end = $mydb->escape_value($time_end);
    $description = $mydb->escape_value($description);


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE TIME
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblscheduletime
        WHERE time_start = '{$time_start}'
        AND time_end = '{$time_end}'
        LIMIT 1
    ");

    $duplicate = $mydb->loadSingleResult();


    if ($duplicate) {

        message(
            "This schedule time already exists.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduletime/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    $query = "
        INSERT INTO tblscheduletime
        (
            time_start,
            time_end,
            description
        )
        VALUES
        (
            '{$time_start}',
            '{$time_end}',
            '{$description}'
        )
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Schedule time has been added successfully.",
            "success"
        );

    } else {

        message(
            "Failed to add schedule time.",
            "error"
        );
    }


    redirect(
        WEB_ROOT . "module/scheduletime/"
    );
}


/*
|--------------------------------------------------------------------------
| EDIT SCHEDULE TIME
|--------------------------------------------------------------------------
*/

function doEdit()
{
    global $mydb;

    $id = isset($_POST['ID'])
        ? intval($_POST['ID'])
        : 0;

    $time_start = isset($_POST['TIME_START1'])
        ? trim($_POST['TIME_START1'])
        : '';

    $time_end = isset($_POST['TIME_END1'])
        ? trim($_POST['TIME_END1'])
        : '';

    $description = isset($_POST['DESCRIPTION1'])
        ? trim($_POST['DESCRIPTION1'])
        : '';


    if ($id <= 0) {

        message(
            "Invalid schedule time ID.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduletime/"
        );

        return;
    }


    if ($time_start == '' || $time_end == '') {

        message(
            "Start time and end time are required.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduletime/"
        );

        return;
    }


    if ($time_start >= $time_end) {

        message(
            "End time must be later than start time.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduletime/"
        );

        return;
    }


    $time_start = $mydb->escape_value($time_start);
    $time_end = $mydb->escape_value($time_end);
    $description = $mydb->escape_value($description);


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblscheduletime
        WHERE time_start = '{$time_start}'
        AND time_end = '{$time_end}'
        AND id != {$id}
        LIMIT 1
    ");

    $duplicate = $mydb->loadSingleResult();


    if ($duplicate) {

        message(
            "Another schedule time with the same time already exists.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduletime/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    $query = "
        UPDATE tblscheduletime
        SET
            time_start = '{$time_start}',
            time_end = '{$time_end}',
            description = '{$description}'
        WHERE id = {$id}
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Schedule time has been updated successfully.",
            "success"
        );

    } else {

        message(
            "Failed to update schedule time.",
            "error"
        );
    }


    redirect(
        WEB_ROOT . "module/scheduletime/"
    );
}


/*
|--------------------------------------------------------------------------
| DELETE SCHEDULE TIME
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
            "Invalid schedule time ID.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduletime/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK IF TIME IS USED IN SET SCHEDULE
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblsetschedule
        WHERE time_id = {$id}
        LIMIT 1
    ");

    $used = $mydb->loadSingleResult();


    if ($used) {

        message(
            "This schedule time cannot be deleted because it is already used in a schedule.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduletime/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    $query = "
        DELETE FROM tblscheduletime
        WHERE id = {$id}
    ";


    /*
    | IMPORTANT:
    | Use InsertThis(), NOT executeQuery()
    */

    if ($mydb->InsertThis($query)) {

        message(
            "Schedule time has been deleted successfully.",
            "success"
        );

    } else {

        message(
            "Failed to delete schedule time.",
            "error"
        );
    }


    redirect(
        WEB_ROOT . "module/scheduletime/"
    );
}

?>