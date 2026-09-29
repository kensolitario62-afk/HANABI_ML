<?php

require_once(dirname(__FILE__) . '/style.php');

$schedule_time = null;
$classes = array();


if (isset($_GET['id']) && $_GET['id'] != '') {

    $schedule_time_id = (int)$_GET['id'];


    $mydb->setQuery("
        SELECT *
        FROM tblscheduletime
        WHERE id = '".$schedule_time_id."'
        LIMIT 1
    ");


    $schedule_time = $mydb->loadSingleResult();


    if ($schedule_time) {

        /* Class schedules that use this time slot. */
        $mydb->setQuery("
            SELECT
                ss.id,
                ss.semester,
                ss.school_year,
                sub.SUBJECT_CODE,
                sub.SUBJECT_NAME,
                sec.SECTION_NAME,
                sec.YEAR_LEVEL,
                sd.name AS DAY_NAME,
                c.name  AS CLASSROOM_NAME,
                i.name  AS INSTRUCTOR_NAME
            FROM tblsetschedule ss
            LEFT JOIN tblsubjects sub   ON sub.SUBJECT_ID = ss.subject_id
            LEFT JOIN tblsections sec   ON sec.SECTION_ID = ss.section_id
            LEFT JOIN tblscheduleday sd ON sd.id = ss.day_id
            LEFT JOIN tblclassroom c    ON c.id = ss.classroom_id
            LEFT JOIN tblinstructor i   ON i.id = ss.instructor_id
            WHERE ss.time_id = {$schedule_time_id}
            ORDER BY ss.school_year DESC, ss.semester ASC, sd.name ASC
            LIMIT 20
        ");

        $found = $mydb->loadResultList();

        if ($found) {
            $classes = $found;
        }
    }

}


function st_tile($label, $value, $icon, $full = false)
{
    echo '<div class="ss-tile' . ($full ? ' full' : '') . '"><div><i class="fa ' . $icon . '"></i>'
        . '<div><span>' . htmlspecialchars($label) . '</span><b>' . $value . '</b></div></div></div>';
}

/* "1 hr 30 mins" between two HH:MM(:SS) values. */
function st_duration($start, $end)
{
    $diff = strtotime($end) - strtotime($start);

    if ($diff <= 0) { return '-'; }

    $h = floor($diff / 3600);
    $m = floor(($diff % 3600) / 60);

    $out = array();
    if ($h > 0) { $out[] = $h . ' hr' . ($h > 1 ? 's' : ''); }
    if ($m > 0) { $out[] = $m . ' min' . ($m > 1 ? 's' : ''); }

    return implode(' ', $out);
}

?>

<section class="content">

    <div class="container-fluid">


        <?php if (!$schedule_time): ?>


            <div class="alert alert-warning">

                No schedule time was selected.

                Please go back to the

                <a href="<?php echo WEB_ROOT; ?>module/scheduletime/">

                    schedule time list

                </a>

                and click the view button of a schedule time.

            </div>


        <?php else: ?>

            <?php
            $startText = date("h:i A", strtotime($schedule_time->time_start));
            $endText   = date("h:i A", strtotime($schedule_time->time_end));
            $duration  = st_duration($schedule_time->time_start, $schedule_time->time_end);
            $descText  = trim((string)$schedule_time->description);
            ?>

            <div class="row">


                <!-- ================= LEFT ================= -->

                <div class="col-md-4">

                    <div class="card ss-hero">

                        <div class="ss-cover">
                            <div style="width:84px;height:84px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:2.4rem;">
                                <i class="fa fa-clock"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($startText); ?> - <?php echo htmlspecialchars($endText); ?></h3>
                            <small>Schedule Time</small>
                        </div>

                        <div class="ss-body">

                            <div class="ss-stat"><i class="fa fa-hashtag"></i><div><span>Schedule Time ID</span><b><?php echo (int)$schedule_time->id; ?></b></div></div>
                            <div class="ss-stat"><i class="fa fa-hourglass-half"></i><div><span>Duration</span><b><?php echo htmlspecialchars($duration); ?></b></div></div>
                            <div class="ss-stat"><i class="fa fa-calendar-check"></i><div><span>Classes Using It</span><b><?php echo count($classes); ?><?php echo count($classes) >= 20 ? '+' : ''; ?></b></div></div>

                            <a href="<?php echo WEB_ROOT; ?>module/scheduletime/" class="btn ss-btn main btn-block mt-3">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                            <a href="<?php echo WEB_ROOT; ?>module/scheduletime/print.php?id=<?php echo (int)$schedule_time->id; ?>" target="_blank" class="btn ss-btn btn-success btn-block mt-2">
                                <i class="fa fa-print"></i> Print
                            </a>

                        </div>

                    </div>

                </div>


                <!-- ================= RIGHT ================= -->

                <div class="col-md-8">

                    <div class="card ss-panel">

                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-clock mr-1"></i> Schedule Time Information</h3>
                        </div>

                        <div class="card-body">
                            <div class="ss-info">
                                <?php
                                st_tile('Time Start', htmlspecialchars($startText), 'fa-clock-o');
                                st_tile('Time End', htmlspecialchars($endText), 'fa-hourglass-end');
                                st_tile('Duration', htmlspecialchars($duration), 'fa-hourglass-half');
                                st_tile('Schedule Time ID', (int)$schedule_time->id, 'fa-hashtag');
                                st_tile('Description', $descText !== '' ? nl2br(htmlspecialchars($descText)) : 'No description provided.', 'fa-align-left', true);
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

                                <div class="ss-empty"><i class="fa fa-inbox"></i>No class schedules use this time slot yet.</div>

                            <?php else: ?>

                                <div class="table-responsive">
                                <table class="table table-sm mb-0 ss-mini">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Section</th>
                                            <th>Day</th>
                                            <th>Room</th>
                                            <th>Instructor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($classes as $a): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars((string)$a->SUBJECT_CODE); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars((string)$a->SUBJECT_NAME); ?></small></td>
                                            <td><?php echo htmlspecialchars(trim($a->YEAR_LEVEL . ' - ' . $a->SECTION_NAME, ' -')); ?></td>
                                            <td><?php echo htmlspecialchars((string)$a->DAY_NAME); ?></td>
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