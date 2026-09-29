<?php require_once(dirname(__FILE__) . '/style.php');

function dr_tile($label, $value, $icon, $full = false)
{
  echo '<div class="ss-tile' . ($full ? ' full' : '') . '"><div><i class="fa ' . $icon . '"></i>'
    . '<div><span>' . htmlspecialchars($label) . '</span><b>' . $value . '</b></div></div></div>';
}

function dr_val($v)
{
  $v = trim((string)$v);
  return $v !== '' ? htmlspecialchars($v) : '<span class="text-muted">-</span>';
}
?>
<section class="content">
  <div class="container-fluid">

  <?php if (!$doc): ?>
    <div class="alert alert-warning">
      Doctor profile not found. Please go back to the <a href="<?php echo WEB_ROOT; ?>module/doctor/">doctor list</a>.
    </div>

  <?php else: ?>

    <?php
    $isActive = ($doc->STATUS == 'Active');
    $nameText = trim((string)$doc->FULLNAME);
    $initial  = strtoupper(substr($nameText !== '' ? $nameText : '?', 0, 1));
    $schedule = trim($doc->SCHEDULE_DAYS.' '.$doc->SCHEDULE_TIME);
    ?>

    <div class="row">

      <!-- ================= LEFT ================= -->
      <div class="col-md-4">
        <div class="card ss-hero">
          <div class="ss-cover">
            <div style="width:96px;height:96px;border-radius:50%;background:rgba(255,255,255,.2);border:4px solid rgba(255,255,255,.85);display:inline-flex;align-items:center;justify-content:center;font-size:2.6rem;font-weight:800;box-shadow:0 6px 18px rgba(0,0,0,.35);">
              <?php echo htmlspecialchars($initial); ?>
            </div>
            <h3><?php echo htmlspecialchars($nameText); ?></h3>
            <small><?php echo $doc->SPECIALIZATION ? htmlspecialchars($doc->SPECIALIZATION) : 'Doctor'; ?></small>
          </div>
          <div class="ss-body">
            <div class="ss-stat"><i class="fa fa-toggle-on"></i><div><span>Status</span><b><span class="ss-pill" style="<?php echo $isActive ? '' : 'background:rgba(128,128,128,.2);color:inherit;'; ?>"><?php echo $isActive ? 'Active' : 'Inactive'; ?></span></b></div></div>
            <div class="ss-stat"><i class="fa fa-user-circle"></i><div><span>Login Username</span><b><?php echo dr_val($doc->USERNAME); ?></b></div></div>
            <div class="ss-stat"><i class="fa fa-stethoscope"></i><div><span>Total Visits Logged</span><b><?php echo count($visits); ?></b></div></div>

            <a href="<?php echo WEB_ROOT; ?>module/doctor/" class="btn ss-btn main btn-block mt-3">
              <i class="fa fa-arrow-left"></i> Back to List
            </a>
          </div>
        </div>
      </div>

      <!-- ================= RIGHT ================= -->
      <div class="col-md-8">

        <div class="card ss-panel">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-user-md mr-1"></i> Doctor Information</h3>
          </div>
          <div class="card-body">
            <div class="ss-info">
              <?php
              dr_tile('Full Name', dr_val($doc->FULLNAME), 'fa-user-md');
              dr_tile('Specialization', dr_val($doc->SPECIALIZATION), 'fa-stethoscope');
              dr_tile('PRC License No.', dr_val($doc->LICENSE_NO), 'fa-id-card');
              dr_tile('Contact No.', dr_val($doc->CONTACT_NO), 'fa-phone');
              dr_tile('Schedule', dr_val($schedule), 'fa-calendar', true);
              ?>
            </div>
          </div>
        </div>

        <div class="card ss-panel">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-notes-medical mr-1"></i> Consultation Activity</h3>
          </div>
          <div class="card-body p-0">
            <?php if (count($visits) < 1): ?>
              <div class="ss-empty"><i class="fa fa-inbox"></i>This doctor hasn't logged any consultations yet.</div>
            <?php else: ?>
              <div class="table-responsive">
              <table class="table table-sm mb-0 ss-mini">
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
                  <?php foreach ($visits as $v) : ?>
                  <tr>
                    <td><?php echo htmlspecialchars($v->VISIT_DATE); ?></td>
                    <td><strong><?php echo htmlspecialchars(trim($v->FNAME.' '.$v->LNAME)); ?></strong></td>
                    <td><?php echo htmlspecialchars($v->CHIEF_COMPLAINT); ?></td>
                    <td>
                      <?php if ($v->DIAGNOSIS): ?>
                        <?php echo htmlspecialchars($v->DIAGNOSIS); ?>
                      <?php else: ?>
                        <span class="badge badge-warning">Pending</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-right"><a class="btn btn-info btn-xs" href="<?php echo WEB_ROOT; ?>module/patient/index.php?view=view&id=<?php echo (int)$v->PATIENT_ID; ?>"><i class="fa fa-eye"></i> Patient</a></td>
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