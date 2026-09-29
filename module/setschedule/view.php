<?php

global $mydb;

require_once(dirname(__FILE__) . '/style.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo '<div class="alert alert-danger m-3">Invalid schedule ID.</div>';
    return;
}

$mydb->setQuery("
    SELECT
        ss.id,
        ss.semester,
        ss.school_year,

        d.name AS DEPARTMENT_NAME,

        co.COURSE_CODE,
        co.COURSE_NAME,

        sec.SECTION_NAME,
        sec.YEAR_LEVEL,

        sub.SUBJECT_CODE,
        sub.SUBJECT_NAME,
        sub.UNITS,

        c.name AS CLASSROOM_NAME,
        sd.name AS DAY_NAME,

        st.time_start,
        st.time_end,

        i.name AS INSTRUCTOR_NAME,
        i.instructor_id AS INSTRUCTOR_CODE

    FROM tblsetschedule ss

    LEFT JOIN tbldepartment d   ON d.id = ss.department_id
    LEFT JOIN tblsections sec   ON sec.SECTION_ID = ss.section_id
    LEFT JOIN tblcourses co     ON co.COURSE_ID = sec.COURSE_ID
    LEFT JOIN tblsubjects sub   ON sub.SUBJECT_ID = ss.subject_id
    LEFT JOIN tblclassroom c    ON c.id = ss.classroom_id
    LEFT JOIN tblscheduleday sd ON sd.id = ss.day_id
    LEFT JOIN tblscheduletime st ON st.id = ss.time_id
    LEFT JOIN tblinstructor i   ON i.id = ss.instructor_id

    WHERE ss.id = {$id}
    LIMIT 1
");

$row = $mydb->loadSingleResult();

if (!$row) {
    echo '<div class="alert alert-danger m-3">Schedule record not found.</div>';
    return;
}

/* Small helper: escaped value with a fallback label. */
function ss_val($value, $fallback = 'Not Set')
{
    $value = trim((string)$value);
    return $value !== '' ? htmlspecialchars($value) : $fallback;
}

$time = 'Not Set';

if (!empty($row->time_start) && !empty($row->time_end)) {
    $time = date('h:i A', strtotime($row->time_start))
        . ' - '
        . date('h:i A', strtotime($row->time_end));
}

$course = trim($row->COURSE_CODE . ' - ' . $row->COURSE_NAME, ' -');

$sectionText = trim(
    $row->YEAR_LEVEL . ($row->SECTION_NAME !== null && $row->SECTION_NAME !== '' ? ' - Section ' . $row->SECTION_NAME : ''),
    ' -'
);

$instructor = trim((string)$row->INSTRUCTOR_NAME);

if ($instructor !== '' && !empty($row->INSTRUCTOR_CODE)) {
    $instructor .= ' - ' . $row->INSTRUCTOR_CODE;
}

$units = ($row->UNITS !== null && $row->UNITS !== '') ? (int)$row->UNITS : 0;

$scheduleBackUrl = WEB_ROOT . 'module/setschedule/';

?>

<section class="content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-md-4">

                <div class="card ss-hero">
                    <div class="ss-cover">
                        <div class="ss-ico"><i class="fa fa-calendar"></i></div>
                        <h3><?php echo ss_val($row->SUBJECT_NAME, 'N/A'); ?></h3>
                        <small>Class Schedule</small>
                    </div>
                    <div class="ss-body">
                        <div class="ss-stat"><i class="fa fa-hashtag"></i><div><span>Schedule ID</span><b><?php echo (int)$row->id; ?></b></div></div>
                        <div class="ss-stat"><i class="fa fa-bookmark"></i><div><span>Subject Code</span><b><?php echo ss_val($row->SUBJECT_CODE, 'N/A'); ?></b></div></div>
                        <div class="ss-stat"><i class="fa fa-calendar"></i><div><span>Day</span><b><?php echo ss_val($row->DAY_NAME); ?></b></div></div>
                        <div class="ss-stat"><i class="fa fa-clock-o"></i><div><span>Time</span><b><?php echo htmlspecialchars($time); ?></b></div></div>
                        <a href="<?php echo $scheduleBackUrl; ?>" class="btn ss-btn main btn-block mt-3">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card ss-panel">
                    <div class="card-header"><h3 class="card-title"><i class="fa fa-file-text-o mr-1"></i> Summary</h3></div>
                    <div class="card-body">
                        <div class="ss-summary">
                            <?php echo ss_val($row->SUBJECT_CODE, 'N/A'); ?>
                            (<?php echo $units; ?> unit<?php echo $units === 1 ? '' : 's'; ?>)
                            is scheduled for
                            <?php echo ss_val($course, 'N/A'); ?>,
                            <?php echo ss_val($sectionText, 'N/A'); ?>,
                            every <?php echo ss_val($row->DAY_NAME); ?>
                            at <?php echo htmlspecialchars($time); ?>
                            in <?php echo ss_val($row->CLASSROOM_NAME, 'an unassigned room'); ?>,
                            <?php echo ss_val($row->semester); ?>,
                            S.Y. <?php echo ss_val($row->school_year); ?>.
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-8">

                <div class="card ss-panel">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-calendar mr-1"></i> Schedule Information</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $tiles = array(
                            array('Department',           ss_val($row->DEPARTMENT_NAME),              'fa-building', false),
                            array('Course',               ss_val($course),                            'fa-book', false),
                            array('Section',              ss_val($sectionText),                       'fa-users', false),
                            array('Subject Code',         ss_val($row->SUBJECT_CODE, 'N/A'),          'fa-bookmark', false),
                            array('Subject Description',  ss_val($row->SUBJECT_NAME, 'N/A'),          'fa-file-text-o', true),
                            array('Units',                (string)$units,                             'fa-star', false),
                            array('Classroom',            ss_val($row->CLASSROOM_NAME, 'Not Assigned'), 'fa-map-marker', false),
                            array('Schedule Day',         ss_val($row->DAY_NAME),                     'fa-calendar', false),
                            array('Schedule Time',        htmlspecialchars($time),                    'fa-clock-o', false),
                            array('Instructor',           ss_val($instructor, 'Not Assigned'),        'fa-user', true),
                            array('Semester',             ss_val($row->semester),                     'fa-flag', false),
                            array('School Year',          ss_val($row->school_year),                  'fa-calendar-o', false)
                        );
                        ?>
                        <div class="ss-info">
                            <?php foreach ($tiles as $t): ?>
                                <div class="ss-tile<?php echo $t[3] ? ' full' : ''; ?>">
                                    <div>
                                        <i class="fa <?php echo $t[2]; ?>"></i>
                                        <div><span><?php echo $t[0]; ?></span><b><?php echo $t[1]; ?></b></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card ss-panel">
                    <div class="card-body">
                        <a href="<?php echo $scheduleBackUrl; ?>" class="btn ss-btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        <a href="<?php echo WEB_ROOT; ?>module/setschedule/print.php?id=<?php echo (int)$row->id; ?>"
                           target="_blank" class="btn ss-btn btn-success">
                            <i class="fa fa-print"></i> Print
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>