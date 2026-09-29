<?php

require_once("../../include/initialize.php");
require_once(dirname(__FILE__) . '/style.php');

global $mydb;


/* =========================================================
   GET SUBJECT ID
   ========================================================= */

$SUBJECT_ID = isset($_GET['id']) ? intval($_GET['id']) : 0;


/* =========================================================
   GET SUBJECT INFORMATION
   ========================================================= */

$mydb->setQuery("

    SELECT
        s.*,
        c.COURSE_CODE,
        c.COURSE_NAME,
        c.COURSE_DESC,
        c.STATUS AS COURSE_STATUS

    FROM tblsubjects s

    LEFT JOIN tblcourses c
        ON c.COURSE_ID = s.COURSE_ID

    WHERE s.SUBJECT_ID = '".$SUBJECT_ID."'

    LIMIT 1

");

$subjectResult = $mydb->loadSingleResult();


/* =========================================================
   IF SUBJECT DOES NOT EXIST
   ========================================================= */

if (!$subjectResult) {

    message("Subject not found!", "error");

    redirect("index.php");

    exit;
}


/* =========================================================
   SUBJECT VALUES
   ========================================================= */

$SUBJECT_CODE = $subjectResult->SUBJECT_CODE;

$SUBJECT_NAME = $subjectResult->SUBJECT_NAME;

$UNITS = intval($subjectResult->UNITS);

$PRICE_PER_UNIT = floatval($subjectResult->PRICE_PER_UNIT);

$TOTAL_AMOUNT = $UNITS * $PRICE_PER_UNIT;

$COURSE_CODE = isset($subjectResult->COURSE_CODE)
                ? $subjectResult->COURSE_CODE
                : '';

$COURSE_NAME = isset($subjectResult->COURSE_NAME)
                ? $subjectResult->COURSE_NAME
                : '';

$YEAR_LEVEL = $subjectResult->YEAR_LEVEL;

$SEMESTER = $subjectResult->SEMESTER;


/* One icon tile in the info grid. */
function sj_tile($label, $value, $icon, $full = false)
{
    echo '<div class="ss-tile' . ($full ? ' full' : '') . '"><div><i class="fa ' . $icon . '"></i>'
        . '<div><span>' . $label . '</span><b>' . $value . '</b></div></div></div>';
}

$title = "Subject View";
$courseActive = (isset($subjectResult->COURSE_STATUS) && $subjectResult->COURSE_STATUS == 'Active');

?>

<section class="content">
  <div class="container-fluid">
    <div class="row">

      <!-- ================= LEFT ================= -->
      <div class="col-md-4">

        <div class="card ss-hero">
          <div class="ss-cover">
            <div style="width:84px;height:84px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:2.4rem;">
              <i class="fa fa-book"></i>
            </div>
            <h3><?php echo htmlspecialchars($SUBJECT_CODE); ?></h3>
            <small><?php echo htmlspecialchars($SUBJECT_NAME); ?></small>
          </div>
          <div class="ss-body">
            <div class="ss-stat"><i class="fa fa-star"></i><div><span>Units</span><b><?php echo $UNITS; ?></b></div></div>
            <div class="ss-stat"><i class="fa fa-tag"></i><div><span>Price Per Unit</span><b>&#8369; <?php echo number_format($PRICE_PER_UNIT, 2); ?></b></div></div>
            <div class="ss-stat"><i class="fa fa-money-bill"></i><div><span>Total Amount</span><b>&#8369; <?php echo number_format($TOTAL_AMOUNT, 2); ?></b></div></div>
            <div class="ss-stat"><i class="fa fa-level-up"></i><div><span>Year Level</span><b><?php echo htmlspecialchars($YEAR_LEVEL); ?></b></div></div>
            <div class="ss-stat"><i class="fa fa-flag"></i><div><span>Semester</span><b><?php echo htmlspecialchars($SEMESTER); ?></b></div></div>

            <a href="index.php" class="btn ss-btn main btn-block mt-3"><i class="fa fa-arrow-left"></i> Back to List</a>
            <a href="print.php?id=<?php echo $SUBJECT_ID; ?>" target="_blank" class="btn ss-btn btn-success btn-block mt-2"><i class="fa fa-print"></i> Print</a>
          </div>
        </div>

      </div>

      <!-- ================= RIGHT ================= -->
      <div class="col-md-8">

        <div class="card ss-panel">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-book mr-1"></i> Subject Details</h3>
          </div>
          <div class="card-body">
            <div class="ss-info">
              <?php
              sj_tile('Subject Code', htmlspecialchars($SUBJECT_CODE), 'fa-hashtag');
              sj_tile('Units', $UNITS, 'fa-star');
              sj_tile('Subject Name', htmlspecialchars($SUBJECT_NAME), 'fa-book', true);
              sj_tile('Price Per Unit', '&#8369; ' . number_format($PRICE_PER_UNIT, 2), 'fa-tag');
              sj_tile('Total Amount', '&#8369; ' . number_format($TOTAL_AMOUNT, 2), 'fa-money-bill');
              sj_tile('Year Level', htmlspecialchars($YEAR_LEVEL), 'fa-level-up');
              sj_tile('Semester', htmlspecialchars($SEMESTER), 'fa-flag');
              ?>
            </div>
          </div>
        </div>

        <div class="card ss-panel">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-graduation-cap mr-1"></i> Course Information</h3>
          </div>
          <div class="card-body">
            <div class="ss-info">
              <?php
              sj_tile('Course Code', htmlspecialchars($COURSE_CODE), 'fa-hashtag');
              sj_tile('Course Status',
                  '<span class="ss-pill" style="' . ($courseActive ? '' : 'background:rgba(128,128,128,.2);color:inherit;') . '">'
                  . htmlspecialchars((string)$subjectResult->COURSE_STATUS) . '</span>', 'fa-toggle-on');
              sj_tile('Course Name', htmlspecialchars($COURSE_NAME), 'fa-graduation-cap', true);
              sj_tile('Course Description',
                  !empty($subjectResult->COURSE_DESC)
                      ? nl2br(htmlspecialchars($subjectResult->COURSE_DESC))
                      : 'No description available',
                  'fa-align-left', true);
              ?>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</section>