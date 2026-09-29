<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;

/*
|--------------------------------------------------------------------------
| SCHOOL HEADER  (edit these once)
|--------------------------------------------------------------------------
| 'logo' = the school seal image. Point it at the same file your sidebar uses.
|--------------------------------------------------------------------------
*/

$school = array(
    'name'    => 'COLEGIO DE SANTA RITA DE SAN CARLOS, INC.',
    'address' => 'Atienza Avenue, San Carlos City, Negros Occidental',
    'tel'     => '(034) 312-6212',
    'email'   => 'colegiodestarita_scr@yahoo.com',
    'logo'    => WEB_ROOT . 'csr.png'
);

/*
|--------------------------------------------------------------------------
| MODES
|--------------------------------------------------------------------------
| ?id=5
|     One schedule record (View page -> Print).
|
| ?section_id=1&school_year=2025-2026&semester=1st Semester[&subject_id=4]
|     Whole class schedule of a section (all subjects, or one Subject).
|--------------------------------------------------------------------------
*/

$id          = isset($_GET['id'])          ? intval($_GET['id'])          : 0;
$section_id  = isset($_GET['section_id'])  ? intval($_GET['section_id'])  : 0;
$subject_id  = isset($_GET['subject_id'])  ? intval($_GET['subject_id'])  : 0;
$semester    = isset($_GET['semester'])    ? trim($_GET['semester'])      : '';
$school_year = isset($_GET['school_year']) ? trim($_GET['school_year'])   : '';

function ps_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES);
}

/* "13:00:00" -> array("1:00", "PM"); 12:00 PM is shown as NOON. */
function ps_time_parts($time)
{
    $ts = strtotime($time);
    $h  = (int)date('g', $ts);
    $m  = date('i', $ts);
    $ap = date('A', $ts);

    if ($h === 12 && $m === '00' && $ap === 'PM') {
        $ap = 'NOON';
    }

    return array($h . ':' . $m, $ap);
}

/* "1:00 – 4:00 PM", "10:00 AM – 12:30 PM", "10:00 – 12:00 NOON" */
function ps_time_range($start, $end)
{
    list($st, $sa) = ps_time_parts($start);
    list($et, $ea) = ps_time_parts($end);

    if ($sa === $ea || ($ea === 'NOON' && $sa === 'AM')) {
        return $st . ' – ' . $et . ' ' . $ea;
    }

    return $st . ' ' . $sa . ' – ' . $et . ' ' . $ea;
}

/* Sort weight from the first day in the code: M, T/TTh, W, Th, F, SAT. */
function ps_day_weight($name)
{
    $n = strtoupper(trim((string)$name));

    if (strpos($n, 'SAT') === 0) return 6;
    if (strpos($n, 'SUN') === 0) return 7;
    if (strpos($n, 'TH')  === 0) return 4;

    switch (substr($n, 0, 1)) {
        case 'M': return 1;
        case 'T': return 2;
        case 'W': return 3;
        case 'F': return 5;
    }

    return 9;
}

$semesterNames = array(
    '1st Semester' => 'FIRST SEMESTER',
    '2nd Semester' => 'SECOND SEMESTER',
    'Summer'       => 'SUMMER'
);

$where = '';
$error = '';

if ($id > 0) {

    $where = "WHERE ss.id = {$id}";

} elseif ($section_id > 0 && $semester !== '' && $school_year !== '') {

    $where = "WHERE ss.section_id = {$section_id}"
        . " AND ss.semester = '" . $mydb->escape_value($semester) . "'"
        . " AND ss.school_year = '" . $mydb->escape_value($school_year) . "'";

    if ($subject_id > 0) {
        $where .= " AND ss.subject_id = {$subject_id}";
    }

} else {

    $error = 'Please choose a Course, Section, School Year and Semester.';
}

$rows = array();

if ($error === '') {

    $mydb->setQuery("
        SELECT
            ss.id,
            ss.semester,
            ss.school_year,

            cse.COURSE_CODE,
            cse.COURSE_NAME,

            sec.SECTION_NAME,
            sec.YEAR_LEVEL,

            sub.SUBJECT_CODE,
            sub.SUBJECT_NAME,
            sub.UNITS,

            c.name AS CLASSROOM_NAME,
            sd.name AS DAY_NAME,

            st.time_start,
            st.time_end,

            i.name AS INSTRUCTOR_NAME

        FROM tblsetschedule ss

        LEFT JOIN tblsections sec    ON sec.SECTION_ID = ss.section_id
        LEFT JOIN tblcourses cse     ON cse.COURSE_ID = sec.COURSE_ID
        LEFT JOIN tblsubjects sub    ON sub.SUBJECT_ID = ss.subject_id
        LEFT JOIN tblclassroom c     ON c.id = ss.classroom_id
        LEFT JOIN tblscheduleday sd  ON sd.id = ss.day_id
        LEFT JOIN tblscheduletime st ON st.id = ss.time_id
        LEFT JOIN tblinstructor i    ON i.id = ss.instructor_id

        {$where}
    ");

    $found = $mydb->loadResultList();
    $rows  = $found ? $found : array();

    /* Day group first (M, T/TTh, W, Th, F, SAT), then time of day. */
    usort($rows, function ($a, $b) {

        $wa = ps_day_weight($a->DAY_NAME);
        $wb = ps_day_weight($b->DAY_NAME);

        if ($wa !== $wb) {
            return $wa - $wb;
        }

        $cmp = strcmp((string)$a->DAY_NAME, (string)$b->DAY_NAME);

        if ($cmp !== 0) {
            return $cmp;
        }

        return strcmp((string)$a->time_start, (string)$b->time_start);
    });
}

$head       = $rows ? $rows[0] : null;
$totalUnits = 0;

foreach ($rows as $r) {
    $totalUnits += (int)$r->UNITS;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <title>Schedule of Classes</title>

    <style>

        @page { size: A4 portrait; margin: 12mm; }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: Cambria, "Times New Roman", Times, serif;
            color: #000;
            margin: 0;
            padding: 16px;
        }

        .toolbar {
            margin-bottom: 14px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar button,
        .toolbar a {
            font-size: 13px;
            padding: 6px 14px;
            margin-right: 6px;
            border: 1px solid #555;
            border-radius: 3px;
            background: #f4f4f4;
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        .sheet { max-width: 190mm; margin: 0 auto; }

        /* ---------- letterhead ---------- */

        .letterhead {
            display: flex;
            align-items: center;
            margin-bottom: 14px;
        }

        .letterhead .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            flex: 0 0 80px;
        }

        .letterhead .text {
            flex: 1;
            text-align: center;
            padding-right: 80px;           /* balances the logo so text is centred */
            line-height: 1.35;
        }

        .school-name { font-size: 17px; font-weight: bold; letter-spacing: .3px; }
        .school-line { font-size: 12px; }
        .school-line a { color: #1a4fbf; text-decoration: underline; }

        /* ---------- titles ---------- */

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            text-decoration: underline;
            margin: 6px 0 0 0;
        }

        .term {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .set {
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            color: #1a5fd0;
            text-decoration: underline;
            margin: 2px 0 4px 0;
        }

        .course-bar {
            background: #e8686b;
            border: 1px solid #555;
            border-bottom: 0;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            padding: 3px 6px;
            text-transform: uppercase;
        }

        /* ---------- table ---------- */

        table.sched {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.sched th,
        table.sched td {
            border: 1px solid #555;
            padding: 5px 6px;
            text-align: center;
            vertical-align: top;
            font-size: 14px;
            text-transform: uppercase;
            word-wrap: break-word;
        }

        table.sched thead th {
            background: #e8686b;
            color: #fbe3e3;
            font-size: 12px;
            font-weight: bold;
        }

        table.sched tr.sep td {
            background: #1f8ad6;
            height: 14px;
            padding: 0;
        }

        table.sched tr.total td { height: 24px; font-weight: bold; }

        .empty {
            border: 1px solid #999;
            padding: 24px;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
        }

        @media print {
            .toolbar { display: none; }
            body { padding: 0; }
        }

    </style>

</head>
<body>

    <div class="toolbar">
        <button type="button" onclick="window.print();">Print</button>
        <a href="javascript:window.close();">Close</a>
    </div>

    <?php if ($error !== ''): ?>

        <div class="empty"><?php echo ps_e($error); ?></div>

    <?php elseif (!$head): ?>

        <div class="empty">No schedule found for the selected filters.</div>

    <?php else: ?>

        <?php
        $termName = isset($semesterNames[$head->semester])
            ? $semesterNames[$head->semester]
            : strtoupper($head->semester);
        ?>

        <div class="sheet">

            <div class="letterhead">

                <img
                    class="logo"
                    src="<?php echo ps_e($school['logo']); ?>"
                    alt=""
                    onerror="this.style.visibility='hidden';"
                >

                <div class="text">
                    <div class="school-name"><?php echo ps_e($school['name']); ?></div>
                    <div class="school-line"><?php echo ps_e($school['address']); ?></div>
                    <div class="school-line">Tel. No.: <?php echo ps_e($school['tel']); ?></div>
                    <div class="school-line">
                        Email: <a href="mailto:<?php echo ps_e($school['email']); ?>"><?php echo ps_e($school['email']); ?></a>
                    </div>
                </div>

            </div>

            <p class="title">SCHEDULE OF CLASSES</p>
            <p class="term"><?php echo ps_e($termName); ?> A.Y. <?php echo ps_e($head->school_year); ?></p>
            <p class="set"><?php echo ps_e(strtoupper($head->YEAR_LEVEL)); ?>-SET <?php echo ps_e(strtoupper($head->SECTION_NAME)); ?></p>

            <div class="course-bar">
                <?php echo ps_e($head->COURSE_NAME); ?> (<?php echo ps_e($head->COURSE_CODE); ?>)
            </div>

            <table class="sched">

                <thead>
                    <tr>
                        <th style="width:16%;">TIME</th>
                        <th style="width:9%;">DAY</th>
                        <th style="width:11%;">CODE</th>
                        <th style="width:29%;">SUBJECT DESCRIPTION</th>
                        <th style="width:7%;">UNITS</th>
                        <th style="width:13%;">ROOM</th>
                        <th style="width:15%;">INSTRUCTOR</th>
                    </tr>
                </thead>

                <tbody>

                    <?php $prevDay = null; ?>

                    <?php foreach ($rows as $r): ?>

                        <?php
                        /* Blue divider whenever the day code changes. */
                        if ($prevDay !== null && strcasecmp($prevDay, (string)$r->DAY_NAME) !== 0) {
                            echo '<tr class="sep"><td colspan="7"></td></tr>';
                        }
                        $prevDay = (string)$r->DAY_NAME;
                        ?>

                        <tr>
                            <td>
                                <?php
                                if (!empty($r->time_start) && !empty($r->time_end)) {
                                    echo ps_e(ps_time_range($r->time_start, $r->time_end));
                                }
                                ?>
                            </td>
                            <td><?php echo ps_e($r->DAY_NAME); ?></td>
                            <td><?php echo ps_e($r->SUBJECT_CODE); ?></td>
                            <td><?php echo ps_e($r->SUBJECT_NAME); ?></td>
                            <td><?php echo (int)$r->UNITS; ?></td>
                            <td><?php echo ps_e($r->CLASSROOM_NAME); ?></td>
                            <td><?php echo ps_e($r->INSTRUCTOR_NAME); ?></td>
                        </tr>

                    <?php endforeach; ?>

                    <tr class="total">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo (int)$totalUnits; ?></td>
                        <td></td>
                        <td></td>
                    </tr>

                </tbody>

            </table>

        </div>

        <script>
            window.addEventListener('load', function () { window.print(); });
        </script>

    <?php endif; ?>

</body>
</html>