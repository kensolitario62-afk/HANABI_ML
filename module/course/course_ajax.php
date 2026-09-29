<?php

require_once("../../include/initialize.php");

global $mydb;


/* =========================================================
   GET SINGLE COURSE FOR EDIT
   ========================================================= */

if (isset($_POST['COURSE_ID'])) {

    $output = array();

    $COURSE_ID = (int)$_POST['COURSE_ID'];

    $query = "SELECT *
              FROM tblcourses
              WHERE COURSE_ID = '".$COURSE_ID."'
              LIMIT 1";

    $mydb->setQuery($query);
    $result = $mydb->loadResultList();

    foreach ($result as $row) {

        $output["COURSE_ID"]   = $row->COURSE_ID;
        $output["COURSE_CODE"] = $row->COURSE_CODE;
        $output["COURSE_NAME"] = $row->COURSE_NAME;
        $output["COURSE_DESC"] = $row->COURSE_DESC;
        $output["STATUS"]      = $row->STATUS;
    }

    echo json_encode($output);

}


/* =========================================================
   DATATABLE COURSE LIST
   ========================================================= */

else {

    $output = array();

    $query = "SELECT *
              FROM tblcourses";


    /* =====================================================
       SEARCH
       ===================================================== */

    if (isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '') {

        $search = $mydb->escape_value($_POST["search"]["value"]);

        $query .= " WHERE COURSE_NAME LIKE '%".$search."%'
                    OR COURSE_CODE LIKE '%".$search."%'
                    OR COURSE_DESC LIKE '%".$search."%'
                    OR STATUS LIKE '%".$search."%'";
    }


    /* =====================================================
       ORDERING
       ===================================================== */

    if (isset($_POST["order"])) {

        $column = (int)$_POST['order']['0']['column'];
        $dir    = $_POST['order']['0']['dir'];

        /*
         * DataTable columns:
         *
         * 0 = #
         * 1 = Course Code
         * 2 = Course Name
         * 3 = Description
         * 4 = Status
         * 5 = Action
         *
         * The Action column should not be used for SQL ordering.
         */

        $allowed_columns = array(
            0 => 'COURSE_ID',
            1 => 'COURSE_CODE',
            2 => 'COURSE_NAME',
            3 => 'COURSE_DESC',
            4 => 'STATUS'
        );

        if (isset($allowed_columns[$column])) {

            $order_column = $allowed_columns[$column];

            if ($dir != 'asc' && $dir != 'desc') {
                $dir = 'asc';
            }

            $query .= " ORDER BY ".$order_column." ".$dir;

        } else {

            $query .= " ORDER BY COURSE_NAME ASC";
        }

    } else {

        $query .= " ORDER BY COURSE_NAME ASC";
    }


    /* =====================================================
       PAGINATION
       ===================================================== */

    if (isset($_POST["length"]) && $_POST["length"] != -1) {

        $start  = isset($_POST["start"]) ? (int)$_POST["start"] : 0;
        $length = (int)$_POST["length"];

        $query .= " LIMIT ".$start.", ".$length;
    }


    /* =====================================================
       GET COURSES
       ===================================================== */

    $mydb->setQuery($query);

    $cur = $mydb->loadResultList();

    $data = array();

    $filtered_rows = $mydb->num_rows();

    $i = 1;


    /* =====================================================
       CREATE DATATABLE ROWS
       ===================================================== */

    foreach ($cur as $result) {

        $sub_array = array();


        /* Number */

        $sub_array[] = $i;


        /* Course Code */

        $sub_array[] = htmlspecialchars($result->COURSE_CODE);


        /* Course Name */

        $sub_array[] = htmlspecialchars($result->COURSE_NAME);


        /* Description */

        $sub_array[] = htmlspecialchars($result->COURSE_DESC);


        /* Status */

        if ($result->STATUS == 'Active') {

            $sub_array[] = '<span class="badge badge-success">
                                Active
                            </span>';

        } else {

            $sub_array[] = '<span class="badge badge-secondary">
                                Inactive
                            </span>';
        }


        /* =================================================
           ACTION BUTTONS
           ================================================= */

        $sub_array[] = '
            <div class="btn-group">
                <button type="button" name="update" COURSE_ID="'.$result->COURSE_ID.'" class="btn btn-warning btn-xs editEntry" title="Edit"><span class="fa fa-edit"></span></button>
                <a href="index.php?view=view&id='.$result->COURSE_ID.'" class="btn btn-info btn-xs" title="View"><span class="fa fa-eye"></span></a>
                <a href="print.php?id='.$result->COURSE_ID.'" target="_blank" class="btn btn-success btn-xs" title="Print"><span class="fa fa-print"></span></a>
                <button type="button" name="delete" COURSE_ID="'.$result->COURSE_ID.'" class="btn btn-danger btn-xs deleteEntry" title="Delete"><span class="fa fa-trash"></span></button>
            </div>
        ';


        $data[] = $sub_array;

        $i++;
    }


    /* =====================================================
       GET TOTAL RECORDS
       ===================================================== */

    function get_total_all_records()
    {

        global $mydb;

        $statement = "SELECT *
                      FROM tblcourses";

        $mydb->setQuery($statement);

        return $mydb->num_rows();
    }


    /* =====================================================
       DATATABLE RESPONSE
       ===================================================== */

    $output = array(

        "data"            => $data,

        "recordsTotal"    => $filtered_rows,

        "recordsFiltered" => get_total_all_records()

    );


    echo json_encode($output);

}

?>