<?php require_once(dirname(__FILE__) . '/style.php'); ?>
<section class="content">

  <div class="container-fluid">

    <?php check_message(); ?>

    <div class="row">

      <div class="col-12">

        <div class="card ss-card">

          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-door-open"></i>List of Classrooms</h3>
          </div>

          <div class="card-body">

            <table id="tblclassroom" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th>Classroom Name</th>
                  <th>Description</th>
                  <th>Action</th>
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

          </div>

        </div>

      </div>

    </div>

  </div>

</section>


<!-- ADD CLASSROOM MODAL -->
<div class="modal fade ss-modal" id="AddNewEntry" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form action="controller.php?action=add" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-plus"></i></span>Add New Classroom</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12"><div class="ss-group"><i class="fa fa-door-open"></i>Classroom Details</div></div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="NAME">Classroom Name</label>
                <div class="ss-input"><i class="fa fa-door-open"></i><input type="text" class="form-control" name="NAME" id="NAME" placeholder="e.g. Room 101" required></div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="DESCRIPTION">Description</label>
                <div class="ss-input"><i class="fa fa-align-left"></i><textarea class="form-control" name="DESCRIPTION" id="DESCRIPTION" rows="3" placeholder="Short description (capacity, building, floor...)"></textarea></div>
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

<!-- EDIT CLASSROOM MODAL -->
<div class="modal fade ss-modal" id="editClassroomModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form action="controller.php?action=edit" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-edit"></i></span>Modify Classroom</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <input type="hidden" name="ID" id="ID">
            <div class="col-12"><div class="ss-group"><i class="fa fa-door-open"></i>Classroom Details</div></div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="NAME1">Classroom Name</label>
                <div class="ss-input"><i class="fa fa-door-open"></i><input type="text" class="form-control" name="NAME1" id="NAME1" placeholder="Classroom Name" required></div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="DESCRIPTION1">Description</label>
                <div class="ss-input"><i class="fa fa-align-left"></i><textarea class="form-control" name="DESCRIPTION1" id="DESCRIPTION1" rows="3" placeholder="Short description (capacity, building, floor...)"></textarea></div>
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