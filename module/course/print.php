<?php

require_once("../../include/initialize.php");

global $mydb;

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id <= 0) {
    die("Invalid course ID.");
}

/* ==============================
   GET COURSE INFORMATION
   ============================== */

$mydb->setQuery("
    SELECT *
    FROM tblcourses
    WHERE COURSE_ID = '".$course_id."'
    LIMIT 1
");

$course = $mydb->loadSingleResult();

if (!$course) {
    die("Course not found.");
}


/* ==============================
   GET SUBJECTS
   ============================== */

$mydb->setQuery("
    SELECT *
    FROM tblsubjects
    WHERE COURSE_ID = '".$course_id."'
    ORDER BY
        CASE
            WHEN YEAR_LEVEL = '1st Year' THEN 1
            WHEN YEAR_LEVEL = '2nd Year' THEN 2
            WHEN YEAR_LEVEL = '3rd Year' THEN 3
            WHEN YEAR_LEVEL = '4th Year' THEN 4
            ELSE 5
        END,
        CASE
            WHEN SEMESTER = '1st Semester' THEN 1
            WHEN SEMESTER = '2nd Semester' THEN 2
            WHEN SEMESTER = 'Summer' THEN 3
            ELSE 4
        END,
        SUBJECT_CODE ASC
");

$subjects = $mydb->loadResultList();


/* ==============================
   GROUP SUBJECTS
   ============================== */

$grouped = array();

foreach ($subjects as $subject) {

    $year = !empty($subject->YEAR_LEVEL)
        ? $subject->YEAR_LEVEL
        : 'Unassigned';

    $semester = !empty($subject->SEMESTER)
        ? $subject->SEMESTER
        : 'Unassigned';

    $key = $year . ' - ' . $semester;

    if (!isset($grouped[$key])) {
        $grouped[$key] = array();
    }

    $grouped[$key][] = $subject;
}


/* ==============================
   TOTALS
   ============================== */

$total_units = 0;
$total_amount = 0;

foreach ($subjects as $subject) {

    $units = (int)$subject->UNITS;

    /*
     * tblsubjects contains PRICE_PER_UNIT.
     * Use 900 as fallback if the value is empty.
     */
    $price_per_unit = isset($subject->PRICE_PER_UNIT)
        ? (float)$subject->PRICE_PER_UNIT
        : 900;

    $amount = $units * $price_per_unit;

    $total_units += $units;
    $total_amount += $amount;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <title>
        Curriculum - <?php echo htmlspecialchars($course->COURSE_CODE); ?>
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            background: #fff;
            font-size: 13px;
        }

        .print-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* ==========================
           HEADER
           ========================== */

        .school-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .school-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .school-header h2 {
            margin: 5px 0 0;
            font-size: 16px;
            font-weight: bold;
        }

        .school-header p {
            margin: 3px 0;
            font-size: 12px;
        }

        .report-title {
            text-align: center;
            margin: 20px 0;
        }

        .report-title h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        /* ==========================
           COURSE INFORMATION
           ========================== */

        .course-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .course-info td {
            border: 1px solid #000;
            padding: 7px;
        }

        .course-info .label {
            width: 18%;
            font-weight: bold;
            background: #f2f2f2;
        }

        /* ==========================
           CURRICULUM TABLE
           ========================== */

        .semester-title {
            margin-top: 18px;
            margin-bottom: 0;
            padding: 8px;
            border: 1px solid #000;
            border-bottom: none;
            font-size: 14px;
            font-weight: bold;
            background: #f2f2f2;
        }

        table.curriculum {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.curriculum th,
        table.curriculum td {
            border: 1px solid #000;
            padding: 7px;
        }

        table.curriculum th {
            text-align: center;
            font-weight: bold;
            background: #eaeaea;
        }

        table.curriculum td {
            vertical-align: middle;
        }

        .code {
            width: 15%;
            text-align: center;
        }

        .subject-name {
            width: 40%;
        }

        .units {
            width: 10%;
            text-align: center;
        }

        .price {
            width: 15%;
            text-align: right;
        }

        .amount {
            width: 15%;
            text-align: right;
        }

        .semester-total {
            font-weight: bold;
            background: #f7f7f7;
        }

        /* ==========================
           GRAND TOTAL
           ========================== */

        .grand-total {
            margin-top: 20px;
        }

        .grand-total table {
            width: 100%;
            border-collapse: collapse;
        }

        .grand-total td {
            border: 1px solid #000;
            padding: 8px;
        }

        .grand-total .label {
            font-weight: bold;
            text-align: right;
            width: 70%;
        }

        .grand-total .value {
            font-weight: bold;
            text-align: right;
        }

        /* ==========================
           FOOTER
           ========================== */

        .report-footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature {
            width: 40%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 45px;
            margin-bottom: 5px;
        }

        .signature-label {
            font-size: 12px;
        }

        .generated {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #555;
        }

        /* ==========================
           PRINT BUTTON
           ========================== */

        .print-button-container {
            text-align: center;
            margin-bottom: 25px;
        }

        .print-button {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            margin-left: 5px;
            background: #6c757d;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }

        /* ==========================
           PRINT SETTINGS
           ========================== */

        @media print {

            @page {
                size: A4 portrait;
                margin: 12mm;
            }

            body {
                padding: 0;
                font-size: 11px;
            }

            .print-button-container {
                display: none;
            }

            .print-container {
                max-width: 100%;
            }

            .school-header h1 {
                font-size: 17px;
            }

            .school-header h2 {
                font-size: 14px;
            }

            .report-title h2 {
                font-size: 15px;
            }

            .semester-title {
                background: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table.curriculum th {
                background: #eaeaea !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .course-info .label {
                background: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .semester-total {
                background: #f7f7f7 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table.curriculum {
                page-break-inside: auto;
            }

            table.curriculum tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .semester-title {
                page-break-after: avoid;
            }

            .grand-total {
                page-break-inside: avoid;
            }

            .report-footer {
                page-break-inside: avoid;
            }

        }

    </style>

    <style>
        /* ---------- refreshed look (colours kept print-safe) ---------- */
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body { font-family: Cambria, "Times New Roman", Times, serif; }

        .print-button-container { font-family: Arial, Helvetica, sans-serif; }
        .print-button {
            background: linear-gradient(120deg, #8b0000, #c0392b) !important;
            border-radius: 30px !important;
            padding: 10px 26px !important;
            font-weight: bold;
            box-shadow: 0 3px 10px rgba(0,0,0,.25);
        }
        .back-button { border-radius: 30px !important; padding: 10px 26px !important; font-weight: bold; }

        /* letterhead */
        .letterhead { display: flex; align-items: center; margin-bottom: 6px; }
        .letterhead .logo { width: 80px; height: 80px; object-fit: contain; flex: 0 0 80px; }
        .letterhead .text { flex: 1; text-align: center; padding-right: 80px; line-height: 1.35; }
        .school-name { font-size: 18px; font-weight: bold; letter-spacing: .3px; text-transform: uppercase; }
        .school-line { font-size: 12px; }

        .rule { height: 4px; background: linear-gradient(90deg, #8b0000, #c0392b, #8b0000); border-radius: 4px; margin: 10px 0 4px; }
        .report-title { margin: 14px 0 16px !important; }
        .report-title h2 { font-size: 22px !important; letter-spacing: 2px; color: #8b0000; }
        .report-title p { margin: 2px 0 0; font-size: 12px; color: #555; font-style: italic; }

        /* course information */
        .course-info { border-radius: 8px; overflow: hidden; }
        .course-info td { border-color: #b9b9b9 !important; }
        .course-info .label { background: #f6e3e1 !important; color: #5c0000; }

        /* semester bars + tables */
        .semester-title {
            background: #8b0000 !important; color: #fff; border-color: #8b0000 !important;
            border-radius: 8px 8px 0 0; letter-spacing: .5px; text-transform: uppercase;
        }
        table.curriculum th { background: #f6e3e1 !important; color: #5c0000; border-color: #b9b9b9 !important; text-transform: uppercase; font-size: 11px; letter-spacing: .4px; }
        table.curriculum td { border-color: #cfcfcf !important; }
        table.curriculum tbody tr:nth-child(even) td { background: #fafafa; }
        .semester-total td, .semester-total { background: #fbeeed !important; color: #5c0000; }

        /* grand total */
        .grand-total table { border-radius: 8px; overflow: hidden; }
        .grand-total td { border-color: #b9b9b9 !important; }
        .grand-total tr:last-child td { background: #8b0000 !important; color: #fff; font-size: 14px; }

        .signature-line { border-bottom: 1px solid #333; }
        .generated { border-top: 1px dashed #bbb; padding-top: 8px; }
    </style>

</head>

<body>

<div class="print-container">

    <!-- PRINT BUTTONS -->

    <div class="print-button-container">

        <button
            type="button"
            class="print-button"
            onclick="window.print();">
            &#128424; Print Curriculum
        </button>

        <a
            href="<?php echo WEB_ROOT; ?>module/course/index.php?view=view&id=<?php echo $course_id; ?>"
            class="back-button">
            Back
        </a>

    </div>


    <!-- SCHOOL HEADER -->

    <div class="letterhead">
        <img class="logo" src="<?php echo WEB_ROOT; ?>csr.png" alt="" onerror="this.style.visibility='hidden';">
        <div class="text">
            <div class="school-name">Colegio de Santa Rita de San Carlos, Inc.</div>
            <div class="school-line">Atienza Avenue, San Carlos City, Negros Occidental</div>
            <div class="school-line">Academic Program and Subject Listing</div>
        </div>
    </div>
    <div class="rule"></div>


    <!-- REPORT TITLE -->

    <div class="report-title">

        <h2>
            Curriculum Report
        </h2>
        <p><?php echo htmlspecialchars($course->COURSE_NAME); ?> (<?php echo htmlspecialchars($course->COURSE_CODE); ?>)</p>

    </div>


    <!-- COURSE INFORMATION -->

    <table class="course-info">

        <tr>

            <td class="label">
                Course Code
            </td>

            <td>
                <?php echo htmlspecialchars($course->COURSE_CODE); ?>
            </td>

            <td class="label">
                Status
            </td>

            <td>
                <?php echo htmlspecialchars($course->STATUS); ?>
            </td>

        </tr>

        <tr>

            <td class="label">
                Course Name
            </td>

            <td colspan="3">
                <?php echo htmlspecialchars($course->COURSE_NAME); ?>
            </td>

        </tr>

        <tr>

            <td class="label">
                Description
            </td>

            <td colspan="3">
                <?php
                echo !empty($course->COURSE_DESC)
                    ? nl2br(htmlspecialchars($course->COURSE_DESC))
                    : 'No description provided.';
                ?>
            </td>

        </tr>

    </table>


    <!-- SUBJECTS -->

    <?php if (count($subjects) < 1): ?>

        <table class="curriculum">

            <tr>

                <td style="text-align:center; padding:20px;">
                    No subjects have been added under this course.
                </td>

            </tr>

        </table>

    <?php else: ?>

        <?php foreach ($grouped as $groupLabel => $groupSubjects): ?>

            <?php

            $semester_units = 0;
            $semester_amount = 0;

            foreach ($groupSubjects as $subject) {

                $subject_units = (int)$subject->UNITS;

                $subject_price = isset($subject->PRICE_PER_UNIT)
                    ? (float)$subject->PRICE_PER_UNIT
                    : 900;

                $subject_amount = $subject_units * $subject_price;

                $semester_units += $subject_units;
                $semester_amount += $subject_amount;
            }

            ?>

            <div class="semester-title">

                <?php echo htmlspecialchars($groupLabel); ?>

            </div>

            <table class="curriculum">

                <thead>

                    <tr>

                        <th class="code">
                            Subject Code
                        </th>

                        <th class="subject-name">
                            Subject Name
                        </th>

                        <th class="units">
                            Units
                        </th>

                        <th class="price">
                            Price / Unit
                        </th>

                        <th class="amount">
                            Amount
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($groupSubjects as $subject): ?>

                        <?php

                        $subject_units = (int)$subject->UNITS;

                        $subject_price = isset($subject->PRICE_PER_UNIT)
                            ? (float)$subject->PRICE_PER_UNIT
                            : 900;

                        $subject_amount =
                            $subject_units * $subject_price;

                        ?>

                        <tr>

                            <td class="code">
                                <?php
                                echo htmlspecialchars(
                                    $subject->SUBJECT_CODE
                                );
                                ?>
                            </td>

                            <td class="subject-name">
                                <?php
                                echo htmlspecialchars(
                                    $subject->SUBJECT_NAME
                                );
                                ?>
                            </td>

                            <td class="units">
                                <?php
                                echo $subject_units;
                                ?>
                            </td>

                            <td class="price">
                                ₱<?php
                                echo number_format(
                                    $subject_price,
                                    2
                                );
                                ?>
                            </td>

                            <td class="amount">
                                ₱<?php
                                echo number_format(
                                    $subject_amount,
                                    2
                                );
                                ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <tr class="semester-total">

                        <td colspan="2" style="text-align:right;">
                            Total for
                            <?php
                            echo htmlspecialchars($groupLabel);
                            ?>
                        </td>

                        <td class="units">
                            <?php
                            echo $semester_units;
                            ?>
                        </td>

                        <td></td>

                        <td class="amount">
                            ₱<?php
                            echo number_format(
                                $semester_amount,
                                2
                            );
                            ?>
                        </td>

                    </tr>

                </tbody>

            </table>

        <?php endforeach; ?>

    <?php endif; ?>


    <!-- GRAND TOTAL -->

    <div class="grand-total">

        <table>

            <tr>

                <td class="label">
                    TOTAL NUMBER OF SUBJECTS
                </td>

                <td class="value">
                    <?php echo count($subjects); ?>
                </td>

            </tr>

            <tr>

                <td class="label">
                    TOTAL UNITS
                </td>

                <td class="value">
                    <?php echo $total_units; ?>
                </td>

            </tr>

            <tr>

                <td class="label">
                    TOTAL CURRICULUM AMOUNT
                </td>

                <td class="value">
                    ₱<?php echo number_format($total_amount, 2); ?>
                </td>

            </tr>

        </table>

    </div>


    <!-- SIGNATURE AREA -->

    <div class="report-footer">

        <div class="signature">

            <div class="signature-line"></div>

            <div class="signature-label">
                Prepared By
            </div>

        </div>

        <div class="signature">

            <div class="signature-line"></div>

            <div class="signature-label">
                Approved By
            </div>

        </div>

    </div>


    <!-- GENERATED -->

    <div class="generated">

        Generated from the School Information System

    </div>

</div>


<script>

    /*
     * Automatically open the browser print dialog
     * after the page has loaded.
     *
     * Remove this window.print() if you want the
     * user to click the Print Curriculum button.
     */

    window.onload = function() {

        setTimeout(function() {

            window.print();

        }, 500);

    };

</script>

</body>
</html>