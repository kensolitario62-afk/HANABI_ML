<?php require_once(dirname(__FILE__) . '/style.php'); ?>
<section class="content">

  <div class="container-fluid">

    <?php check_message(); ?>

    <div class="row">

      <div class="col-12">

        <div class="card ss-card">

          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-calendar"></i>List of Schedule Days</h3>
          </div>

          <div class="card-body">

            <table id="tblscheduleday" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th>DAY NAME</th>
                  <th>DESCRIPTION</th>
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
              <span>Manage the days of the week used by the Set Schedule module.</span>
            </div>

          </div>

        </div>

      </div>

    </div>

  </div>

</section>


<!-- ADD NEW SCHEDULE DAY MODAL -->
<div class="modal fade ss-modal" id="AddNewEntry" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form action="controller.php?action=add" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-plus"></i></span>Add New Schedule Day</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12"><div class="ss-group"><i class="fa fa-calendar"></i>Day Details</div></div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="NAME">Day Name</label>
                <div class="ss-input"><i class="fa fa-calendar-day"></i><input type="text" class="form-control" name="NAME" id="NAME" placeholder="e.g. Monday" required></div>
              </div>
            </div>
            <div class="col-12"><div class="ss-group"><i class="fa fa-align-left"></i>Details</div></div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="DESCRIPTION">Description</label>
                <div class="ss-input"><i class="fa fa-align-left"></i><textarea class="form-control" name="DESCRIPTION" id="DESCRIPTION" rows="3" placeholder="Short description"></textarea></div>
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


<!-- EDIT SCHEDULE DAY MODAL -->
<div class="modal fade ss-modal" id="editEntry" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form action="controller.php?action=edit" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-edit"></i></span>Modify Schedule Day</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <input type="hidden" name="ID" id="ID">
            <div class="col-12"><div class="ss-group"><i class="fa fa-calendar"></i>Day Details</div></div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="NAME1">Day Name</label>
                <div class="ss-input"><i class="fa fa-calendar-day"></i><input type="text" class="form-control" name="NAME1" id="NAME1" placeholder="e.g. Monday" required></div>
              </div>
            </div>
            <div class="col-12"><div class="ss-group"><i class="fa fa-align-left"></i>Details</div></div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="DESCRIPTION1">Description</label>
                <div class="ss-input"><i class="fa fa-align-left"></i><textarea class="form-control" name="DESCRIPTION1" id="DESCRIPTION1" rows="3" placeholder="Short description"></textarea></div>
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