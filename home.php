<?php
// Solitario Solutions

global $mydb;

/* Server fallback: Philippine time. (Best set once in include/initialize.php.) */
date_default_timezone_set('Asia/Manila');

function home_safe_count($table) {
  global $mydb;
  if (!$mydb->tableExists($table)) { return 0; }
  $mydb->setQuery("SELECT * FROM `".$table."`");
  return $mydb->num_rows();
}

$totalAlumni  = home_safe_count('alumni_details');
$totalStudent = home_safe_count('tblstudent');
$totalCourse  = home_safe_count('tblcourses');
$totalSection = home_safe_count('tblsections');

// Most recently registered enrollments, for a quick "what's happening"
// glance - mirrors the doctor dashboard's Recent Visits table.
$recentEnrollments = array();
if ($mydb->tableExists('tblenrollment')) {
  $mydb->setQuery("SELECT e.ENROLLMENT_ID, e.STATUS, e.DATE_RESERVED, e.YEAR_LEVEL, e.SEMESTER,
      s.IDNO, s.LNAME, s.FNAME, s.MNAME,
      c.COURSE_CODE
    FROM `tblenrollment` e
    LEFT JOIN `tblstudent` s ON s.S_ID = e.S_ID
    LEFT JOIN `tblcourses` c ON c.COURSE_ID = e.COURSE_ID
    ORDER BY e.ENROLLMENT_ID DESC
    LIMIT 5");
  $recentEnrollments = $mydb->loadResultList();
}

$adminName = isset($_SESSION['DISPLAYNAME']) ? $_SESSION['DISPLAYNAME'] : 'there';

$hour = (int)date('G');
$greeting = ($hour < 12) ? 'Good morning' : (($hour < 17) ? 'Good afternoon' : 'Good evening');

/* Stat cards: label, value, icon, colour class, link */
$stats = array(
  array('Alumni Count',   $totalAlumni,  'fa-user-graduate',       'hm-c1', WEB_ROOT.'module/generic/index.php?t=alumni_details'),
  array('Total Students', $totalStudent, 'fa-users',               'hm-c2', WEB_ROOT.'module/student/'),
  array('Total Courses',  $totalCourse,  'fa-book',                'hm-c3', WEB_ROOT.'module/course/'),
  array('Total Sections', $totalSection, 'fa-chalkboard-teacher',  'hm-c4', WEB_ROOT.'module/generic/index.php?t=tblsections')
);

/* Enrollment status -> pill colour class */
$statusClass = array(
  'Registered' => 'st-grey', 'Assigned' => 'st-blue', 'Sectioned' => 'st-indigo',
  'Paid' => 'st-purple', 'Enrolled' => 'st-green', 'Dropped' => 'st-red'
);
?>
<style>
.hm-hero{position:relative;overflow:hidden;border-radius:18px;padding:26px 30px;margin-bottom:22px;color:#fff;
  background:linear-gradient(120deg,#5c0000,#8b0000 50%,#c0392b);box-shadow:0 10px 30px rgba(139,0,0,.35);animation:solFade .4s ease}
.hm-hero:before,.hm-hero:after{content:"";position:absolute;border-radius:50%;background:rgba(255,255,255,.08)}
.hm-hero:before{width:220px;height:220px;right:-50px;top:-90px}
.hm-hero:after{width:140px;height:140px;right:120px;bottom:-80px}
.hm-hero h2{font-weight:800;margin:0 0 4px;font-size:1.7rem;position:relative}
.hm-hero p{margin:0;opacity:.88;position:relative}
.hm-hero .hm-date{display:inline-block;margin-top:12px;padding:4px 14px;border-radius:30px;background:rgba(255,255,255,.18);font-size:.82rem;position:relative}
.hm-hero .hm-ico{position:absolute;right:34px;top:50%;transform:translateY(-50%);font-size:4.5rem;opacity:.22}

.hm-stat{position:relative;background:#fff;border-radius:16px;padding:20px 20px 14px;margin-bottom:22px;overflow:hidden;
  box-shadow:0 6px 22px rgba(0,0,0,.12);transition:transform .2s,box-shadow .2s;border-top:4px solid var(--c)}
.hm-stat:hover{transform:translateY(-5px);box-shadow:0 14px 32px rgba(0,0,0,.22)}
.hm-stat .hm-icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;
  font-size:1.5rem;color:#fff;background:linear-gradient(135deg,var(--c),var(--c2));box-shadow:0 6px 16px rgba(0,0,0,.25);margin-bottom:12px}
.hm-stat .hm-num{font-size:2.3rem;font-weight:800;line-height:1}
.hm-stat .hm-label{text-transform:uppercase;letter-spacing:.8px;font-size:.74rem;font-weight:700;opacity:.65;margin-top:4px}
.hm-stat a{display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:10px;border-top:1px dashed rgba(128,128,128,.3);
  font-size:.85rem;font-weight:700;color:var(--c)}
.hm-stat a i{transition:transform .2s}
.hm-stat a:hover i{transform:translateX(5px)}
.hm-stat .hm-bg{position:absolute;right:-12px;top:-6px;font-size:6rem;color:var(--c);opacity:.07}
.hm-c1{--c:#0f8fb5;--c2:#3ec6e8}
.hm-c2{--c:#1e8e4e;--c2:#2ecc71}
.hm-c3{--c:#d4830d;--c2:#f5b041}
.hm-c4{--c:#a93226;--c2:#e74c3c}
body.dark-mode .hm-stat{background:#23262b}

.hm-quick .btn{border-radius:30px;padding:8px 20px;margin:0 8px 8px 0;border:0;box-shadow:0 3px 10px rgba(0,0,0,.2)}
.hm-panel{border:0;border-radius:16px;overflow:hidden;box-shadow:0 6px 22px rgba(0,0,0,.12)}
.hm-panel>.card-header{background:linear-gradient(120deg,#8b0000,#c0392b);color:#fff;border:0;padding:16px 20px;display:flex;align-items:center;justify-content:space-between}
.hm-panel>.card-header .card-title{font-weight:700;margin:0;color:#fff}
.hm-panel>.card-header .card-title i{margin-right:8px;opacity:.9}
.hm-panel>.card-header a{color:#fff;font-size:.85rem;font-weight:600;background:rgba(255,255,255,.18);padding:4px 14px;border-radius:30px}
.hm-panel>.card-header a:hover{background:rgba(255,255,255,.3);text-decoration:none}
.hm-panel td,.hm-panel th{vertical-align:middle}
.hm-avatar{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;margin-right:10px;
  background:linear-gradient(135deg,#8b0000,#c0392b);color:#fff;font-weight:700;font-size:.8rem}
.hm-pill{display:inline-block;padding:3px 12px;border-radius:30px;font-size:.74rem;font-weight:700;letter-spacing:.3px;color:#fff}
.st-grey{background:#6c7a89}.st-blue{background:#2f80ed}.st-indigo{background:#4b5bd6}
.st-purple{background:#8e44ad}.st-green{background:#1e9e5a}.st-red{background:#d63c3c}
.hm-view{border-radius:30px;padding:2px 14px;font-size:.78rem;font-weight:700;border:1px solid #c0392b;color:#c0392b}
.hm-view:hover{background:#c0392b;color:#fff;text-decoration:none}
.hm-empty{text-align:center;padding:34px;opacity:.7}
.hm-empty i{display:block;font-size:2.4rem;color:#c0392b;margin-bottom:8px}
body.dark-mode .hm-panel>.card-header{background:linear-gradient(120deg,#8b0000,#c0392b)!important}
body.dark-mode .hm-view{color:#ff8a80;border-color:#ff8a80}
</style>

<section class="content">

  <div class="container-fluid">

    <!-- WELCOME BANNER -->
    <div class="hm-hero">
      <i class="fas fa-university hm-ico"></i>
      <h2><span id="hmGreeting"><?php echo $greeting; ?></span>, <?php echo htmlspecialchars($adminName); ?>!</h2>
      <p>Here's what's happening across the system.</p>
      <span class="hm-date"><i class="far fa-calendar-alt"></i> <span id="hmDate"><?php echo date('l, F j, Y'); ?></span> &nbsp;<i class="far fa-clock"></i> <span id="hmClock"><?php echo date('g:i:s A'); ?></span></span>
    </div>

    <!-- STAT CARDS -->
    <div class="row">
      <?php foreach ($stats as $s): ?>
        <div class="col-lg-3 col-sm-6 col-12">
          <div class="hm-stat <?php echo $s[3]; ?>">
            <i class="fas <?php echo $s[2]; ?> hm-bg"></i>
            <div class="hm-icon"><i class="fas <?php echo $s[2]; ?>"></i></div>
            <div class="hm-num"><?php echo (int)$s[1]; ?></div>
            <div class="hm-label"><?php echo $s[0]; ?></div>
            <a href="<?php echo $s[4]; ?>">More info <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="hm-quick mb-3">
      <a href="<?php echo WEB_ROOT; ?>module/student/" class="btn btn-primary"><i class="fas fa-user-plus"></i> Students</a>
      <a href="<?php echo WEB_ROOT; ?>module/enrollment/index.php" class="btn btn-primary"><i class="fas fa-clipboard-list"></i> Enrollment</a>
      <a href="<?php echo WEB_ROOT; ?>module/setschedule/" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Set Schedule</a>
    </div>

    <!-- RECENT ENROLLMENTS -->
    <div class="card hm-panel">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clipboard-list"></i>Recent Enrollments</h3>
        <a href="<?php echo WEB_ROOT; ?>module/enrollment/index.php">View all</a>
      </div>
      <div class="card-body p-0">
        <?php if (count($recentEnrollments) < 1): ?>
          <div class="hm-empty"><i class="fas fa-inbox"></i>No enrollment records yet.</div>
        <?php else: ?>
          <div class="table-responsive">
          <table class="table table-hover mb-0">
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
                $sc = isset($statusClass[$e->STATUS]) ? $statusClass[$e->STATUS] : 'st-grey';
                $fullName = trim($e->LNAME.', '.$e->FNAME.' '.$e->MNAME);
                $initial  = strtoupper(substr(trim((string)$e->LNAME) !== '' ? trim($e->LNAME) : '?', 0, 1));
            ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($e->IDNO); ?></strong></td>
                <td><span class="hm-avatar"><?php echo htmlspecialchars($initial); ?></span><?php echo htmlspecialchars($fullName); ?></td>
                <td><?php echo htmlspecialchars($e->COURSE_CODE); ?></td>
                <td><?php echo htmlspecialchars($e->YEAR_LEVEL); ?></td>
                <td><?php echo htmlspecialchars($e->SEMESTER); ?></td>
                <td><?php echo htmlspecialchars($e->DATE_RESERVED); ?></td>
                <td><span class="hm-pill <?php echo $sc; ?>"><?php echo htmlspecialchars($e->STATUS); ?></span></td>
                <td><a class="hm-view" href="<?php echo WEB_ROOT; ?>module/enrollment/index.php">View</a></td>
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

<script>
/* Live greeting, date and clock - uses the visitor's own device time,
   so it is always correct no matter what timezone the server is in. */
(function () {
  var g = document.getElementById('hmGreeting');
  var d = document.getElementById('hmDate');
  var c = document.getElementById('hmClock');
  if (!g || !d || !c) { return; }

  function tick() {
    var now = new Date();
    var h = now.getHours();

    g.textContent = h < 12 ? 'Good morning' : (h < 17 ? 'Good afternoon' : 'Good evening');
    d.textContent = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    c.textContent = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit' });
  }

  tick();
  setInterval(tick, 1000);
})();
</script>