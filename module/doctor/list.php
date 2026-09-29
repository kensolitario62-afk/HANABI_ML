<?php
// Solitario Solution
global $mydb;

require_once(dirname(__FILE__) . '/style.php');

// Doctor-type login accounts, so a profile can be linked to one.
$doctorAccounts = array();
$mydb->setQuery("SELECT UID, DISPLAYNAME, USERNAME FROM `tblusers` WHERE `TYPE` = 'Doctor' AND `STATUSACTIVE` = 1 ORDER BY DISPLAYNAME ASC");
foreach ($mydb->loadResultList() as $r) { $doctorAccounts[] = $r; }
?>
<section class="content">
  <div class="container-fluid">
    <?php check_message(); ?>
    <div class="row">
      <div class="col-12">

        <div class="card ss-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-user-md"></i>List of Doctors</h3>
          </div>
          <div class="card-body">
            <table id="tbldoctors" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Specialization</th>
                <th>Contact No.</th>
                <th>Schedule</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
              </thead>
              <tbody></tbody>
            </table>
            <div class="ss-actions mt-3">
              <button type="button" class="btn btn-add" data-toggle="modal" data-target="#AddNewEntry"><i class="fa fa-plus"></i> Add New</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- MODAL: ADD NEW DOCTOR -->
<div class="modal fade ss-modal" id="AddNewEntry">
  <div class="modal-dialog modal-lg">
  <form action="controller.php?action=add" method="POST">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-plus"></i></span>Add New Doctor Profile</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12"><div class="ss-group"><i class="fa fa-user-md"></i>Doctor Profile</div></div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="FULLNAME">Full Name</label>
              <div class="ss-input"><i class="fa fa-user-md"></i><input type="text" class="form-control" name="FULLNAME" id="FULLNAME" placeholder="e.g. Dr. Juan Santos" required></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="SPECIALIZATION">Specialization</label>
              <div class="ss-input"><i class="fa fa-stethoscope"></i><input type="text" class="form-control" name="SPECIALIZATION" id="SPECIALIZATION" placeholder="e.g. General Medicine"></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="LICENSE_NO">PRC License No.</label>
              <div class="ss-input"><i class="fa fa-id-card"></i><input type="text" class="form-control" name="LICENSE_NO" id="LICENSE_NO" placeholder="Optional"></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="CONTACT_NO">Contact Number</label>
              <div class="ss-input"><i class="fa fa-phone"></i><input type="text" class="form-control" name="CONTACT_NO" id="CONTACT_NO" placeholder="Enter Contact Number"></div>
            </div>
          </div>

          <div class="col-12"><div class="ss-group"><i class="fa fa-calendar-check-o"></i>Schedule</div></div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="SCHEDULE_DAYS">Schedule Days</label>
              <div class="ss-input"><i class="fa fa-calendar"></i><input type="text" class="form-control" name="SCHEDULE_DAYS" id="SCHEDULE_DAYS" placeholder="e.g. Mon, Wed, Fri"></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="SCHEDULE_TIME">Schedule Time</label>
              <div class="ss-input"><i class="fa fa-clock-o"></i><input type="text" class="form-control" name="SCHEDULE_TIME" id="SCHEDULE_TIME" placeholder="e.g. 8:00 AM - 5:00 PM"></div>
            </div>
          </div>

          <div class="col-12"><div class="ss-group"><i class="fa fa-cog"></i>Account &amp; Status</div></div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="UID">Link to Doctor Account (optional)</label>
              <div class="ss-input"><i class="fa fa-link"></i><select class="form-control" name="UID" id="UID"><option value="">-- No linked account --</option><?php foreach ($doctorAccounts as $u) { ?><option value="<?php echo $u->UID; ?>"><?php echo htmlspecialchars($u->DISPLAYNAME.' ('.$u->USERNAME.')'); ?></option><?php } ?></select></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="STATUS">Status</label>
              <div class="ss-input"><i class="fa fa-toggle-on"></i><select class="form-control" name="STATUS" id="STATUS"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="save"><i class="fa fa-save"></i> Save changes</button>
      </div>
    </div>
  </form>
  </div>
</div>

<!-- MODAL: EDIT DOCTOR -->
<div class="modal fade ss-modal" id="editEntry">
  <div class="modal-dialog modal-lg">
  <form action="controller.php?action=edit" method="POST">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-edit"></i></span>Edit Doctor Profile</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="DOCTOR_ID" id="DOCTOR_ID" value="">
        <div class="row">
          <div class="col-12"><div class="ss-group"><i class="fa fa-user-md"></i>Doctor Profile</div></div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="FULLNAME1">Full Name</label>
              <div class="ss-input"><i class="fa fa-user-md"></i><input type="text" class="form-control" name="FULLNAME" id="FULLNAME1" required></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="SPECIALIZATION1">Specialization</label>
              <div class="ss-input"><i class="fa fa-stethoscope"></i><input type="text" class="form-control" name="SPECIALIZATION" id="SPECIALIZATION1"></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="LICENSE_NO1">PRC License No.</label>
              <div class="ss-input"><i class="fa fa-id-card"></i><input type="text" class="form-control" name="LICENSE_NO" id="LICENSE_NO1"></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="CONTACT_NO1">Contact Number</label>
              <div class="ss-input"><i class="fa fa-phone"></i><input type="text" class="form-control" name="CONTACT_NO" id="CONTACT_NO1"></div>
            </div>
          </div>

          <div class="col-12"><div class="ss-group"><i class="fa fa-calendar-check-o"></i>Schedule</div></div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="SCHEDULE_DAYS1">Schedule Days</label>
              <div class="ss-input"><i class="fa fa-calendar"></i><input type="text" class="form-control" name="SCHEDULE_DAYS" id="SCHEDULE_DAYS1"></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="SCHEDULE_TIME1">Schedule Time</label>
              <div class="ss-input"><i class="fa fa-clock-o"></i><input type="text" class="form-control" name="SCHEDULE_TIME" id="SCHEDULE_TIME1"></div>
            </div>
          </div>

          <div class="col-12"><div class="ss-group"><i class="fa fa-cog"></i>Account &amp; Status</div></div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="UID1">Link to Doctor Account (optional)</label>
              <div class="ss-input"><i class="fa fa-link"></i><select class="form-control" name="UID" id="UID1"><option value="">-- No linked account --</option><?php foreach ($doctorAccounts as $u) { ?><option value="<?php echo $u->UID; ?>"><?php echo htmlspecialchars($u->DISPLAYNAME.' ('.$u->USERNAME.')'); ?></option><?php } ?></select></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="STATUS1">Status</label>
              <div class="ss-input"><i class="fa fa-toggle-on"></i><select class="form-control" name="STATUS" id="STATUS1"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" name="edit"><i class="fa fa-save"></i> Save changes</button>
      </div>
    </div>
  </form>
  </div>
</div>