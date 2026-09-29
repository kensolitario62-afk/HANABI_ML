<?php

require_once(dirname(__FILE__) . '/style.php');

$schedule_day = null;
$classes = array();


if (isset($_GET['id']) && $_GET['id'] != '') {

    $schedule_day_id = (int)$_GET['id'];


    $mydb->setQuery("
        SELECT *
        FROM tblscheduleday
        WHERE id = '".$schedule_day_id."'
        LIMIT 1
    ");


    $schedule_day = $mydb->loadSingleResult();


    if ($schedule_day) {

        /* Class schedules that use this day. */
        $mydb->setQuery("
            SELECT
                ss.id,
                ss.semester,
                ss.school_year,
                sub.SUBJECT_CODE,
                sub.SUBJECT_NAME,
                sec.SECTION_NAME,
                sec.YEAR_LEVEL,
                st.time_start,
                st.time_end,
                c.name  AS CLASSROOM_NAME,
                i.name  AS INSTRUCTOR_NAME
            FROM tblsetschedule ss
            LEFT JOIN tblsubjects sub     ON sub.SUBJECT_ID = ss.subject_id
            LEFT JOIN tblsections sec     ON sec.SECTION_ID = ss.section_id
            LEFT JOIN tblscheduletime st  ON st.id = ss.time_id
            LEFT JOIN tblclassroom c      ON c.id = ss.classroom_id
            LEFT JOIN tblinstructor i     ON i.id = ss.instructor_id
            WHERE ss.day_id = {$schedule_day_id}
            ORDER BY ss.school_year DESC, ss.semester ASC, st.time_start ASC
            LIMIT 20
        ");

        $found = $mydb->loadResultList();

        if ($found) {
            $classes = $found;
        }
    }

}


function sd_tile($label, $value, $icon, $full = false)
{
    echo '<div class="ss-tile' . ($full ? ' full' : '') . '"><div><i class="fa ' . $icon . '"></i>'
        . '<div><span>' . htmlspecialchars($label) . '</span><b>' . $value . '</b></div></div></div>';
}

?>

<section class="content">

    <div class="container-fluid">


        <?php if (!$schedule_day): ?>


            <div class="alert alert-warning">

                No schedule day was selected.

                Please go back to the

                <a href="<?php echo WEB_ROOT; ?>module/scheduleday/">

                    schedule day list

                </a>

                and click the view button of a schedule day.

            </div>


        <?php else: ?>

            <?php
            $nameText = $schedule_day->name;
            $descText = trim((string)$schedule_day->description);
            ?>

            <div class="row">


                <!-- ================= LEFT ================= -->

                <div class="col-md-4">

                    <div class="card ss-hero">

                        <div class="ss-cover">
                            <div style="width:84px;height:84px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:2.4rem;">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($nameText); ?></h3>
                            <small>Schedule Day</small>
                        </div>

                        <div class="ss-body">

                            <div class="ss-stat"><i class="fa fa-hashtag"></i><div><span>Schedule Day ID</span><b><?php echo (int)$schedule_day->id; ?></b></div></div>
                            <div class="ss-stat"><i class="fa fa-calendar-check"></i><div><span>Classes Using It</span><b><?php echo count($classes); ?><?php echo count($classes) >= 20 ? '+' : ''; ?></b></div></div>

                            <a href="<?php echo WEB_ROOT; ?>module/scheduleday/" class="btn ss-btn main btn-block mt-3">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                            <a href="<?php echo WEB_ROOT; ?>module/scheduleday/print.php?id=<?php echo (int)$schedule_day->id; ?>" target="_blank" class="btn ss-btn btn-success btn-block mt-2">
                                <i class="fa fa-print"></i> Print
                            </a>

                        </div>

                    </div>

                </div>


                <!-- ================= RIGHT ================= -->

                <div class="col-md-8">

                    <div class="card ss-panel">

                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-calendar mr-1"></i> Schedule Day Information</h3>
                        </div>

                        <div class="card-body">
                            <div class="ss-info">
                                <?php
                                sd_tile('Day Name', htmlspecialchars($nameText), 'fa-calendar-day');
                                sd_tile('Schedule Day ID', (int)$schedule_day->id, 'fa-hashtag');
                                sd_tile('Description', $descText !== '' ? nl2br(htmlspecialchars($descText)) : 'No description provided.', 'fa-align-left', true);
                                ?>
                            </div>
                        </div>

                    </div>


                    <div class="card ss-panel">

                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-calendar-check mr-1"></i> Class Schedules</h3>
                        </div>

                        <div class="card-body p-0">

                            <?php if (count($classes) < 1): ?>

                                <div class="ss-empty"><i class="fa fa-inbox"></i>No class schedules use this day yet.</div>

                            <?php else: ?>

                                <div class="table-responsive">
                                <table class="table table-sm mb-0 ss-mini">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Section</th>
                                            <th>Time</th>
                                            <th>Room</th>
                                            <th>Instructor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($classes as $a): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars((string)$a->SUBJECT_CODE); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars((string)$a->SUBJECT_NAME); ?></small></td>
                                            <td><?php echo htmlspecialchars(trim($a->YEAR_LEVEL . ' - ' . $a->SECTION_NAME, ' -')); ?></td>
                                            <td><?php
                                                $ts = !empty($a->time_start) ? date("h:i A", strtotime($a->time_start)) : '';
                                                $te = !empty($a->time_end) ? date("h:i A", strtotime($a->time_end)) : '';
                                                echo htmlspecialchars(trim($ts . ($ts && $te ? ' - ' : '') . $te));
                                            ?></td>
                                            <td><?php echo htmlspecialchars((string)$a->CLASSROOM_NAME); ?></td>
                                            <td><?php echo htmlspecialchars((string)$a->INSTRUCTOR_NAME); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


            </div>


        <?php endif; ?>


    </div>

</section>