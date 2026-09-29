<?php

require_once(dirname(__FILE__) . '/style.php');

$course = null;
$subjects = array();
$sections = array();
$studentCount = 0;

if (isset($_GET['id']) && $_GET['id'] != '') {

    $mydb->setQuery("SELECT * FROM `tblcourses` WHERE `COURSE_ID` = '".(int)$_GET['id']."' LIMIT 1");
    $course = $mydb->loadSingleResult();

    if ($course) {

        // Every subject under this course, grouped for display by year level + semester.
        $mydb->setQuery("SELECT * FROM `tblsubjects` WHERE `COURSE_ID` = '".(int)$_GET['id']."' 
            ORDER BY `YEAR_LEVEL` ASC, `SEMESTER` ASC, `SUBJECT_CODE` ASC");
        $subjects = $mydb->loadResultList();

        // Sections offered under this course.
        $mydb->setQuery("SELECT sec.*, sy.SCHOOL_YEAR FROM `tblsections` sec 
            LEFT JOIN `tblschoolyear` sy ON sy.SY_ID = sec.SY_ID 
            WHERE sec.`COURSE_ID` = '".(int)$_GET['id']."' 
            ORDER BY sec.`YEAR_LEVEL` ASC, sec.`SECTION_NAME` ASC");
        $sections = $mydb->loadResultList();

        // How many students currently have this course on their record.
        $mydb->setQuery("SELECT S_ID FROM `tblstudent` WHERE `COURSE_ID` = '".(int)$_GET['id']."'");
        $studentCount = $mydb->num_rows();
    }
}

// Group the subjects by "YEAR_LEVEL - SEMESTER" so the table can be
// broken into readable sections instead of one long flat list.
$grouped = array();
if (isset($subjects)) {
    foreach ($subjects as $s) {
        $key = ($s->YEAR_LEVEL ? $s->YEAR_LEVEL : 'Unassigned').' - '.($s->SEMESTER ? $s->SEMESTER : 'Unassigned');
        if (!isset($grouped[$key])) { $grouped[$key] = array(); }
        $grouped[$key][] = $s;
    }
}
?>

<section class="content">
  <div class="container-fluid">

  <?php if (!$course): ?>
    <div class="alert alert-warning">
      No course was selected. Please go back to the <a href="<?php echo WEB_ROOT; ?>module/course/">course list</a> and click the view button of a course.
    </div>

  <?php else: ?>

    <?php
    $isActive   = ($course->STATUS == 'Active');
    $totalUnits = 0;
    foreach ($subjects as $s) { $totalUnits += (int)$s->UNITS; }
    ?>

    <div class="row">
      <div class="col-md-4">

        <!-- Course summary -->
        <div class="card ss-hero">
          <div class="ss-cover">
            <div class="ss-ico" style="width:84px;height:84px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:2.4rem;">
              <i class="fa fa-graduation-cap"></i>
            </div>
            <h3><?php echo htmlspecialchars($course->COURSE_CODE); ?></h3>
            <small><?php echo htmlspecialchars($course->COURSE_NAME); ?></small>
          </div>
          <div class="ss-body">
            <div class="ss-stat"><i class="fa fa-toggle-on"></i><div><span>Status</span><b><span class="ss-pill" style="<?php echo $isActive ? '' : 'background:rgba(128,128,128,.2);color:inherit;'; ?>"><?php echo htmlspecialchars($course->STATUS); ?></span></b></div></div>
            <div class="ss-stat"><i class="fa fa-book-open"></i><div><span>Subjects</span><b><?php echo count($subjects); ?> <small class="text-muted">(<?php echo $totalUnits; ?> units)</small></b></div></div>
            <div class="ss-stat"><i class="fa fa-chalkboard"></i><div><span>Sections</span><b><?php echo count($sections); ?></b></div></div>
            <div class="ss-stat"><i class="fa fa-users"></i><div><span>Enrolled Students</span><b><?php echo (int)$studentCount; ?></b></div></div>

            <a href="<?php echo WEB_ROOT; ?>module/course/" class="btn ss-btn main btn-block mt-3"><i class="fa fa-arrow-left"></i> Back to List</a>
            <a href="<?php echo WEB_ROOT; ?>module/course/print.php?id=<?php echo (int)$course->COURSE_ID; ?>" target="_blank" class="btn ss-btn btn-success btn-block mt-2"><i class="fa fa-print"></i> Print Curriculum</a>
          </div>
        </div>

        <!-- Description -->
        <div class="card ss-panel">
          <div class="card-header"><h3 class="card-title"><i class="fa fa-align-left mr-1"></i> Description</h3></div>
          <div class="card-body">
            <div class="ss-summary" style="border-left:4px solid var(--ss-b);border-radius:12px;padding:14px 16px;background:var(--ss-soft);line-height:1.7;">
              <?php echo $course->COURSE_DESC ? nl2br(htmlspecialchars($course->COURSE_DESC)) : 'No description provided.'; ?>
            </div>
          </div>
        </div>

        <!-- Sections -->
        <div class="card ss-panel">
          <div class="card-header"><h3 class="card-title"><i class="fa fa-chalkboard mr-1"></i> Sections</h3></div>
          <div class="card-body p-0">
            <?php if (count($sections) < 1): ?>
              <div class="ss-empty"><i class="fa fa-inbox"></i>No sections have been created for this course yet.</div>
            <?php else: ?>
              <table class="table table-sm mb-0 ss-mini">
                <thead><tr><th>Section</th><th>Year Level</th><th>School Year</th></tr></thead>
                <tbody>
                <?php foreach ($sections as $sec): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($sec->SECTION_NAME); ?></strong></td>
                    <td><?php echo htmlspecialchars($sec->YEAR_LEVEL); ?></td>
                    <td><?php echo htmlspecialchars($sec->SCHOOL_YEAR); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>

      </div>
      <!-- /.col -->

      <div class="col-md-8">
        <div class="card ss-panel">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-book mr-1"></i> Curriculum / Subjects</h3>
          </div>
          <div class="card-body pt-2">

            <?php if (count($subjects) < 1): ?>
              <div class="ss-empty"><i class="fa fa-inbox"></i>No subjects have been added under this course yet.</div>
            <?php else: ?>

              <?php foreach ($grouped as $groupLabel => $groupSubjects):
                  $gUnits = 0; $gAmount = 0;
                  foreach ($groupSubjects as $s) { $gUnits += (int)$s->UNITS; $gAmount += $s->UNITS * $s->PRICE_PER_UNIT; }
              ?>
                <div class="ss-sub">
                  <span><i class="fa fa-calendar-check-o mr-1" style="color:var(--ss-b)"></i> <?php echo htmlspecialchars($groupLabel); ?></span>
                  <small><?php echo count($groupSubjects); ?> subjects &middot; <?php echo $gUnits; ?> units</small>
                </div>
                <div class="table-responsive">
                <table class="table table-sm ss-mini">
                  <thead>
                    <tr>
                      <th width="15%">Code</th>
                      <th>Description</th>
                      <th width="10%">Units</th>
                      <th width="15%">Price / Unit</th>
                      <th width="15%">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($groupSubjects as $s): ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($s->SUBJECT_CODE); ?></strong></td>
                        <td><?php echo htmlspecialchars($s->SUBJECT_NAME); ?></td>
                        <td><?php echo htmlspecialchars($s->UNITS); ?></td>
                        <td>&#8369;<?php echo number_format($s->PRICE_PER_UNIT, 2); ?></td>
                        <td>&#8369;<?php echo number_format($s->UNITS * $s->PRICE_PER_UNIT, 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                    <tr>
                      <td colspan="2" class="text-right"><strong>Total</strong></td>
                      <td><strong><?php echo $gUnits; ?></strong></td>
                      <td></td>
                      <td><strong>&#8369;<?php echo number_format($gAmount, 2); ?></strong></td>
                    </tr>
                  </tbody>
                </table>
                </div>
              <?php endforeach; ?>

            <?php endif; ?>

          </div>
        </div>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

  <?php endif; ?>

  </div>
</section>