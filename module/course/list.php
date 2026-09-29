<?php require_once(dirname(__FILE__) . '/style.php'); ?>
<section class="content">

  <div class="container-fluid">
    <?php check_message(); ?>
    <div class="row">
      <div class="col-12">

        <div class="card ss-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-book"></i>List of Courses</h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <table id="tblcourse" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th width="5%">#</th>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
              </thead>
              <tbody>

              </tbody>
              <tfoot>

              </tfoot>
            </table>

            <div class="ss-actions mt-3">
              <button type="button" class="btn btn-add" data-toggle="modal" data-target="#AddNewEntry"><i class="fa fa-plus"></i> Add New</button>
            </div>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div>
  <!-- /.container-fluid -->
</section>

<!-----START of Add Form---->
<div class="modal fade ss-modal" id="AddNewEntry">
  <div class="modal-dialog">
    <form action="controller.php?action=add" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-plus"></i></span>Add New Course</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
                    <div class="col-12"><div class="ss-group"><i class="fa fa-graduation-cap"></i>Course Details</div></div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="COURSE_CODE">Course Code</label>
                        <div class="ss-input"><i class="fa fa-hashtag"></i><input type="text" class="form-control" name="COURSE_CODE" id="COURSE_CODE" placeholder="e.g. BSIT" required></div>
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="COURSE_NAME">Course Name</label>
                        <div class="ss-input"><i class="fa fa-graduation-cap"></i><input type="text" class="form-control" name="COURSE_NAME" id="COURSE_NAME" placeholder="e.g. Bachelor of Science in Information Technology" required></div>
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="COURSE_DESC">Description</label>
                        <div class="ss-input"><i class="fa fa-align-left"></i><textarea class="form-control" name="COURSE_DESC" id="COURSE_DESC" placeholder="Short description"></textarea></div>
                      </div>
                    </div>

                    <div class="col-12"><div class="ss-group"><i class="fa fa-sliders"></i>Availability</div></div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="STATUS">Status</label>
                        <div class="ss-input"><i class="fa fa-toggle-on"></i><select class="form-control" name="STATUS" id="STATUS" required><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
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
<!-----End of Add Form---->

<!-----Start of edit Form---->
<div class="modal fade ss-modal" id="editEntry">
  <div class="modal-dialog">
    <form action="controller.php?action=edit" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-edit"></i></span>Modify Course</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
                    <input type="hidden" name="COURSE_ID" id="COURSE_ID">
                    <div class="col-12"><div class="ss-group"><i class="fa fa-graduation-cap"></i>Course Details</div></div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="COURSE_CODE1">Course Code</label>
                        <div class="ss-input"><i class="fa fa-hashtag"></i><input type="text" class="form-control" name="COURSE_CODE1" id="COURSE_CODE1" placeholder="e.g. BSIT"></div>
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="COURSE_NAME1">Course Name</label>
                        <div class="ss-input"><i class="fa fa-graduation-cap"></i><input type="text" class="form-control" name="COURSE_NAME1" id="COURSE_NAME1" placeholder="e.g. Bachelor of Science in Information Technology"></div>
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="COURSE_DESC1">Description</label>
                        <div class="ss-input"><i class="fa fa-align-left"></i><textarea class="form-control" name="COURSE_DESC1" id="COURSE_DESC1" placeholder="Short description"></textarea></div>
                      </div>
                    </div>

                    <div class="col-12"><div class="ss-group"><i class="fa fa-sliders"></i>Availability</div></div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label for="STATUS1">Status</label>
                        <div class="ss-input"><i class="fa fa-toggle-on"></i><select class="form-control" name="STATUS1" id="STATUS1"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
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