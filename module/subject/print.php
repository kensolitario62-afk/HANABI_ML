<?php

require_once("../../include/initialize.php");

global $mydb;


/* =========================================================
   GET SUBJECT ID
   ========================================================= */

$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($subject_id <= 0) {
    die("Invalid subject ID.");
}


/* =========================================================
   GET SUBJECT INFORMATION
   ========================================================= */

$mydb->setQuery("
    SELECT 
        s.*,
        c.COURSE_CODE,
        c.COURSE_NAME
    FROM tblsubjects s
    LEFT JOIN tblcourses c
        ON c.COURSE_ID = s.COURSE_ID
    WHERE s.SUBJECT_ID = '".$subject_id."'
    LIMIT 1
");

$subject = $mydb->loadSingleResult();


if (!$subject) {
    die("Subject not found.");
}


/* =========================================================
   CALCULATE AMOUNT
   ========================================================= */

$units = (int)$subject->UNITS;

$price_per_unit = isset($subject->PRICE_PER_UNIT)
    ? (float)$subject->PRICE_PER_UNIT
    : 900;

$total_amount = $units * $price_per_unit;


/* =========================================================
   DATE
   ========================================================= */

$date_printed = date("d/m/Y");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Subject Print - <?php echo htmlspecialchars($subject->SUBJECT_CODE); ?>
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            padding: 30px;
            background: #ffffff;
            color: #000000;
            font-family: "Times New Roman", Times, serif;
            font-size: 13px;
        }


        .print-container {
            width: 100%;
            max-width: 850px;
            margin: 0 auto;
        }


        /* =====================================================
           PRINT BUTTON
           ===================================================== */

        .print-area {
            text-align: center;
            margin-bottom: 25px;
        }


        .print-button {
            background: #a90000;
            color: #ffffff;
            border: none;
            padding: 9px 22px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
        }


        .back-button {
            display: inline-block;
            margin-left: 5px;
            padding: 9px 22px;
            background: #6c757d;
            color: #ffffff;
            text-decoration: none;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            border-radius: 4px;
        }


        /* =====================================================
           SCHOOL HEADER
           ===================================================== */

        .school-header {
            position: relative;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #000000;
        }


        .school-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }


        .school-header p {
            margin: 2px 0;
            font-size: 12px;
        }


        .school-logo {
            position: absolute;
            left: 5px;
            top: 0;
            width: 65px;
            height: 65px;
            object-fit: contain;
        }


        /* =====================================================
           REPORT TITLE
           ===================================================== */

        .report-title {
            text-align: center;
            margin-top: 8px;
            margin-bottom: 20px;
        }


        .report-title h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #003399;
            text-transform: uppercase;
        }


        .report-title p {
            margin: 2px 0;
            font-size: 12px;
        }


        /* =====================================================
           SUBJECT INFORMATION
           ===================================================== */

        .subject-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }


        .subject-info td {
            border: 1px solid #000000;
            padding: 8px;
        }


        .subject-info .label {
            width: 20%;
            font-weight: bold;
        }


        .subject-info .value {
            width: 30%;
        }


        /* =====================================================
           SUBJECT TABLE
           ===================================================== */

        .subject-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }


        .subject-table th,
        .subject-table td {
            border: 1px solid #000000;
            padding: 8px;
        }


        .subject-table th {
            color: #003399;
            text-align: center;
            font-weight: bold;
        }


        .subject-table td {
            vertical-align: middle;
        }


        .center {
            text-align: center;
        }


        .right {
            text-align: right;
        }


        /* =====================================================
           TOTAL
           ===================================================== */

        .total-row {
            font-weight: bold;
        }


        .total-row td {
            border-top: 2px solid #000000;
        }


        /* =====================================================
           SIGNATURE
           ===================================================== */

        .signature-area {
            width: 100%;
            margin-top: 55px;
        }


        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }


        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px 30px;
        }


        .signature-line {
            border-bottom: 1px solid #000000;
            height: 35px;
            margin-bottom: 5px;
        }


        .signature-name {
            font-weight: bold;
            font-size: 12px;
        }


        .signature-label {
            color: #003399;
            font-size: 11px;
        }


        /* =====================================================
           FOOTER
           ===================================================== */

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #555555;
        }


        /* =====================================================
           PRINT
           ===================================================== */

        @media print {

            @page {
                size: A4 portrait;
                margin: 15mm;
            }


            body {
                padding: 0;
                font-size: 12px;
            }


            .print-area {
                display: none;
            }


            .print-container {
                max-width: 100%;
            }


            .school-header h1 {
                font-size: 18px;
            }


            .school-header p {
                font-size: 11px;
            }


            .report-title h2 {
                font-size: 14px;
            }


            .subject-table {
                page-break-inside: avoid;
            }


            .signature-area {
                page-break-inside: avoid;
            }

        }

    </style>

    <style>
        /* ---------- refreshed look (print-safe colours) ---------- */
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .print-area { font-family: Arial, Helvetica, sans-serif; }
        .print-button {
            background: linear-gradient(120deg, #8b0000, #c0392b) !important;
            border-radius: 30px !important; padding: 10px 26px !important;
            font-weight: bold; box-shadow: 0 3px 10px rgba(0,0,0,.25);
        }
        .back-button { border-radius: 30px !important; padding: 10px 26px !important; font-weight: bold; }

        .letterhead { display: flex; align-items: center; }
        .letterhead .logo { width: 80px; height: 80px; object-fit: contain; flex: 0 0 80px; }
        .letterhead .text { flex: 1; text-align: center; padding-right: 80px; line-height: 1.35; }
        .school-name { font-size: 19px; font-weight: bold; text-transform: uppercase; letter-spacing: .3px; }
        .school-line { font-size: 12px; }
        .rule { height: 4px; background: linear-gradient(90deg, #8b0000, #c0392b, #8b0000); border-radius: 4px; margin: 10px 0 4px; }

        .report-title h2 { color: #8b0000 !important; font-size: 20px !important; letter-spacing: 2px; }

        .subject-info { border-radius: 8px; overflow: hidden; }
        .subject-info td { border-color: #b9b9b9 !important; }
        .subject-info .label { background: #f6e3e1 !important; color: #5c0000; }

        .subject-table th { background: #8b0000 !important; color: #fff !important; border-color: #8b0000 !important; text-transform: uppercase; font-size: 11px; letter-spacing: .4px; }
        .subject-table td { border-color: #cfcfcf !important; }
        .total-row td { background: #fbeeed !important; color: #5c0000; border-top: 2px solid #8b0000 !important; }

        .signature-label { color: #8b0000 !important; }
        .footer { border-top: 1px dashed #bbb; padding-top: 8px; }
    </style>

</head>


<body>


<div class="print-container">


    <!-- =====================================================
         PRINT BUTTON
         ===================================================== -->

    <div class="print-area">

        <button
            type="button"
            class="print-button"
            onclick="window.print();">

            &#128424; Print This Form

        </button>


        <a
            href="<?php echo WEB_ROOT; ?>module/subject/index.php?view=list"
            class="back-button">

            Back

        </a>

    </div>


    <!-- =====================================================
         SCHOOL HEADER
         ===================================================== -->

    <div class="letterhead">
        <img class="logo" src="<?php echo WEB_ROOT; ?>csr.png" alt="" onerror="this.style.visibility='hidden';">
        <div class="text">
            <div class="school-name">Colegio de Santa Rita de San Carlos, Inc.</div>
            <div class="school-line">Atienza Avenue, San Carlos City, Negros Occidental</div>
            <div class="school-line">Subject Information Form</div>
        </div>
    </div>
    <div class="rule"></div>

    <!-- =====================================================
         REPORT TITLE
         ===================================================== -->

    <div class="report-title">

        <h2>
            SUBJECT INFORMATION
        </h2>

        <p>
            Date Printed: <?php echo $date_printed; ?>
        </p>

    </div>


    <!-- =====================================================
         SUBJECT INFORMATION
         ===================================================== -->

    <table class="subject-info">

        <tr>

            <td class="label">
                Subject Code
            </td>

            <td class="value">
                <?php
                echo htmlspecialchars(
                    $subject->SUBJECT_CODE
                );
                ?>
            </td>


            <td class="label">
                Units
            </td>

            <td class="value">
                <?php
                echo $units;
                ?>
            </td>

        </tr>


        <tr>

            <td class="label">
                Subject Name
            </td>

            <td colspan="3">
                <?php
                echo htmlspecialchars(
                    $subject->SUBJECT_NAME
                );
                ?>
            </td>

        </tr>


        <tr>

            <td class="label">
                Course
            </td>

            <td colspan="3">

                <?php

                echo htmlspecialchars(
                    $subject->COURSE_CODE
                );

                echo " - ";

                echo htmlspecialchars(
                    $subject->COURSE_NAME
                );

                ?>

            </td>

        </tr>


        <tr>

            <td class="label">
                Year Level
            </td>

            <td>
                <?php
                echo htmlspecialchars(
                    $subject->YEAR_LEVEL
                );
                ?>
            </td>


            <td class="label">
                Semester
            </td>

            <td>
                <?php
                echo htmlspecialchars(
                    $subject->SEMESTER
                );
                ?>
            </td>

        </tr>

    </table>


    <!-- =====================================================
         SUBJECT DETAILS TABLE
         ===================================================== -->

    <table class="subject-table">

        <thead>

            <tr>

                <th>
                    Subject Code
                </th>

                <th>
                    Subject Name
                </th>

                <th>
                    Units
                </th>

                <th>
                    Price / Unit
                </th>

                <th>
                    Amount
                </th>

            </tr>

        </thead>


        <tbody>

            <tr>

                <td class="center">

                    <?php
                    echo htmlspecialchars(
                        $subject->SUBJECT_CODE
                    );
                    ?>

                </td>


                <td>

                    <?php
                    echo htmlspecialchars(
                        $subject->SUBJECT_NAME
                    );
                    ?>

                </td>


                <td class="center">

                    <?php
                    echo $units;
                    ?>

                </td>


                <td class="right">

                    ₱<?php
                    echo number_format(
                        $price_per_unit,
                        2
                    );
                    ?>

                </td>


                <td class="right">

                    ₱<?php
                    echo number_format(
                        $total_amount,
                        2
                    );
                    ?>

                </td>

            </tr>


            <tr class="total-row">

                <td colspan="2" class="right">
                    Total
                </td>

                <td class="center">
                    <?php
                    echo $units;
                    ?>
                </td>

                <td></td>

                <td class="right">

                    ₱<?php
                    echo number_format(
                        $total_amount,
                        2
                    );
                    ?>

                </td>

            </tr>

        </tbody>

    </table>


    <!-- =====================================================
         SIGNATURE AREA
         ===================================================== -->

    <div class="signature-area">

        <table class="signature-table">

            <tr>

                <td>

                    <div class="signature-line"></div>

                    <div class="signature-name">
                        ______________________________
                    </div>

                    <div class="signature-label">
                        PREPARED BY
                    </div>

                </td>


                <td>

                    <div class="signature-line"></div>

                    <div class="signature-name">
                        ______________________________
                    </div>

                    <div class="signature-label">
                        APPROVED BY
                    </div>

                </td>

            </tr>

        </table>

    </div>


    <!-- =====================================================
         FOOTER
         ===================================================== -->

    <div class="footer">

        Generated from the School Information System

    </div>


</div>


<!-- =========================================================
     AUTO PRINT
     ========================================================= -->

<script>

window.onload = function() {

    setTimeout(function() {

        window.print();

    }, 500);

};

</script>


</body>

</html>