<?php

require_once(dirname(__FILE__) . '/style.php');

// Set the student and enrollment variables to empty first.
$student = null;
$enroll  = null;

// Check if a student ID was passed through the URL.
if (isset($_GET['id']) && $_GET['id'] != '') {

    // Get the student's information from the database using the ID.
    $mydb->setQuery("SELECT * FROM `tblstudent` WHERE `S_ID`='".(int)$_GET['id']."' LIMIT 1");
    $student = $mydb->loadSingleResult();

    // Get the student's latest enrollment, including course, section, and school year.
    $mydb->setQuery("SELECT e.*, c.COURSE_CODE, c.COURSE_NAME, s.SECTION_NAME, sy.SCHOOL_YEAR
                      FROM `tblenrollment` e
                      LEFT JOIN `tblcourses` c ON c.COURSE_ID = e.COURSE_ID
                      LEFT JOIN `tblsections` s ON s.SECTION_ID = e.SECTION_ID
                      LEFT JOIN `tblschoolyear` sy ON sy.SY_ID = e.SY_ID
                      WHERE e.S_ID='".(int)$_GET['id']."'
                      ORDER BY e.ENROLLMENT_ID DESC LIMIT 1");
    $enroll = $mydb->loadSingleResult();
}

/* Escaped value with a fallback label. */
function st_val($obj, $field, $fallback = '-')
{
    if (!is_object($obj) || !isset($obj->$field)) return $fallback;
    $v = trim((string)$obj->$field);
    if ($v === '' || $v === '0000-00-00') return $fallback;
    return htmlspecialchars($v);
}

/* One icon tile in the info grid. */
function st_tile($label, $value, $icon, $full = false)
{
    echo '<div class="ss-tile' . ($full ? ' full' : '') . '"><div><i class="fa ' . $icon . '"></i>'
        . '<div><span>' . $label . '</span><b>' . $value . '</b></div></div></div>';
}

$noPhoto = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='150' height='150'><rect width='100%25' height='100%25' fill='%23e0e0e0'/><text x='50%25' y='50%25' font-size='16' fill='%23888' text-anchor='middle' dy='.3em'>No Photo</text></svg>";

?>

<section class="content">
  <div class="container-fluid">

  <?php if (!$student): ?>

    <div class="alert alert-warning">
      No student was selected. Please go back to the <a href="<?php echo WEB_ROOT; ?>module/student/">student list</a> and click the view button of a student.
    </div>

  <?php else: ?>

    <?php
    $fullName = trim($student->FNAME.' '.$student->MNAME.' '.$student->LNAME);
    $photoSrc = !empty($student->photo) ? WEB_ROOT.'module/student/'.$student->photo : $noPhoto;
    ?>

    <div class="row">

      <!-- ================= LEFT ================= -->
      <div class="col-md-4 col-lg-3">

        <div class="card ss-hero">
          <div class="ss-cover">
            <img src="<?php echo htmlspecialchars($photoSrc, ENT_QUOTES); ?>"
                 onerror="this.onerror=null;this.src=this.getAttribute('data-fallback');"
                 data-fallback="<?php echo htmlspecialchars($noPhoto, ENT_QUOTES); ?>"
                 alt="Student photo">
            <h3><?php echo htmlspecialchars($fullName); ?></h3>
            <small>Student ID: <?php echo st_val($student, 'IDNO'); ?></small>
          </div>

          <div class="ss-body">
            <div class="ss-stat"><i class="fa fa-venus-mars"></i><div><span>Gender</span><b><?php echo st_val($student, 'SEX'); ?></b></div></div>
            <div class="ss-stat"><i class="fa fa-calendar"></i><div><span>Birthday</span><b><?php echo st_val($student, 'BDAY'); ?></b></div></div>
            <div class="ss-stat"><i class="fa fa-info-circle"></i><div><span>Status</span><b><?php echo st_val($student, 'STATUS'); ?></b></div></div>

            <a href="<?php echo WEB_ROOT; ?>module/student/" class="btn ss-btn main btn-block mt-3">
              <i class="fa fa-arrow-left"></i> Back to List
            </a>
          </div>
        </div>

        <div class="card ss-panel">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-graduation-cap mr-1"></i> Current Enrollment</h3>
          </div>
          <div class="card-body">

            <?php if ($enroll): ?>

              <div class="ss-stat"><i class="fa fa-book"></i><div><span>Course</span><b><?php echo htmlspecialchars($enroll->COURSE_CODE.' - '.$enroll->COURSE_NAME); ?></b></div></div>
              <div class="ss-stat"><i class="fa fa-users"></i><div><span>Section</span><b><?php echo st_val($enroll, 'SECTION_NAME', 'Not yet sectioned'); ?></b></div></div>
              <div class="ss-stat"><i class="fa fa-calendar-o"></i><div><span>School Year</span><b><?php echo st_val($enroll, 'SCHOOL_YEAR'); ?></b></div></div>
              <div class="ss-stat"><i class="fa fa-flag"></i><div><span>Enrollment Status</span><b><span class="ss-pill"><?php echo st_val($enroll, 'STATUS'); ?></span></b></div></div>

            <?php else: ?>

              <div class="ss-empty">
                <i class="fa fa-inbox"></i>
                This student is not yet enrolled in any course/section.
              </div>

            <?php endif; ?>

          </div>
        </div>

      </div>

      <!-- ================= RIGHT ================= -->
      <div class="col-md-8 col-lg-9">
        <div class="card ss-panel">

          <div class="card-header p-2">
            <ul class="nav nav-pills ss-tabs">
              <li class="nav-item"><a class="nav-link active" href="#profile" data-toggle="tab"><i class="fa fa-user"></i> Profile Info</a></li>
              <li class="nav-item"><a class="nav-link" href="#contact" data-toggle="tab"><i class="fa fa-phone"></i> Contact Info</a></li>
            </ul>
          </div>

          <div class="card-body">
            <div class="tab-content">

              <div class="active tab-pane" id="profile">
                <div class="ss-info">
                  <?php
                  st_tile('Student ID Number', st_val($student, 'IDNO'), 'fa-id-card');
                  st_tile('Full Name', htmlspecialchars($fullName), 'fa-user');
                  st_tile('Gender', st_val($student, 'SEX'), 'fa-venus-mars');
                  st_tile('Birthday', st_val($student, 'BDAY'), 'fa-calendar');
                  st_tile('Birth Place', st_val($student, 'BPLACE'), 'fa-map-marker');
                  st_tile('Age', st_val($student, 'AGE'), 'fa-hashtag');
                  st_tile('Nationality', st_val($student, 'NATIONALITY'), 'fa-flag');
                  st_tile('Religion', st_val($student, 'RELIGION'), 'fa-star');
                  st_tile('Status', st_val($student, 'STATUS'), 'fa-info-circle');
                  ?>
                </div>
              </div>

              <div class="tab-pane" id="contact">
                <div class="ss-info">
                  <?php
                  st_tile('Contact Number', st_val($student, 'CONTACT_NO'), 'fa-phone');
                  st_tile('Email', st_val($student, 'EMAIL'), 'fa-envelope');
                  st_tile('Home Address', st_val($student, 'HOME_ADD'), 'fa-home', true);
                  ?>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>

    </div>

  <?php endif; ?>

  </div>
</section>