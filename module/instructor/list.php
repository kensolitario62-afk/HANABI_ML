<?php require_once(dirname(__FILE__) . '/style.php'); ?>
<section class="content">
  <div class="container-fluid">

    <?php check_message(); ?>

    <div class="row">
      <div class="col-12">

        <div class="card ss-card">

          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-chalkboard-teacher"></i>List of Instructors</h3>
          </div>

          <div class="card-body">

            <table id="tblinstructor" class="table table-bordered table-striped" style="width:100%;">
              <thead>
                <tr>
                  <th style="width:5%;">#</th>
                  <th style="width:18%;">Instructor ID</th>
                  <th style="width:25%;">Instructor Name</th>
                  <th style="width:32%;">Description</th>
                  <th style="width:20%;">Action</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>

            <div class="ss-actions mt-3">
              <button type="button" class="btn btn-add" data-toggle="modal" data-target="#AddNewEntry">
                <i class="fa fa-plus"></i> Add New
              </button>
            </div>

          </div>

        </div>

      </div>
    </div>

  </div>
</section>


<!-- ADD NEW INSTRUCTOR -->
<div class="modal fade ss-modal" id="AddNewEntry" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="controller.php?action=add" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><span class="ss-badge"><i class="fa fa-user-plus"></i></span>Add New Instructor</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
          <div class="col-12"><div class="ss-group"><i class="fa fa-chalkboard-teacher"></i>Instructor Details</div></div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="INSTRUCTOR_ID">Instructor ID</label>
              <div class="ss-input"><i class="fa fa-id-card"></i><input type="text" name="INSTRUCTOR_ID" id="INSTRUCTOR_ID" class="form-control" placeholder="e.g. 2026-001"></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="NAME">Instructor Name</label>
              <div class="ss-input"><i class="fa fa-user"></i><input type="text" name="NAME" id="NAME" class="form-control" placeholder="Full name" required></div>
            </div>
          </div>
          <div class="col-sm-12">
            <div class="form-group">
              <label for="DESCRIPTION">Description</label>
              <div class="ss-input"><i class="fa fa-align-left"></i><textarea name="DESCRIPTION" id="DESCRIPTION" class="form-control" rows="3" placeholder="Department, specialization, notes..."></textarea></div>
            </div>
          </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- EDIT INSTRUCTOR -->
<div class="modal fade ss-modal" id="editEntry" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="controller.php?action=edit" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><span class="ss-badge"><i class="fa fa-edit"></i></span>Edit Instructor</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
          <input type="hidden" name="ID" id="ID">
          <div class="col-12"><div class="ss-group"><i class="fa fa-chalkboard-teacher"></i>Instructor Details</div></div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="INSTRUCTOR_ID1">Instructor ID</label>
              <div class="ss-input"><i class="fa fa-id-card"></i><input type="text" name="INSTRUCTOR_ID1" id="INSTRUCTOR_ID1" class="form-control" placeholder="e.g. 2026-001"></div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="NAME1">Instructor Name</label>
              <div class="ss-input"><i class="fa fa-user"></i><input type="text" name="NAME1" id="NAME1" class="form-control" placeholder="Full name" required></div>
            </div>
          </div>
          <div class="col-sm-12">
            <div class="form-group">
              <label for="DESCRIPTION1">Description</label>
              <div class="ss-input"><i class="fa fa-align-left"></i><textarea name="DESCRIPTION1" id="DESCRIPTION1" class="form-control" rows="3" placeholder="Department, specialization, notes..."></textarea></div>
            </div>
          </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
        </div>
      </div>
    </form>
  </div>
</div>