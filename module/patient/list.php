<?php require_once(dirname(__FILE__) . '/style.php'); ?>
<section class="content">

  <div class="container-fluid">

    <?php check_message(); ?>

    <div class="row">

      <div class="col-12">

        <div class="card ss-card">

          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-user-injured"></i>List of Patients</h3>
          </div>

          <div class="card-body">

            <table id="tblpatient" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th>LAST NAME</th>
                  <th>FIRST NAME</th>
                  <th>SEX</th>
                  <th>CONTACT NO.</th>
                  <th>STATUS</th>
                  <th width="15%">Action</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
              <tfoot>
              </tfoot>
            </table>

            <div class="ss-actions mt-3">
              <button type="button" class="btn btn-add" data-toggle="modal" data-target="#AddNewEntry">
                <i class="fa fa-plus"></i> Add New
              </button>
            </div>

            <div class="ss-hint mt-3 mb-0">
              <i class="fa fa-info-circle"></i>
              <span>Manage patient records and their visit history.</span>
            </div>

          </div>

        </div>

      </div>

    </div>

  </div>

</section>


<!-- ADD NEW PATIENT MODAL -->
<div class="modal fade ss-modal" id="AddNewEntry" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form action="controller.php?action=add" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-plus"></i></span>Add New Patient</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">

            <div class="col-12"><div class="ss-group"><i class="fa fa-id-card"></i>Personal Details</div></div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="FNAME">First Name</label>
                <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="FNAME" id="FNAME" required></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="MNAME">Middle Name</label>
                <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="MNAME" id="MNAME"></div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="LNAME">Last Name</label>
                <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="LNAME" id="LNAME" required></div>
              </div>
            </div>

            <div class="col-12"><div class="ss-group"><i class="fa fa-notes-medical"></i>Bio Info</div></div>

            <div class="col-sm-4">
              <div class="form-group">
                <label for="SEX">Sex</label>
                <div class="ss-input"><i class="fa fa-venus-mars"></i>
                  <select class="form-control" name="SEX" id="SEX">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="BDAY">Birthday</label>
                <div class="ss-input"><i class="fa fa-birthday-cake"></i><input type="date" class="form-control" name="BDAY" id="BDAY"></div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="AGE">Age</label>
                <div class="ss-input"><i class="fa fa-hashtag"></i><input type="number" min="0" class="form-control" name="AGE" id="AGE"></div>
              </div>
            </div>

            <div class="col-12"><div class="ss-group"><i class="fa fa-address-book"></i>Contact</div></div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="CONTACT_NO">Contact No.</label>
                <div class="ss-input"><i class="fa fa-phone"></i><input type="text" class="form-control" name="CONTACT_NO" id="CONTACT_NO"></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="STATUS">Status</label>
                <div class="ss-input"><i class="fa fa-toggle-on"></i>
                  <select class="form-control" name="STATUS" id="STATUS">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="ADDRESS">Address</label>
                <div class="ss-input"><i class="fa fa-map-marker-alt"></i><textarea class="form-control" name="ADDRESS" id="ADDRESS" rows="3"></textarea></div>
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


<!-- EDIT PATIENT MODAL -->
<div class="modal fade ss-modal" id="editEntry" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form action="controller.php?action=edit" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-edit"></i></span>Modify Patient</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">

            <input type="hidden" name="PATIENT_ID" id="PATIENT_ID">

            <div class="col-12"><div class="ss-group"><i class="fa fa-id-card"></i>Personal Details</div></div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="FNAME1">First Name</label>
                <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="FNAME1" id="FNAME1" required></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="MNAME1">Middle Name</label>
                <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="MNAME1" id="MNAME1"></div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="LNAME1">Last Name</label>
                <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="LNAME1" id="LNAME1" required></div>
              </div>
            </div>

            <div class="col-12"><div class="ss-group"><i class="fa fa-notes-medical"></i>Bio Info</div></div>

            <div class="col-sm-4">
              <div class="form-group">
                <label for="SEX1">Sex</label>
                <div class="ss-input"><i class="fa fa-venus-mars"></i>
                  <select class="form-control" name="SEX1" id="SEX1">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="BDAY1">Birthday</label>
                <div class="ss-input"><i class="fa fa-birthday-cake"></i><input type="date" class="form-control" name="BDAY1" id="BDAY1"></div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label for="AGE1">Age</label>
                <div class="ss-input"><i class="fa fa-hashtag"></i><input type="number" min="0" class="form-control" name="AGE1" id="AGE1"></div>
              </div>
            </div>

            <div class="col-12"><div class="ss-group"><i class="fa fa-address-book"></i>Contact</div></div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="CONTACT_NO1">Contact No.</label>
                <div class="ss-input"><i class="fa fa-phone"></i><input type="text" class="form-control" name="CONTACT_NO1" id="CONTACT_NO1"></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="STATUS1">Status</label>
                <div class="ss-input"><i class="fa fa-toggle-on"></i>
                  <select class="form-control" name="STATUS1" id="STATUS1">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="ADDRESS1">Address</label>
                <div class="ss-input"><i class="fa fa-map-marker-alt"></i><textarea class="form-control" name="ADDRESS1" id="ADDRESS1" rows="3"></textarea></div>
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