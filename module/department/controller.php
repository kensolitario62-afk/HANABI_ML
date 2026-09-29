<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;


/*
|--------------------------------------------------------------------------
| ACTION
|--------------------------------------------------------------------------
*/

$action = isset($_GET['action'])
    ? $_GET['action']
    : '';


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

        redirect('index.php');

        break;
}



/*
|--------------------------------------------------------------------------
| ADD DEPARTMENT
|--------------------------------------------------------------------------
*/

function doInsert()
{

    global $mydb;


    $NAME = isset($_POST['NAME'])
        ? trim($_POST['NAME'])
        : '';


    $DESCRIPTION = isset($_POST['DESCRIPTION'])
        ? trim($_POST['DESCRIPTION'])
        : '';



    /*
    | Required field
    */

    if ($NAME == '') {

        message(
            "Please provide a department name.",
            "error"
        );

        redirect('index.php');

        return;
    }



    /*
    | Escape values
    */

    $NAME = $mydb->escape_value($NAME);

    $DESCRIPTION = $mydb->escape_value($DESCRIPTION);



    /*
    | Check duplicate
    */

    $mydb->setQuery("
        SELECT id
        FROM tbldepartment
        WHERE name = '{$NAME}'
        LIMIT 1
    ");


    $existing = $mydb->loadSingleResult();


    if ($existing) {

        message(
            "Department already exists!",
            "error"
        );

        redirect('index.php');

        return;
    }



    /*
    | Insert
    */

    $query = "
        INSERT INTO tbldepartment
        (
            name,
            description
        )
        VALUES
        (
            '{$NAME}',
            '{$DESCRIPTION}'
        )
    ";


    $result = $mydb->InsertThis($query);


    if ($result === true) {

        message(
            "New Department has been created successfully!",
            "success"
        );

    } else {

        message(
            "Department was not created.",
            "error"
        );

    }


    redirect('index.php');

}



/*
|--------------------------------------------------------------------------
| EDIT DEPARTMENT
|--------------------------------------------------------------------------
*/

function doEdit()
{

    global $mydb;


    /*
    | Get ID
    */

    $ID = isset($_POST['ID'])
        ? (int)$_POST['ID']
        : 0;


    /*
    | Get values
    */

    $NAME = isset($_POST['NAME1'])
        ? trim($_POST['NAME1'])
        : '';


    $DESCRIPTION = isset($_POST['DESCRIPTION1'])
        ? trim($_POST['DESCRIPTION1'])
        : '';



    /*
    | Validate ID
    */

    if ($ID <= 0) {

        message(
            "Invalid department ID.",
            "error"
        );

        redirect('index.php');

        return;
    }



    /*
    | Validate name
    */

    if ($NAME == '') {

        message(
            "Please provide a department name.",
            "error"
        );

        redirect('index.php');

        return;
    }



    /*
    | Check if department exists
    */

    $mydb->setQuery("
        SELECT id
        FROM tbldepartment
        WHERE id = {$ID}
        LIMIT 1
    ");


    $department = $mydb->loadSingleResult();


    if (!$department) {

        message(
            "Department no longer exists.",
            "error"
        );

        redirect('index.php');

        return;
    }



    /*
    | Escape values
    */

    $NAME = $mydb->escape_value($NAME);

    $DESCRIPTION = $mydb->escape_value($DESCRIPTION);



    /*
    | Check duplicate name
    */

    $mydb->setQuery("
        SELECT id
        FROM tbldepartment
        WHERE
            name = '{$NAME}'
            AND id != {$ID}
        LIMIT 1
    ");


    $existing = $mydb->loadSingleResult();


    if ($existing) {

        message(
            "Another department with this name already exists.",
            "error"
        );

        redirect('index.php');

        return;
    }



    /*
    | Update department
    */

    $query = "
        UPDATE tbldepartment

        SET
            name = '{$NAME}',
            description = '{$DESCRIPTION}'

        WHERE
            id = {$ID}
    ";


    $result = $mydb->InsertThis($query);


    if ($result === true) {

        message(
            "Department has been updated successfully!",
            "success"
        );

    } else {

        message(
            "Department was not updated.",
            "error"
        );

    }


    redirect('index.php');

}



/*
|--------------------------------------------------------------------------
| DELETE DEPARTMENT
|--------------------------------------------------------------------------
*/

function doDelete()
{

    global $mydb;


    $ID = isset($_GET['id'])
        ? (int)$_GET['id']
        : 0;



    /*
    | Validate ID
    */

    if ($ID <= 0) {

        message(
            "Invalid department ID.",
            "error"
        );

        redirect('index.php');

        return;
    }



    /*
    | Check if department exists
    */

    $mydb->setQuery("
        SELECT id
        FROM tbldepartment
        WHERE id = {$ID}
        LIMIT 1
    ");


    $department = $mydb->loadSingleResult();


    if (!$department) {

        message(
            "Department not found.",
            "error"
        );

        redirect('index.php');

        return;
    }



    /*
    | Check if used in Set Schedule
    */

    $mydb->setQuery("
        SELECT id
        FROM tblsetschedule
        WHERE department_id = {$ID}
        LIMIT 1
    ");


    $used = $mydb->loadSingleResult();


    if ($used) {

        message(
            "This department cannot be deleted because it is already used in a schedule.",
            "error"
        );

        redirect('index.php');

        return;
    }



    /*
    | Delete
    */

    $query = "
        DELETE FROM tbldepartment
        WHERE id = {$ID}
    ";


    $result = $mydb->InsertThis($query);


    if ($result === true) {

        message(
            "Department has been deleted successfully!",
            "success"
        );

    } else {

        message(
            "Department could not be deleted.",
            "error"
        );

    }


    redirect('index.php');

}

?>