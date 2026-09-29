<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;


/*
|--------------------------------------------------------------------------
| ACTION
|--------------------------------------------------------------------------
*/

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
        redirect(WEB_ROOT . "module/classroom/");
        break;
}


/*
|--------------------------------------------------------------------------
| ADD CLASSROOM
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


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($name == '') {

        message(
            "Classroom name is required.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/classroom/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE VALUES
    |--------------------------------------------------------------------------
    */

    $nameSafe = $mydb->escape_value($name);

    $descriptionSafe = $mydb->escape_value($description);


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblclassroom
        WHERE name = '{$nameSafe}'
        LIMIT 1
    ");

    $duplicate = $mydb->loadSingleResult();


    if ($duplicate) {

        message(
            "Classroom already exists.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/classroom/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    $query = "
        INSERT INTO tblclassroom
        (
            name,
            description
        )
        VALUES
        (
            '{$nameSafe}',
            '{$descriptionSafe}'
        )
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Classroom has been added successfully.",
            "success"
        );

    } else {

        message(
            "Failed to add classroom.",
            "error"
        );
    }


    redirect(
        WEB_ROOT . "module/classroom/"
    );
}


/*
|--------------------------------------------------------------------------
| EDIT CLASSROOM
|--------------------------------------------------------------------------
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


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($id <= 0) {

        message(
            "Invalid classroom ID.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/classroom/"
        );

        return;
    }


    if ($name == '') {

        message(
            "Classroom name is required.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/classroom/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE VALUES
    |--------------------------------------------------------------------------
    */

    $nameSafe = $mydb->escape_value($name);

    $descriptionSafe = $mydb->escape_value($description);


    /*
    |--------------------------------------------------------------------------
    | CHECK DUPLICATE
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblclassroom
        WHERE name = '{$nameSafe}'
        AND id != {$id}
        LIMIT 1
    ");

    $duplicate = $mydb->loadSingleResult();


    if ($duplicate) {

        message(
            "Another classroom with this name already exists.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/classroom/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    $query = "
        UPDATE tblclassroom
        SET
            name = '{$nameSafe}',
            description = '{$descriptionSafe}'
        WHERE id = {$id}
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Classroom has been updated successfully.",
            "success"
        );

    } else {

        message(
            "Failed to update classroom.",
            "error"
        );
    }


    redirect(
        WEB_ROOT . "module/classroom/"
    );
}


/*
|--------------------------------------------------------------------------
| DELETE CLASSROOM
|--------------------------------------------------------------------------
*/

function doDelete()
{
    global $mydb;

    $id = isset($_GET['id'])
        ? intval($_GET['id'])
        : 0;


    /*
    |--------------------------------------------------------------------------
    | VALIDATE ID
    |--------------------------------------------------------------------------
    */

    if ($id <= 0) {

        message(
            "Invalid classroom ID.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/classroom/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK IF CLASSROOM IS USED IN SET SCHEDULE
    |--------------------------------------------------------------------------
    */

    $mydb->setQuery("
        SELECT id
        FROM tblsetschedule
        WHERE classroom_id = {$id}
        LIMIT 1
    ");

    $used = $mydb->loadSingleResult();


    if ($used) {

        message(
            "This classroom cannot be deleted because it is already used in a schedule.",
            "error"
        );

        redirect(
            WEB_ROOT . "module/classroom/"
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    $query = "
        DELETE FROM tblclassroom
        WHERE id = {$id}
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Classroom has been deleted successfully.",
            "success"
        );

    } else {

        message(
            "Failed to delete classroom.",
            "error"
        );
    }


    redirect(
        WEB_ROOT . "module/classroom/"
    );
}

?>