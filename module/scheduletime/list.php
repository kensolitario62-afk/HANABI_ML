<?php require_once(dirname(__FILE__) . '/style.php'); ?>
<section class="content">

  <div class="container-fluid">

    <?php check_message(); ?>

    <div class="row">

      <div class="col-12">

        <div class="card ss-card">

          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-clock"></i>List of Schedule Times</h3>
          </div>

          <div class="card-body">

            <table id="tblscheduletime" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th>TIME START</th>
                  <th>TIME END</th>
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
              <span>Manage schedule time periods used by the Set Schedule module.</span>
            </div>

          </div>

        </div>

      </div>

    </div>

  </div>

</section>


<!-- ADD NEW SCHEDULE TIME MODAL -->
<div class="modal fade ss-modal" id="AddNewEntry" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form action="controller.php?action=add" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-plus"></i></span>Add New Schedule Time</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12"><div class="ss-group"><i class="fa fa-clock-o"></i>Time Period</div></div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="TIME_START">Time Start</label>
                <div class="ss-input"><i class="fa fa-clock-o"></i><input type="time" class="form-control" name="TIME_START" id="TIME_START" required></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="TIME_END">Time End</label>
                <div class="ss-input"><i class="fa fa-hourglass-end"></i><input type="time" class="form-control" name="TIME_END" id="TIME_END" required></div>
              </div>
            </div>
            <div class="col-12"><div class="ss-group"><i class="fa fa-align-left"></i>Details</div></div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="DESCRIPTION">Description</label>
                <div class="ss-input"><i class="fa fa-align-left"></i><textarea class="form-control" name="DESCRIPTION" id="DESCRIPTION" rows="3" placeholder="e.g. Morning Class"></textarea></div>
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


<!-- EDIT SCHEDULE TIME MODAL -->
<div class="modal fade ss-modal" id="editEntry" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <form action="controller.php?action=edit" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-edit"></i></span>Modify Schedule Time</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <input type="hidden" name="ID" id="ID">
            <div class="col-12"><div class="ss-group"><i class="fa fa-clock-o"></i>Time Period</div></div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="TIME_START1">Time Start</label>
                <div class="ss-input"><i class="fa fa-clock-o"></i><input type="time" class="form-control" name="TIME_START1" id="TIME_START1" required></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="TIME_END1">Time End</label>
                <div class="ss-input"><i class="fa fa-hourglass-end"></i><input type="time" class="form-control" name="TIME_END1" id="TIME_END1" required></div>
              </div>
            </div>
            <div class="col-12"><div class="ss-group"><i class="fa fa-align-left"></i>Details</div></div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="DESCRIPTION1">Description</label>
                <div class="ss-input"><i class="fa fa-align-left"></i><textarea class="form-control" name="DESCRIPTION1" id="DESCRIPTION1" rows="3" placeholder="e.g. Morning Class"></textarea></div>
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