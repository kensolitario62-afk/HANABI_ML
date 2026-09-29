<?php

require_once("../../include/initialize.php");
require_once(dirname(__FILE__) . '/style.php');

global $mydb;


$id = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;


$mydb->setQuery("
    SELECT
        id,
        instructor_id,
        name,
        description
    FROM tblinstructor
    WHERE id = " . $id . "
    LIMIT 1
");

$instructor = $mydb->loadSingleResult();


if (!$instructor) {

    message("Instructor not found.", "error");

    redirect(WEB_ROOT . "module/instructor/index.php?view=list");

    exit;

}


/* Classes this instructor is currently assigned to (latest first). */
$assigned = array();

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
        st.time_end
    FROM tblsetschedule ss
    LEFT JOIN tblsubjects sub    ON sub.SUBJECT_ID = ss.subject_id
    LEFT JOIN tblsections sec    ON sec.SECTION_ID = ss.section_id
    LEFT JOIN tblscheduleday sd  ON sd.id = ss.day_id
    LEFT JOIN tblscheduletime st ON st.id = ss.time_id
    WHERE ss.instructor_id = " . (int)$instructor->id . "
    ORDER BY ss.school_year DESC, ss.semester ASC, sd.name ASC, st.time_start ASC
");

$found = $mydb->loadResultList();
if ($found) {
    $assigned = $found;
}


function in_tile($label, $value, $icon, $full = false)
{
    echo '<div class="ss-tile' . ($full ? ' full' : '') . '"><div><i class="fa ' . $icon . '"></i>'
        . '<div><span>' . $label . '</span><b>' . $value . '</b></div></div></div>';
}

$nameText = trim((string)$instructor->name);
$initial  = strtoupper(substr($nameText !== '' ? $nameText : '?', 0, 1));
$codeText = trim((string)$instructor->instructor_id);
$descText = trim((string)$instructor->description);

?>

<section class="content">

    <div class="container-fluid">

        <?php check_message(); ?>

        <div class="row">

            <!-- ================= LEFT ================= -->
            <div class="col-md-4">

                <div class="card ss-hero">
                    <div class="ss-cover">
                        <div style="width:96px;height:96px;border-radius:50%;background:rgba(255,255,255,.2);border:4px solid rgba(255,255,255,.85);display:inline-flex;align-items:center;justify-content:center;font-size:2.6rem;font-weight:800;box-shadow:0 6px 18px rgba(0,0,0,.35);">
                            <?php echo htmlspecialchars($initial); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($nameText); ?></h3>
                        <small>Instructor</small>
                    </div>
                    <div class="ss-body">
                        <div class="ss-stat"><i class="fa fa-id-card"></i><div><span>Instructor ID</span><b><?php echo $codeText !== '' ? htmlspecialchars($codeText) : '-'; ?></b></div></div>
                        <div class="ss-stat"><i class="fa fa-calendar-check"></i><div><span>Assigned Classes</span><b><?php echo count($assigned); ?></b></div></div>

                        <a href="<?php echo WEB_ROOT; ?>module/instructor/index.php?view=list" class="btn ss-btn main btn-block mt-3">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                        <a href="<?php echo WEB_ROOT; ?>module/instructor/print.php?id=<?php echo (int)$instructor->id; ?>" target="_blank" class="btn ss-btn btn-success btn-block mt-2">
                            <i class="fa fa-print"></i> Print
                        </a>
                    </div>
                </div>

            </div>

            <!-- ================= RIGHT ================= -->
            <div class="col-md-8">

                <div class="card ss-panel">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-chalkboard-teacher mr-1"></i> Instructor Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="ss-info">
                            <?php
                            in_tile('Instructor ID', $codeText !== '' ? htmlspecialchars($codeText) : '-', 'fa-id-card');
                            in_tile('Instructor Name', htmlspecialchars($nameText), 'fa-user');
                            in_tile('Description', $descText !== '' ? nl2br(htmlspecialchars($descText)) : 'No description provided.', 'fa-align-left', true);
                            ?>
                        </div>
                    </div>
                </div>

                <div class="card ss-panel">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-calendar-check mr-1"></i> Assigned Classes</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($assigned) < 1): ?>
                            <div class="ss-empty"><i class="fa fa-inbox"></i>This instructor has no assigned classes yet.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                            <table class="table table-sm mb-0 ss-mini">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Section</th>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Term</th>
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
                                        <td><?php echo htmlspecialchars(trim($a->semester . ' ' . $a->school_year)); ?></td>
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

    </div>

</section>