<?php

require_once(dirname(__FILE__) . '/style.php');

$department = null;
$assigned   = array();


if (
    isset($_GET['id']) &&
    $_GET['id'] != ''
) {

    $department_id = (int)$_GET['id'];


    $mydb->setQuery("
        SELECT
            id,
            name,
            description
        FROM tbldepartment
        WHERE id = {$department_id}
        LIMIT 1
    ");

    $department = $mydb->loadSingleResult();


    if ($department) {

        /* Class schedules that use this department. */
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
                st.time_start,
                st.time_end,
                i.name AS INSTRUCTOR_NAME
            FROM tblsetschedule ss
            LEFT JOIN tblsubjects sub    ON sub.SUBJECT_ID = ss.subject_id
            LEFT JOIN tblsections sec    ON sec.SECTION_ID = ss.section_id
            LEFT JOIN tblscheduleday sd  ON sd.id = ss.day_id
            LEFT JOIN tblscheduletime st ON st.id = ss.time_id
            LEFT JOIN tblinstructor i    ON i.id = ss.instructor_id
            WHERE ss.department_id = {$department_id}
            ORDER BY ss.school_year DESC, ss.semester ASC, sd.name ASC, st.time_start ASC
            LIMIT 20
        ");

        $found = $mydb->loadResultList();

        if ($found) {
            $assigned = $found;
        }

    }

}


function dp_tile($label, $value, $icon, $full = false)
{
    echo '<div class="ss-tile' . ($full ? ' full' : '') . '"><div><i class="fa ' . $icon . '"></i>'
        . '<div><span>' . $label . '</span><b>' . $value . '</b></div></div></div>';
}

?>



<section class="content">

    <div class="container-fluid">


        <?php if (!$department): ?>


            <div class="alert alert-warning">

                <h5>
                    <i class="icon fas fa-exclamation-triangle"></i>
                    Department Not Found
                </h5>

                The selected department does not exist.

            </div>


            <a
                href="<?php echo WEB_ROOT; ?>module/department/"
                class="btn ss-btn main">

                <i class="fa fa-arrow-left"></i>

                Back to Departments

            </a>


        <?php else: ?>

            <?php
            $deptName = htmlspecialchars($department->name, ENT_QUOTES, 'UTF-8');
            $deptDesc = trim((string)$department->description);
            ?>

            <div class="row">


                <!-- ================= LEFT ================= -->

                <div class="col-md-4">

                    <div class="card ss-hero">

                        <div class="ss-cover">
                            <div style="width:84px;height:84px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:2.4rem;">
                                <i class="fa fa-building"></i>
                            </div>
                            <h3><?php echo $deptName; ?></h3>
                            <small>Department</small>
                        </div>

                        <div class="ss-body">

                            <div class="ss-stat"><i class="fa fa-hashtag"></i><div><span>Department ID</span><b><?php echo (int)$department->id; ?></b></div></div>
                            <div class="ss-stat"><i class="fa fa-calendar-check"></i><div><span>Schedules Using It</span><b><?php echo count($assigned); ?><?php echo count($assigned) >= 20 ? '+' : ''; ?></b></div></div>

                            <a href="<?php echo WEB_ROOT; ?>module/department/" class="btn ss-btn main btn-block mt-3">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                            <a href="<?php echo WEB_ROOT; ?>module/department/print.php?id=<?php echo (int)$department->id; ?>" target="_blank" class="btn ss-btn btn-success btn-block mt-2">
                                <i class="fa fa-print"></i> Print
                            </a>

                        </div>

                    </div>

                </div>


                <!-- ================= RIGHT ================= -->

                <div class="col-md-8">

                    <div class="card ss-panel">

                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-building mr-1"></i> Department Information</h3>
                        </div>

                        <div class="card-body">
                            <div class="ss-info">
                                <?php
                                dp_tile('Department ID', (int)$department->id, 'fa-hashtag');
                                dp_tile('Department Name', $deptName, 'fa-building');
                                dp_tile('Description', $deptDesc !== '' ? nl2br(htmlspecialchars($deptDesc, ENT_QUOTES, 'UTF-8')) : 'No description provided.', 'fa-align-left', true);
                                ?>
                            </div>
                        </div>

                    </div>


                    <div class="card ss-panel">

                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-calendar-check mr-1"></i> Class Schedules</h3>
                        </div>

                        <div class="card-body p-0">

                            <?php if (count($assigned) < 1): ?>

                                <div class="ss-empty"><i class="fa fa-inbox"></i>No class schedules use this department yet.</div>

                            <?php else: ?>

                                <div class="table-responsive">
                                <table class="table table-sm mb-0 ss-mini">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Section</th>
                                            <th>Day</th>
                                            <th>Time</th>
                                            <th>Instructor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($assigned as $a):
                                        $time = '-';
                                        if (!empty($a->time_start) && !empty($a->time_end)) {
                                            $time = date('h:i A', strtotime($a->time_start)) . ' - ' . date('h:i A', strtotime($a->time_end));
                                        }
                                    ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars((string)$a->SUBJECT_CODE); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars((string)$a->SUBJECT_NAME); ?></small></td>
                                            <td><?php echo htmlspecialchars(trim($a->YEAR_LEVEL . ' - ' . $a->SECTION_NAME, ' -')); ?></td>
                                            <td><?php echo htmlspecialchars((string)$a->DAY_NAME); ?></td>
                                            <td><?php echo htmlspecialchars($time); ?></td>
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