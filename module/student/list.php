<?php
require_once(dirname(__FILE__) . '/style.php');
/* Dropdown data for the Register modal. $mydb is set up by
   include/initialize.php, which template.php has already loaded.

   NOTE: there is no Section field here on purpose. Sectioning is the
   SECOND stage and happens on the Enrollment screen. */
global $mydb;

// Pull every school year, so the Register form's "Academic Year"
// dropdown can list them (newest first) and flag which one is Active.
$regSchoolYears = array();
$mydb->setQuery("SELECT SY_ID, SCHOOL_YEAR, STATUS FROM `tblschoolyear` ORDER BY SCHOOL_YEAR DESC");
foreach ($mydb->loadResultList() as $r) { $regSchoolYears[] = $r; }

// Pull every active course, for the "Course" dropdown.
$regCourses = array();
$mydb->setQuery("SELECT COURSE_ID, COURSE_CODE, COURSE_NAME FROM `tblcourses` WHERE STATUS = 'Active' ORDER BY COURSE_CODE ASC");
foreach ($mydb->loadResultList() as $r) { $regCourses[] = $r; }

// Fixed option lists (not stored in the database, just hardcoded here)
// used to build the Year Level, Semester, and Category dropdowns.
$regYearLevels = array('1st Year', '2nd Year', '3rd Year', '4th Year');
$regSemesters  = array('1st Semester', '2nd Semester', 'Summer');
$regCategories = array('New', 'Old', 'Transferee', 'Returnee', 'Shiftee');
?>
<!-- ============================================================
     THE STUDENT LIST TABLE
     Shown as a DataTable - the actual rows are fetched live from
     ajax.php via JavaScript, this HTML just builds the empty shell
     (headers + an "Add New" button that opens the modal below).
     ============================================================ -->
 
      <div class="container-fluid">
         <?php check_message(); ?>
        <div class="row">
          <div class="col-12">
          
            <div class="card ss-card">
              <div class="card-header">
                <h3 class="card-title"><i class="fa fa-graduation-cap"></i>List of Students</h3>
              </div>

              <!-- /.card-header -->
              <div class="card-body">
                <table id="tblstudent" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>#</th>
                    <!-- /`LNAME`, `FNAME`, `MNAME`, `SEX`, `BDAY`-->
                    <th>LNAME</th>
                    <th>FNAME</th>
                    <th>MNAME</th>
                    <th>SEX</th>
                      <th>BDAY</th>

                   
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  
                  </tbody>
                  <tfoot>
                  
                  </tfoot>
                </table>
                  <div class="ss-actions mt-3">
          
                  <button type="button" class="btn btn-add" data-toggle="modal" data-target="#AddNewEntry"><i class="fa fa-user-plus"></i> Add New</button>
                 
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


<!-- ============================================================
     ADD NEW STUDENT MODAL
     A brand new student record only. This does NOT enroll them for
     a term - that starts separately via the "Reg" button (the
     Register modal further down this file).
     Posts to controller.php?action=add
     ============================================================ -->
<div class="modal fade ss-modal" id="AddNewEntry">
        <div class="modal-dialog">
        <form action="controller.php?action=add" enctype="multipart/form-data" method="POST">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-user-plus"></i></span>Add New Student</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <!-- Photo preview + file picker. The placeholder circle
                   is just an inline SVG so nothing breaks if no photo
                   is chosen - it gets replaced client-side once a
                   file is picked (handled in index.php's JS). -->
              <div class="ss-photo">
                <img id="img-upload" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='90' height='90'><rect width='100%25' height='100%25' fill='%23e0e0e0'/><text x='50%25' y='50%25' font-size='12' fill='%23888' text-anchor='middle' dy='.3em'>No Photo</text></svg>" width="90" height="90">
                <div class="form-group mb-0">
                  <label for="imgInp" class="col-form-label col-form-label-sm d-block">Photo</label>
                  <input type="file" class="form-control-file" name="photo" id="imgInp" accept="image/*">
                </div>
              </div>

              <div class="row">

                <!-- Core identifying info: ID number + full name.
                     IDNO is checked against duplicates server-side in
                     controller.php before this can be saved. -->
                <div class="col-12"><div class="ss-group"><i class="fa fa-id-card"></i>Identity</div></div>
                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="IDNO" class="col-form-label col-form-label-sm">Student ID Number</label>
                    <div class="ss-input"><i class="fa fa-id-card"></i><input type="text" class="form-control" name="IDNO" id="IDNO" placeholder="Enter Student ID Number" required></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="FNAME" class="col-form-label col-form-label-sm">First Name</label>
                    <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="FNAME" id="FNAME" placeholder="Enter First Name" required></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="MNAME" class="col-form-label col-form-label-sm">Middle Name</label>
                    <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="MNAME" id="MNAME" placeholder="Enter Middle Name" required></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="LNAME" class="col-form-label col-form-label-sm">Last Name</label>
                    <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="LNAME" id="LNAME" placeholder="Enter Last Name" required></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="SEX" class="col-form-label col-form-label-sm">Select Gender</label>
                    <div class="ss-input"><i class="fa fa-venus-mars"></i><select class="form-control" name="SEX" id="SEX" required>
                      <option value="">Select Gender</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                    </select></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="BDAY" class="col-form-label col-form-label-sm">Date Started</label>
                    <div class="ss-input"><i class="fa fa-calendar"></i><input type="date" class="form-control" name="BDAY" id="BDAY" required></div>
                  </div>
                </div>

                <!-- Extended profile fields (Birth Place through Home
                     Address). None of these are required - a student
                     can be saved with just the fields above filled
                     in, and these completed later via Edit. -->
                <div class="col-12"><div class="ss-group"><i class="fa fa-user-circle"></i>Personal Details</div></div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="BPLACE" class="col-form-label col-form-label-sm">Birth Place</label>
                    <div class="ss-input"><i class="fa fa-map-marker"></i><input type="text" class="form-control" name="BPLACE" id="BPLACE" placeholder="Enter Birth Place"></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="AGE" class="col-form-label col-form-label-sm">Age</label>
                    <div class="ss-input"><i class="fa fa-hashtag"></i><input type="number" class="form-control" name="AGE" id="AGE" placeholder="Enter Age" min="0" max="150"></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="NATIONALITY" class="col-form-label col-form-label-sm">Nationality</label>
                    <div class="ss-input"><i class="fa fa-flag"></i><input type="text" class="form-control" name="NATIONALITY" id="NATIONALITY" placeholder="Enter Nationality"></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="RELIGION" class="col-form-label col-form-label-sm">Religion</label>
                    <div class="ss-input"><i class="fa fa-star"></i><input type="text" class="form-control" name="RELIGION" id="RELIGION" placeholder="Enter Religion"></div>
                  </div>
                </div>

                <div class="col-12"><div class="ss-group"><i class="fa fa-phone"></i>Contact</div></div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="CONTACT_NO" class="col-form-label col-form-label-sm">Contact Number</label>
                    <div class="ss-input"><i class="fa fa-phone"></i><input type="text" class="form-control" name="CONTACT_NO" id="CONTACT_NO" placeholder="Enter Contact Number"></div>
                  </div>
                </div>

                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="EMAIL" class="col-form-label col-form-label-sm">Email</label>
                    <div class="ss-input"><i class="fa fa-envelope"></i><input type="email" class="form-control" name="EMAIL" id="EMAIL" placeholder="Enter Email"></div>
                  </div>
                </div>

                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="HOME_ADD" class="col-form-label col-form-label-sm">Home Address</label>
                    <div class="ss-input"><i class="fa fa-home"></i><textarea class="form-control" name="HOME_ADD" id="HOME_ADD" placeholder="Enter Home Address"></textarea></div>
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
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
<!-----END of Add Form---->


<!-- ============================================================
     EDIT STUDENT MODAL
     Same fields as Add New, plus a read-only "who am I editing"
     summary box at the top (EDIT_IDNO_TEXT / EDIT_NAME_TEXT) so it's
     always obvious which student's record is open. All values are
     filled in by JavaScript (in index.php) right before this modal
     is shown - nothing here is pre-filled by PHP.
     Posts to controller.php?action=edit
     ============================================================ -->
   <div class="modal fade ss-modal" id="editEntry">
        <div class="modal-dialog">
        <form action="controller.php?action=edit" enctype="multipart/form-data" method="POST">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-user-edit"></i></span>Edit Student</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

<!-- carries S_ID of the row being edited. Hidden: it is not something
     the user should ever see or type into. -->
<input type="hidden" name="UID" id="UID" value="">

              <!-- Who is being edited, shown read-only at a glance -->
              <div class="ss-who"><i class="fa fa-id-badge"></i><div class="row w-100"><div class="col-sm-5"><small>ID No.</small><strong id="EDIT_IDNO_TEXT">-</strong></div><div class="col-sm-7"><small>Student Name</small><strong id="EDIT_NAME_TEXT">-</strong></div></div></div>

              <div class="ss-photo">
                <img id="currentPhoto" src="" width="90" height="90" style="display:none;" onerror="this.style.display='none';">
                <div class="form-group mb-0">
                  <label for="photo1" class="col-form-label col-form-label-sm d-block">Photo (leave blank to keep current)</label>
                  <input type="file" class="form-control-file" name="photo1" id="photo1" accept="image/*">
                </div>
              </div>

              <div class="row">

                <div class="col-12"><div class="ss-group"><i class="fa fa-id-card"></i>Identity</div></div>
                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="IDNO1" class="col-form-label col-form-label-sm">Student ID Number</label>
                    <div class="ss-input"><i class="fa fa-id-card"></i><input type="text" class="form-control" name="IDNO1" id="IDNO1" placeholder="Enter Student ID Number" required></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="FNAME1" class="col-form-label col-form-label-sm">First Name</label>
                    <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="FNAME1" id="FNAME1" placeholder="Enter First Name" required></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="MNAME1" class="col-form-label col-form-label-sm">Middle Name</label>
                    <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="MNAME1" id="MNAME1" placeholder="Enter Middle Name" required></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="LNAME1" class="col-form-label col-form-label-sm">Last Name</label>
                    <div class="ss-input"><i class="fa fa-user"></i><input type="text" class="form-control" name="LNAME1" id="LNAME1" placeholder="Enter Last Name" required></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="SEX1" class="col-form-label col-form-label-sm">Select Gender</label>
                    <div class="ss-input"><i class="fa fa-venus-mars"></i><select class="form-control" name="SEX1" id="SEX1" required>
                      <option value="">Select Gender</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                    </select></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="BDAY1" class="col-form-label col-form-label-sm">Date Started</label>
                    <div class="ss-input"><i class="fa fa-calendar"></i><input type="date" class="form-control" name="BDAY1" id="BDAY1" required></div>
                  </div>
                </div>

                <div class="col-12"><div class="ss-group"><i class="fa fa-user-circle"></i>Personal Details</div></div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="BPLACE1" class="col-form-label col-form-label-sm">Birth Place</label>
                    <div class="ss-input"><i class="fa fa-map-marker"></i><input type="text" class="form-control" name="BPLACE1" id="BPLACE1" placeholder="Enter Birth Place"></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="AGE1" class="col-form-label col-form-label-sm">Age</label>
                    <div class="ss-input"><i class="fa fa-hashtag"></i><input type="number" class="form-control" name="AGE1" id="AGE1" placeholder="Enter Age" min="0" max="150"></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="NATIONALITY1" class="col-form-label col-form-label-sm">Nationality</label>
                    <div class="ss-input"><i class="fa fa-flag"></i><input type="text" class="form-control" name="NATIONALITY1" id="NATIONALITY1" placeholder="Enter Nationality"></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="RELIGION1" class="col-form-label col-form-label-sm">Religion</label>
                    <div class="ss-input"><i class="fa fa-star"></i><input type="text" class="form-control" name="RELIGION1" id="RELIGION1" placeholder="Enter Religion"></div>
                  </div>
                </div>

                <div class="col-12"><div class="ss-group"><i class="fa fa-phone"></i>Contact</div></div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="CONTACT_NO1" class="col-form-label col-form-label-sm">Contact Number</label>
                    <div class="ss-input"><i class="fa fa-phone"></i><input type="text" class="form-control" name="CONTACT_NO1" id="CONTACT_NO1" placeholder="Enter Contact Number"></div>
                  </div>
                </div>

                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="EMAIL1" class="col-form-label col-form-label-sm">Email</label>
                    <div class="ss-input"><i class="fa fa-envelope"></i><input type="email" class="form-control" name="EMAIL1" id="EMAIL1" placeholder="Enter Email"></div>
                  </div>
                </div>

                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="HOME_ADD1" class="col-form-label col-form-label-sm">Home Address</label>
                    <div class="ss-input"><i class="fa fa-home"></i><textarea class="form-control" name="HOME_ADD1" id="HOME_ADD1" placeholder="Enter Home Address"></textarea></div>
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
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
<!-----END of Edit Form---->


<!-----STAGE 1: RESERVE SLOT---->
<!--
     Opened by the green Reg button in the Action column.

     This creates the enrollment record with STATUS = Registered. It does
     NOT assign a section and does NOT set the enrollment date, because
     at this point in the flow neither is known yet. Both are filled in
     later from the Enrollment screen (Sectioning).
-->
   <div class="modal fade ss-modal" id="registerEntry">
        <div class="modal-dialog">
        <form action="controller.php?action=register" method="POST">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-clipboard-check"></i></span>Register for Enrollment</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <input type="hidden" name="R_SID" id="R_SID" value="">

              <!-- Who is being reserved. Read only: the student comes from
                   whichever row's button was clicked, not from typing. -->
              <div class="ss-who"><i class="fa fa-id-badge"></i><div class="row w-100"><div class="col-sm-5"><small>ID No.</small><strong id="R_IDNO_TEXT">-</strong></div><div class="col-sm-7"><small>Student Name</small><strong id="R_NAME_TEXT">-</strong></div></div></div>

              <!-- What term is this registration FOR. This is the
                   data that actually gets written into the new
                   tblenrollment row - everything else here (Year
                   Level, Category, Curriculum Yr) rides along with it. -->
              <div class="row">

                <div class="col-12"><div class="ss-group"><i class="fa fa-calendar"></i>Term</div></div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="R_SY" class="col-form-label col-form-label-sm">Academic Year</label>
                    <div class="ss-input"><i class="fa fa-calendar-o"></i><select class="form-control" name="R_SY" id="R_SY" required>
                      <option value="">Select Academic Year</option>
                      <?php foreach ($regSchoolYears as $sy) { ?>
                      <option value="<?php echo $sy->SY_ID; ?>"><?php echo htmlspecialchars($sy->SCHOOL_YEAR); ?><?php echo ($sy->STATUS == 'Active') ? ' (Active)' : ''; ?></option>
                      <?php } ?>
                    </select></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="R_SEMESTER" class="col-form-label col-form-label-sm">Semester</label>
                    <div class="ss-input"><i class="fa fa-flag"></i><select class="form-control" name="R_SEMESTER" id="R_SEMESTER" required>
                      <option value="">Select Semester</option>
                      <?php foreach ($regSemesters as $sem) { ?>
                      <option value="<?php echo $sem; ?>"><?php echo $sem; ?></option>
                      <?php } ?>
                    </select></div>
                  </div>
                </div>

                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="R_COURSE" class="col-form-label col-form-label-sm">Course</label>
                    <div class="ss-input"><i class="fa fa-book"></i><select class="form-control" name="R_COURSE" id="R_COURSE" required>
                      <option value="">Select Course</option>
                      <?php foreach ($regCourses as $c) { ?>
                      <option value="<?php echo $c->COURSE_ID; ?>"><?php echo htmlspecialchars($c->COURSE_CODE.' - '.$c->COURSE_NAME); ?></option>
                      <?php } ?>
                    </select></div>
                  </div>
                </div>

                <div class="col-12"><div class="ss-group"><i class="fa fa-graduation-cap"></i>Class</div></div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="R_YEARLEVEL" class="col-form-label col-form-label-sm">Year Level</label>
                    <div class="ss-input"><i class="fa fa-level-up"></i><select class="form-control" name="R_YEARLEVEL" id="R_YEARLEVEL" required>
                      <option value="">Select Year Level</option>
                      <?php foreach ($regYearLevels as $yl) { ?>
                      <option value="<?php echo $yl; ?>"><?php echo $yl; ?></option>
                      <?php } ?>
                    </select></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="R_CURRICULUM" class="col-form-label col-form-label-sm">Curriculum Yr</label>
                    <div class="ss-input"><i class="fa fa-file-text-o"></i><input type="text" class="form-control" name="R_CURRICULUM"
                           id="R_CURRICULUM" placeholder="e.g. 2023-2024"></div>
                  </div>
                </div>

                <div class="col-12"><div class="ss-group"><i class="fa fa-clipboard"></i>Registration</div></div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="R_CATEGORY" class="col-form-label col-form-label-sm">Category</label>
                    <div class="ss-input"><i class="fa fa-tag"></i><select class="form-control" name="R_CATEGORY" id="R_CATEGORY" required>
                      <?php foreach ($regCategories as $cat) { ?>
                      <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                      <?php } ?>
                    </select></div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="R_DATE_RESERVED" class="col-form-label col-form-label-sm">Date Registered</label>
                    <div class="ss-input"><i class="fa fa-calendar-check-o"></i><input type="date" class="form-control" name="R_DATE_RESERVED"
                           id="R_DATE_RESERVED" value="<?php echo date('Y-m-d'); ?></div>" required>
                  </div>
                </div>

              </div>

              <div class="ss-hint"><i class="fa fa-info-circle"></i><span>Section and Date Enrolled are assigned later, from <strong>Enrollment &gt; Sectioning</strong>. This step only reserves the slot.</span></div>

            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success" name="register"><i class="fa fa-save"></i> Register</button>
            </div>
          </div>
          </form>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
<!-----END of Register Form---->


<!-- ============================================================
     RECORD CONSULTATION MODAL (Doctor Module)
     Opened from the "Consult" button on a student row. The doctor
     only answers the Chief Complaint here - Diagnosis is filled in
     afterward from that patient's own record once assessment is
     complete.
     Posts to module/doctor/controller.php?action=consult
     ============================================================ -->
<div class="modal fade ss-modal" id="consultEntryModal">
  <div class="modal-dialog">
    <form action="<?php echo WEB_ROOT; ?>module/doctor/controller.php?action=consult" method="POST">
      <div class="modal-content">
        <div class="modal-header">
              <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-stethoscope"></i></span>Record Consultation</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="S_ID" id="C_SID">
          <input type="hidden" name="RETURN_URL" id="C_RETURN_URL">

          <div class="ss-who"><i class="fa fa-user-circle"></i><div><small>Student</small><strong id="C_NAME_TEXT">-</strong></div></div>

          <div class="form-group">
            <label for="C_CHIEF_COMPLAINT" class="col-form-label col-form-label-sm">Chief Complaint</label>
            <div class="ss-input"><i class="fa fa-stethoscope"></i><input type="text" class="form-control" name="CHIEF_COMPLAINT"
              id="C_CHIEF_COMPLAINT" placeholder="e.g. Fever, headache" required></div>
          </div>

          <div class="form-group">
            <label for="C_NOTES" class="col-form-label col-form-label-sm">Notes (optional)</label>
            <div class="ss-input"><i class="fa fa-pencil"></i><textarea class="form-control" name="NOTES" id="C_NOTES"
              placeholder="Anything else worth noting"></textarea></div>
          </div>

          <div class="ss-hint"><i class="fa fa-info-circle"></i><span>Diagnosis is filled in afterward from the patient's record once assessment is complete.</span></div>

        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" name="save"><i class="fa fa-save"></i> Record Consultation</button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-----END of Consult Form---->




<?php
// Nothing else runs here - this trailing block just closes out the
// file cleanly since it's included by template.php, not run directly.
?>