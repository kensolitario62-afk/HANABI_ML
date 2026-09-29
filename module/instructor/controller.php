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
        redirect(WEB_ROOT . "module/instructor/");
        break;
}


/* =========================================================
   ADD INSTRUCTOR
   ========================================================= */
function doInsert()
{
    global $mydb;

    $instructor_id = isset($_POST['INSTRUCTOR_ID'])
        ? trim($_POST['INSTRUCTOR_ID'])
        : '';

    $name = isset($_POST['NAME'])
        ? trim($_POST['NAME'])
        : '';

    $description = isset($_POST['DESCRIPTION'])
        ? trim($_POST['DESCRIPTION'])
        : '';

    if ($name == '') {

        message(
            "Instructor name is required.",
            "error"
        );

        redirect(WEB_ROOT . "module/instructor/");
        return;
    }

    $instructor_id = $mydb->escape_value($instructor_id);
    $name = $mydb->escape_value($name);
    $description = $mydb->escape_value($description);


    /* Check duplicate instructor ID */
    if ($instructor_id != '') {

        $mydb->setQuery("
            SELECT id
            FROM tblinstructor
            WHERE instructor_id = '{$instructor_id}'
            LIMIT 1
        ");

        $duplicate = $mydb->loadSingleResult();

        if ($duplicate) {

            message(
                "Instructor ID already exists.",
                "error"
            );

            redirect(WEB_ROOT . "module/instructor/");
            return;
        }
    }


    /* Check duplicate name */
    $mydb->setQuery("
        SELECT id
        FROM tblinstructor
        WHERE name = '{$name}'
        LIMIT 1
    ");

    $duplicateName = $mydb->loadSingleResult();

    if ($duplicateName) {

        message(
            "Instructor name already exists.",
            "error"
        );

        redirect(WEB_ROOT . "module/instructor/");
        return;
    }


    $query = "
        INSERT INTO tblinstructor
        (
            instructor_id,
            name,
            description
        )
        VALUES
        (
            '{$instructor_id}',
            '{$name}',
            '{$description}'
        )
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Instructor has been added successfully.",
            "success"
        );

    } else {

        message(
            "Failed to add instructor.",
            "error"
        );
    }


    /* ALWAYS RETURN TO INSTRUCTOR MODULE */
    redirect(WEB_ROOT . "module/instructor/");
}


/* =========================================================
   EDIT INSTRUCTOR
   ========================================================= */
function doEdit()
{
    global $mydb;

    $id = isset($_POST['ID'])
        ? intval($_POST['ID'])
        : 0;

    $instructor_id = isset($_POST['INSTRUCTOR_ID1'])
        ? trim($_POST['INSTRUCTOR_ID1'])
        : '';

    $name = isset($_POST['NAME1'])
        ? trim($_POST['NAME1'])
        : '';

    $description = isset($_POST['DESCRIPTION1'])
        ? trim($_POST['DESCRIPTION1'])
        : '';


    if ($id <= 0) {

        message(
            "Invalid instructor ID.",
            "error"
        );

        redirect(WEB_ROOT . "module/instructor/");
        return;
    }


    if ($name == '') {

        message(
            "Instructor name is required.",
            "error"
        );

        redirect(WEB_ROOT . "module/instructor/");
        return;
    }


    $instructor_id = $mydb->escape_value($instructor_id);
    $name = $mydb->escape_value($name);
    $description = $mydb->escape_value($description);


    /* Check duplicate Instructor ID */
    if ($instructor_id != '') {

        $mydb->setQuery("
            SELECT id
            FROM tblinstructor
            WHERE instructor_id = '{$instructor_id}'
            AND id != {$id}
            LIMIT 1
        ");

        $duplicate = $mydb->loadSingleResult();

        if ($duplicate) {

            message(
                "Another instructor already uses this Instructor ID.",
                "error"
            );

            redirect(WEB_ROOT . "module/instructor/");
            return;
        }
    }


    /* Check duplicate name */
    $mydb->setQuery("
        SELECT id
        FROM tblinstructor
        WHERE name = '{$name}'
        AND id != {$id}
        LIMIT 1
    ");

    $duplicateName = $mydb->loadSingleResult();

    if ($duplicateName) {

        message(
            "Another instructor already uses this name.",
            "error"
        );

        redirect(WEB_ROOT . "module/instructor/");
        return;
    }


    $query = "
        UPDATE tblinstructor
        SET
            instructor_id = '{$instructor_id}',
            name = '{$name}',
            description = '{$description}'
        WHERE id = {$id}
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Instructor has been updated successfully.",
            "success"
        );

    } else {

        message(
            "Failed to update instructor.",
            "error"
        );
    }


    /* ALWAYS RETURN TO INSTRUCTOR MODULE */
    redirect(WEB_ROOT . "module/instructor/");
}


/* =========================================================
   DELETE INSTRUCTOR
   ========================================================= */
function doDelete()
{
    global $mydb;

    $id = isset($_GET['id'])
        ? intval($_GET['id'])
        : 0;


    if ($id <= 0) {

        message(
            "Invalid instructor ID.",
            "error"
        );

        redirect(WEB_ROOT . "module/instructor/");
        return;
    }


    /* Check if instructor is already used */
    $mydb->setQuery("
        SELECT id
        FROM tblsetschedule
        WHERE instructor_id = {$id}
        LIMIT 1
    ");

    $used = $mydb->loadSingleResult();


    if ($used) {

        message(
            "This instructor cannot be deleted because it is already used in a schedule.",
            "error"
        );

        redirect(WEB_ROOT . "module/instructor/");
        return;
    }


    $query = "
        DELETE FROM tblinstructor
        WHERE id = {$id}
    ";


    if ($mydb->InsertThis($query)) {

        message(
            "Instructor has been deleted successfully.",
            "success"
        );

    } else {

        message(
            "Failed to delete instructor.",
            "error"
        );
    }


    /* ALWAYS RETURN TO INSTRUCTOR MODULE */
    redirect(WEB_ROOT . "module/instructor/");
}

?>