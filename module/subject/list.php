<?php

require_once(dirname(__FILE__) . '/style.php');

$course = new Course();
$allCourses = $course->listOfCourses();
?>
<section class="content">

  <div class="container-fluid">
    <?php check_message(); ?>
    <div class="row">
      <div class="col-12">

        <div class="card ss-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-book-open"></i>List of Subjects</h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <table id="tblsubject" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th width="5%">#</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Units</th>
                <th>Course</th>
                <th>Year Level</th>
                <th>Semester</th>
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
  <div class="modal-dialog modal-lg">
    <form action="controller.php?action=add" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-plus"></i></span>Add New Subject</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
                <div class="col-12"><div class="ss-group"><i class="fa fa-book"></i>Subject Details</div></div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="SUBJECT_CODE">Subject Code</label>
                    <div class="ss-input"><i class="fa fa-hashtag"></i><input type="text" class="form-control" name="SUBJECT_CODE" id="SUBJECT_CODE" placeholder="e.g. IT101" required></div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="UNITS">Units</label>
                    <div class="ss-input"><i class="fa fa-star"></i><input type="number" step="1" min="1" class="form-control" name="UNITS" id="UNITS" placeholder="3" value="3" required></div>
                  </div>
                </div>
                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="SUBJECT_NAME">Subject Name</label>
                    <div class="ss-input"><i class="fa fa-book"></i><input type="text" class="form-control" name="SUBJECT_NAME" id="SUBJECT_NAME" placeholder="e.g. Introduction to Computing" required></div>
                  </div>
                </div>

                <div class="col-12"><div class="ss-group"><i class="fa fa-graduation-cap"></i>Placement</div></div>
                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="COURSE_ID">Course</label>
                    <div class="ss-input"><i class="fa fa-graduation-cap"></i><select class="form-control" name="COURSE_ID" id="COURSE_ID" required><option value="">-- Select Course --</option><?php foreach($allCourses as $c): ?><option value="<?php echo $c->COURSE_ID; ?>"><?php echo htmlspecialchars($c->COURSE_CODE." - ".$c->COURSE_NAME); ?></option><?php endforeach; ?></select></div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="YEAR_LEVEL">Year Level</label>
                    <div class="ss-input"><i class="fa fa-level-up"></i><select class="form-control" name="YEAR_LEVEL" id="YEAR_LEVEL" required><option value="1st Year">1st Year</option><option value="2nd Year">2nd Year</option><option value="3rd Year">3rd Year</option><option value="4th Year">4th Year</option></select></div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="SEMESTER">Semester</label>
                    <div class="ss-input"><i class="fa fa-flag"></i><select class="form-control" name="SEMESTER" id="SEMESTER" required><option value="1st Semester">1st Semester</option><option value="2nd Semester">2nd Semester</option><option value="Summer">Summer</option></select></div>
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
  <div class="modal-dialog modal-lg">
    <form action="controller.php?action=edit" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-edit"></i></span>Modify Subject</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
                <input type="hidden" name="SUBJECT_ID" id="SUBJECT_ID">
                <div class="col-12"><div class="ss-group"><i class="fa fa-book"></i>Subject Details</div></div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="SUBJECT_CODE1">Subject Code</label>
                    <div class="ss-input"><i class="fa fa-hashtag"></i><input type="text" class="form-control" name="SUBJECT_CODE1" id="SUBJECT_CODE1" placeholder="Subject Code"></div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="UNITS1">Units</label>
                    <div class="ss-input"><i class="fa fa-star"></i><input type="number" step="1" min="1" class="form-control" name="UNITS1" id="UNITS1" placeholder="3"></div>
                  </div>
                </div>
                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="SUBJECT_NAME1">Subject Name</label>
                    <div class="ss-input"><i class="fa fa-book"></i><input type="text" class="form-control" name="SUBJECT_NAME1" id="SUBJECT_NAME1" placeholder="Subject Name"></div>
                  </div>
                </div>

                <div class="col-12"><div class="ss-group"><i class="fa fa-graduation-cap"></i>Placement</div></div>
                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="COURSE_ID1">Course</label>
                    <div class="ss-input"><i class="fa fa-graduation-cap"></i><select class="form-control" name="COURSE_ID1" id="COURSE_ID1"><option value="">-- Select Course --</option><?php foreach($allCourses as $c): ?><option value="<?php echo $c->COURSE_ID; ?>"><?php echo htmlspecialchars($c->COURSE_CODE." - ".$c->COURSE_NAME); ?></option><?php endforeach; ?></select></div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="YEAR_LEVEL1">Year Level</label>
                    <div class="ss-input"><i class="fa fa-level-up"></i><select class="form-control" name="YEAR_LEVEL1" id="YEAR_LEVEL1"><option value="1st Year">1st Year</option><option value="2nd Year">2nd Year</option><option value="3rd Year">3rd Year</option><option value="4th Year">4th Year</option></select></div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="SEMESTER1">Semester</label>
                    <div class="ss-input"><i class="fa fa-flag"></i><select class="form-control" name="SEMESTER1" id="SEMESTER1"><option value="1st Semester">1st Semester</option><option value="2nd Semester">2nd Semester</option><option value="Summer">Summer</option></select></div>
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