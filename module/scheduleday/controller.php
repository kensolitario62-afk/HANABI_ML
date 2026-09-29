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
        redirect(WEB_ROOT . "module/scheduleday/");
        break;
}


/*
|--------------------------------------------------------------------------
| ADD SCHEDULE DAY
|--------------------------------------------------------------------------
*/

function doInsert()
{
    global $mydb;

    $name = isset($_POST['NAME'])
        ? trim($_POST['NAME'])
        : '';

    $description = isset($_POST['DESCRIPTION'])
        ? trim($_POST['DESCRIPTION'])
        : '';


    if ($name == '') {

        message(
            "Please enter the Schedule Day.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduleday/"
        );

        return;
    }


    $name = $mydb->escape_value($name);
    $description = $mydb->escape_value($description);


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblscheduleday
        WHERE name = '{$name}'
        LIMIT 1
    ");

    $duplicate = $mydb->loadSingleResult();


    if ($duplicate) {

        message(
            "This Schedule Day already exists.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduleday/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    $query = "
        INSERT INTO tblscheduleday
        (
            name,
            description
        )
        VALUES
        (
            '{$name}',
            '{$description}'
        )
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Schedule Day has been added successfully.",
            "success"
        );

    } else {

        message(
            "Failed to add Schedule Day.",
            "error"
        );
    }


    redirect(
        WEB_ROOT . "module/scheduleday/"
    );
}


/*
|--------------------------------------------------------------------------
| EDIT SCHEDULE DAY
|--------------------------------------------------------------------------
|
| NOTE: the edit modal in list.php posts NAME1 / DESCRIPTION1
| (not NAME / DESCRIPTION or DAY_NAME). The previous version of
| this function read the wrong field names, so edits always
| failed validation or silently wiped the description.
|
*/

function doEdit()
{
    global $mydb;

    $id = isset($_POST['ID'])
        ? intval($_POST['ID'])
        : 0;

    $name = isset($_POST['NAME1'])
        ? trim($_POST['NAME1'])
        : '';

    $description = isset($_POST['DESCRIPTION1'])
        ? trim($_POST['DESCRIPTION1'])
        : '';


    if ($id <= 0) {

        message(
            "Invalid Schedule Day ID.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduleday/"
        );

        return;
    }


    if ($name == '') {

        message(
            "Please enter the Schedule Day.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduleday/"
        );

        return;
    }


    $name = $mydb->escape_value($name);
    $description = $mydb->escape_value($description);


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblscheduleday
        WHERE name = '{$name}'
        AND id != {$id}
        LIMIT 1
    ");

    $duplicate = $mydb->loadSingleResult();


    if ($duplicate) {

        message(
            "Another Schedule Day with the same name already exists.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduleday/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    $query = "
        UPDATE tblscheduleday
        SET
            name = '{$name}',
            description = '{$description}'
        WHERE id = {$id}
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Schedule Day has been updated successfully.",
            "success"
        );

    } else {

        message(
            "Failed to update Schedule Day.",
            "error"
        );
    }


    redirect(
        WEB_ROOT . "module/scheduleday/"
    );
}


/*
|--------------------------------------------------------------------------
| DELETE SCHEDULE DAY
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
            "Invalid Schedule Day ID.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduleday/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK IF DAY IS USED IN SET SCHEDULE
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblsetschedule
        WHERE day_id = {$id}
        LIMIT 1
    ");

    $used = $mydb->loadSingleResult();


    if ($used) {

        message(
            "This Schedule Day cannot be deleted because it is already used in a schedule.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/scheduleday/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    $query = "
        DELETE FROM tblscheduleday
        WHERE id = {$id}
    ";


    /*
    | IMPORTANT:
    | Use InsertThis(), NOT executeQuery()
    */

    if ($mydb->InsertThis($query)) {

        message(
            "Schedule Day has been deleted successfully.",
            "success"
        );

    } else {

        message(
            "Failed to delete Schedule Day.",
            "error"
        );
    }


    redirect(
        WEB_ROOT . "module/scheduleday/"
    );
}

?>