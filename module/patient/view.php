<?php

require_once(dirname(__FILE__) . '/style.php');

$patient = null;
$visits = array();

if (isset($_GET['id']) && $_GET['id'] != '') {

    $patientModel = new Patient();
    $patient = $patientModel->single_patient((int)$_GET['id']);

    if ($patient) {

        $visitModel = new Visit();
        $visits = $visitModel->visits_for_patient((int)$_GET['id']);
    }
}


function pt_tile($label, $value, $icon, $full = false)
{
    echo '<div class="ss-tile' . ($full ? ' full' : '') . '"><div><i class="fa ' . $icon . '"></i>'
        . '<div><span>' . htmlspecialchars($label) . '</span><b>' . $value . '</b></div></div></div>';
}

?>

<section class="content">

    <div class="container-fluid">


        <?php if (!$patient): ?>


            <div class="alert alert-warning">

                No patient was selected.

                Please go back to the

                <a href="<?php echo WEB_ROOT; ?>module/patient/">

                    patient list

                </a>

                and click the view button of a patient.

            </div>


        <?php else: ?>

            <?php
            $fullName  = trim($patient->FNAME . ' ' . $patient->MNAME . ' ' . $patient->LNAME);
            $bdayText  = !empty($patient->BDAY) ? date("F j, Y", strtotime($patient->BDAY)) : 'Not set';
            $ageText   = ($patient->AGE !== null && $patient->AGE !== '') ? $patient->AGE : 'Not set';
            $statusRaw = (string)$patient->STATUS;
            $badgeClass = ($statusRaw == 'Active') ? 'badge-success' : 'badge-secondary';
            ?>

            <div class="row">


                <!-- ================= LEFT ================= -->

                <div class="col-md-4">

                    <div class="card ss-hero">

                        <div class="ss-cover">
                            <div style="width:84px;height:84px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:2.4rem;">
                                <i class="fa fa-user-injured"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($fullName); ?></h3>
                            <small><?php echo htmlspecialchars($patient->SEX); ?></small>
                        </div>

                        <div class="ss-body">

                            <div class="ss-stat"><i class="fa fa-hashtag"></i><div><span>Patient ID</span><b><?php echo (int)$patient->PATIENT_ID; ?></b></div></div>
                            <div class="ss-stat"><i class="fa fa-toggle-on"></i><div><span>Status</span><b><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($statusRaw); ?></span></b></div></div>
                            <div class="ss-stat"><i class="fa fa-notes-medical"></i><div><span>Total Visits</span><b><?php echo count($visits); ?></b></div></div>

                            <a href="<?php echo WEB_ROOT; ?>module/patient/" class="btn ss-btn main btn-block mt-3">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>

                        </div>

                    </div>

                </div>


                <!-- ================= RIGHT ================= -->

                <div class="col-md-8">

                    <div class="card ss-panel">

                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-id-card mr-1"></i> Patient Information</h3>
                        </div>

                        <div class="card-body">
                            <div class="ss-info">
                                <?php
                                pt_tile('Birthday', htmlspecialchars($bdayText), 'fa-birthday-cake');
                                pt_tile('Age', htmlspecialchars((string)$ageText), 'fa-hashtag');
                                pt_tile('Contact No.', htmlspecialchars($patient->CONTACT_NO), 'fa-phone');
                                pt_tile('Sex', htmlspecialchars($patient->SEX), 'fa-venus-mars');
                                pt_tile('Address', $patient->ADDRESS !== '' ? nl2br(htmlspecialchars($patient->ADDRESS)) : 'No address provided.', 'fa-map-marker-alt', true);
                                ?>
                            </div>
                        </div>

                    </div>


                    <div class="card ss-panel">

                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-notes-medical mr-1"></i> Visit History</h3>
                        </div>

                        <div class="card-body p-0">

                            <?php if (count($visits) < 1): ?>

                                <div class="ss-empty"><i class="fa fa-inbox"></i>No visits recorded for this patient yet.</div>

                            <?php else: ?>

                                <div class="table-responsive">
                                <table class="table table-sm mb-0 ss-mini">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Chief Complaint</th>
                                            <th>Diagnosis</th>
                                            <th>Notes</th>
                                            <th>Doctor</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($visits as $v): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars((string)$v->VISIT_DATE); ?></td>
                                            <td><?php echo htmlspecialchars((string)$v->CHIEF_COMPLAINT); ?></td>
                                            <td><?php echo $v->DIAGNOSIS ? htmlspecialchars($v->DIAGNOSIS) : '<span class="text-muted">Pending</span>'; ?></td>
                                            <td><?php echo htmlspecialchars((string)$v->NOTES); ?></td>
                                            <td><?php echo htmlspecialchars((string)$v->DOCTOR_NAME); ?></td>
                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    class="btn btn-warning btn-xs editVisit"
                                                    title="Add/Edit Diagnosis"
                                                    data-visit-id="<?php echo (int)$v->VISIT_ID; ?>"
                                                    data-patient-id="<?php echo (int)$patient->PATIENT_ID; ?>"
                                                    data-diagnosis="<?php echo htmlspecialchars($v->DIAGNOSIS, ENT_QUOTES); ?>"
                                                    data-notes="<?php echo htmlspecialchars($v->NOTES, ENT_QUOTES); ?>">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                            </td>
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


<!-- MODAL: Edit Visit (Diagnosis/Notes) -->
<div class="modal fade ss-modal" id="editVisitModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form action="controller.php?action=update_visit" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-stethoscope"></i></span>Update Diagnosis</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="VISIT_ID" id="EV_VISIT_ID">
          <input type="hidden" name="PATIENT_ID" id="EV_PATIENT_ID">

          <div class="row">
            <div class="col-12"><div class="ss-group"><i class="fa fa-notes-medical"></i>Visit Findings</div></div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="EV_DIAGNOSIS">Diagnosis</label>
                <div class="ss-input"><i class="fa fa-stethoscope"></i><input type="text" class="form-control" name="DIAGNOSIS" id="EV_DIAGNOSIS"></div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="EV_NOTES">Notes</label>
                <div class="ss-input"><i class="fa fa-align-left"></i><textarea class="form-control" name="NOTES" id="EV_NOTES" rows="3"></textarea></div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" name="save"><i class="fa fa-save"></i> Save</button>
        </div>
      </div>
    </form>
  </div>
</div>