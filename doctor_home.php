<?php


global $mydb;

function clinic_safe_count($table, $where = '') {
  global $mydb;
  if (!$mydb->tableExists($table)) { return 0; }
  $mydb->setQuery("SELECT * FROM `".$table."` ".$where);
  return $mydb->num_rows();
}

$totalPatients = clinic_safe_count('tblpatients');
$visitsToday   = clinic_safe_count('tblvisits', "WHERE `VISIT_DATE` = CURDATE()");
$totalVisits   = clinic_safe_count('tblvisits');
$activeDoctors = clinic_safe_count('tblusers', "WHERE `TYPE` = 'Doctor' AND `STATUSACTIVE` = 1");

// Recent visits, most recent first.
$recentVisits = array();
if ($mydb->tableExists('tblvisits')) {
  $mydb->setQuery("SELECT v.*, p.FNAME, p.LNAME
    FROM `tblvisits` v
    LEFT JOIN `tblpatients` p ON p.PATIENT_ID = v.PATIENT_ID
    ORDER BY v.VISIT_DATE DESC, v.VISIT_ID DESC
    LIMIT 5");
  $recentVisits = $mydb->loadResultList();
}

$doctorName = isset($_SESSION['DISPLAYNAME']) ? $_SESSION['DISPLAYNAME'] : 'Doctor';

$statCards = array(
  array('n' => $totalPatients, 'label' => 'Total Patients',      'icon' => 'fa-user-injured',      'url' => WEB_ROOT.'module/patient/', 'g' => 'linear-gradient(120deg,#8b0000,#c0392b)'),
  array('n' => $visitsToday,   'label' => 'Visits Today',        'icon' => 'fa-calendar-check',    'url' => WEB_ROOT.'module/patient/', 'g' => 'linear-gradient(120deg,#1e8e4e,#28c76f)'),
  array('n' => $totalVisits,   'label' => 'Total Visit Records', 'icon' => 'fa-notes-medical',     'url' => WEB_ROOT.'module/patient/', 'g' => 'linear-gradient(120deg,#d68910,#f5b041)'),
  array('n' => $activeDoctors, 'label' => 'Active Doctors',      'icon' => 'fa-user-md',           'url' => WEB_ROOT.'module/doctor/',  'g' => 'linear-gradient(120deg,#5b2a86,#9b59b6)'),
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
      <span class="ss-badge"><i class="fas fa-user-md"></i></span>
      <div>
        <h4>Welcome back, Dr. <?php echo htmlspecialchars($doctorName); ?>!</h4>
        <span>Here's today's clinic overview.</span>
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
        <h3 class="card-title"><i class="fas fa-stethoscope"></i> Recent Visits</h3>
      </div>
      <div class="card-body">
        <?php if (count($recentVisits) < 1): ?>
          <div class="ss-empty"><i class="fas fa-inbox"></i>No visits recorded yet.</div>
        <?php else: ?>
          <div class="table-responsive">
          <table class="table ss-table mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Chief Complaint</th>
                <th>Diagnosis</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($recentVisits as $v): ?>
              <tr>
                <td><?php echo htmlspecialchars($v->VISIT_DATE); ?></td>
                <td><?php echo htmlspecialchars(trim($v->LNAME.', '.$v->FNAME)); ?></td>
                <td><?php echo htmlspecialchars($v->CHIEF_COMPLAINT); ?></td>
                <td><?php echo $v->DIAGNOSIS ? htmlspecialchars($v->DIAGNOSIS) : '<span class="badge badge-warning">Pending</span>'; ?></td>
                <td><a class="ss-btn" href="<?php echo WEB_ROOT; ?>module/patient/index.php?view=view&id=<?php echo $v->PATIENT_ID; ?>"><i class="fas fa-eye"></i> View patient</a></td>
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