<?php

//Solitario Solution - Registrar Dashboard

global $mydb;

function registrar_safe_count($table) {
  global $mydb;
  if (!$mydb->tableExists($table)) { return 0; }
  $mydb->setQuery("SELECT * FROM `".$table."`");
  return $mydb->num_rows();
}

$totalAlumni  = registrar_safe_count('alumni_details');
$totalStudent = registrar_safe_count('tblstudent');
$totalCourse  = registrar_safe_count('tblcourses');
$totalSection = registrar_safe_count('tblsections');

// Recent enrollment activity, most recent first.
$recentEnrollments = array();
if ($mydb->tableExists('tblenrollment')) {
  $mydb->setQuery("SELECT e.ENROLLMENT_ID, e.YEAR_LEVEL, e.SEMESTER, e.DATE_RESERVED, e.STATUS,
      s.IDNO, s.LNAME, s.FNAME, s.MNAME,
      c.COURSE_CODE
    FROM `tblenrollment` e
    LEFT JOIN `tblstudent` s ON s.S_ID = e.S_ID
    LEFT JOIN `tblcourses` c ON c.COURSE_ID = e.COURSE_ID
    ORDER BY e.ENROLLMENT_ID DESC
    LIMIT 5");
  $recentEnrollments = $mydb->loadResultList();
}

$registrarName = isset($_SESSION['DISPLAYNAME']) ? $_SESSION['DISPLAYNAME'] : 'there';

$statCards = array(
  array('n' => $totalAlumni,  'label' => 'Alumni Count',    'icon' => 'fa-user-graduate', 'url' => WEB_ROOT.'module/generic/index.php?t=alumni_details', 'g' => 'linear-gradient(120deg,#8b0000,#c0392b)'),
  array('n' => $totalStudent, 'label' => 'Total Students',  'icon' => 'fa-users',         'url' => WEB_ROOT.'module/student/',                          'g' => 'linear-gradient(120deg,#1e8e4e,#28c76f)'),
  array('n' => $totalCourse,  'label' => 'Total Courses',   'icon' => 'fa-book',          'url' => WEB_ROOT.'module/course/',                           'g' => 'linear-gradient(120deg,#d68910,#f5b041)'),
  array('n' => $totalSection, 'label' => 'Total Sections',  'icon' => 'fa-chalkboard',    'url' => WEB_ROOT.'module/generic/index.php?t=tblsections',   'g' => 'linear-gradient(120deg,#5b2a86,#9b59b6)'),
);
?>
<style>
:root{
    --ss-a:#8b0000; --ss-b:#c0392b; --ss-soft:rgba(192,57,43,.12);
    --ss-line:rgba(128,128,128,.25); --ss-muted:rgba(128,128,128,.9);
}
/* welcome hero */
.ss-welcome{display:flex;align-items:center;padding:20px 24px;margin-bottom:22px;border-radius:16px;color:#fff;
    background:linear-gradient(135deg,var(--ss-a),var(--ss-b));box-shadow:0 6px 22px rgba(0,0,0,.18)}
.ss-welcome .ss-badge{width:54px;height:54px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;margin-right:16px;font-size:1.4rem;flex:0 0 54px}
.ss-welcome h4{margin:0;font-weight:700}
.ss-welcome span{opacity:.88}

/* stat cards */
.ss-stat-card{position:relative;display:block;overflow:hidden;border-radius:16px;color:#fff!important;padding:20px 22px 44px;margin-bottom:22px;
    box-shadow:0 6px 22px rgba(0,0,0,.22);transition:transform .15s,box-shadow .15s;text-decoration:none!important}
.ss-stat-card:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(0,0,0,.3)}
.ss-stat-card h3{font-size:2.4rem;font-weight:800;margin:0}
.ss-stat-card p{margin:0;text-transform:uppercase;letter-spacing:.8px;font-size:.74rem;font-weight:700;opacity:.9}
.ss-stat-card .ico{position:absolute;right:18px;top:16px;font-size:3.2rem;opacity:.22}
.ss-stat-card .more{position:absolute;left:0;right:0;bottom:0;padding:8px 22px;background:rgba(0,0,0,.18);font-size:.8rem;font-weight:600;letter-spacing:.3px}
.ss-stat-card .more i{margin-left:6px}

/* recent enrollments card */
.ss-card{border:0;border-radius:14px;overflow:hidden;box-shadow:0 6px 22px rgba(0,0,0,.18)}
.ss-card>.card-header{background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff;border:0;padding:16px 20px}
.ss-card>.card-header .card-title{font-weight:600;letter-spacing:.3px;margin:0}
.ss-card>.card-header i{margin-right:8px;opacity:.9}
.ss-card .card-body{padding:20px}
.ss-table thead th{background:var(--ss-soft);border-bottom:2px solid var(--ss-b)!important;border-top:0;font-size:.78rem;letter-spacing:.6px;text-transform:uppercase;white-space:nowrap}
.ss-table tbody tr{transition:background .15s}
.ss-table tbody tr:hover{background:var(--ss-soft)!important}
.ss-table td{vertical-align:middle}
.ss-table .badge{border-radius:30px;padding:5px 13px;font-size:.74rem;font-weight:700;letter-spacing:.3px}
.ss-btn{border-radius:30px!important;padding:5px 16px;font-weight:600;border:0!important;box-shadow:0 3px 10px rgba(0,0,0,.25);
    background:linear-gradient(120deg,var(--ss-a),var(--ss-b));color:#fff!important;font-size:.8rem;text-decoration:none!important;display:inline-block}
.ss-empty{text-align:center;padding:26px;color:var(--ss-muted)}
.ss-empty i{font-size:2rem;display:block;margin-bottom:8px;color:var(--ss-b)}
</style>

<section class="content">

  <div class="container-fluid">

    <div class="ss-welcome">
      <span class="ss-badge"><i class="fas fa-user-tie"></i></span>
      <div>
        <h4>Welcome back, <?php echo htmlspecialchars($registrarName); ?>!</h4>
        <span>Here's what's happening across enrollment.</span>
      </div>
    </div>

    <div class="row">
      <?php foreach ($statCards as $c): ?>
      <div class="col-lg-3 col-6">
        <a href="<?php echo $c['url']; ?>" class="ss-stat-card" style="background:<?php echo $c['g']; ?>">
          <h3><?php echo $c['n']; ?></h3>
          <p><?php echo $c['label']; ?></p>
          <i class="fas <?php echo $c['icon']; ?> ico"></i>
          <div class="more">More info <i class="fas fa-arrow-circle-right"></i></div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="card ss-card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Recent Enrollments</h3>
      </div>
      <div class="card-body">
        <?php if (count($recentEnrollments) < 1): ?>
          <div class="ss-empty"><i class="fas fa-inbox"></i>No enrollment records yet.</div>
        <?php else: ?>
          <div class="table-responsive">
          <table class="table ss-table mb-0">
            <thead>
              <tr>
                <th>ID No.</th>
                <th>Student</th>
                <th>Course</th>
                <th>Year Level</th>
                <th>Semester</th>
                <th>Date Reserved</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($recentEnrollments as $e):
              $statusClass = 'secondary';
              if ($e->STATUS == 'Enrolled')  { $statusClass = 'success'; }
              if ($e->STATUS == 'Sectioned') { $statusClass = 'primary'; }
              if ($e->STATUS == 'Paid')      { $statusClass = 'warning'; }
              if ($e->STATUS == 'Dropped')   { $statusClass = 'danger'; }
            ?>
              <tr>
                <td><?php echo htmlspecialchars($e->IDNO); ?></td>
                <td><?php echo htmlspecialchars(trim($e->LNAME.', '.$e->FNAME.' '.$e->MNAME)); ?></td>
                <td><?php echo htmlspecialchars($e->COURSE_CODE); ?></td>
                <td><?php echo htmlspecialchars($e->YEAR_LEVEL); ?></td>
                <td><?php echo htmlspecialchars($e->SEMESTER); ?></td>
                <td><?php echo htmlspecialchars($e->DATE_RESERVED); ?></td>
                <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($e->STATUS); ?></span></td>
                <td><a class="ss-btn" href="<?php echo WEB_ROOT; ?>module/enrollment/index.php"><i class="fas fa-eye"></i> View</a></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

</section>