<?php
// Enrollment list + Assign + Sectioning + Payment + Enroll
global $mydb;

require_once(dirname(__FILE__) . '/style.php');

$enSchoolYears = array();
$mydb->setQuery("SELECT SY_ID, SCHOOL_YEAR, STATUS FROM `tblschoolyear` ORDER BY SCHOOL_YEAR DESC");
foreach ($mydb->loadResultList() as $r) { $enSchoolYears[] = $r; }

$enCourses = array();
$mydb->setQuery("SELECT COURSE_ID, COURSE_CODE, COURSE_NAME FROM `tblcourses` ORDER BY COURSE_CODE ASC");
foreach ($mydb->loadResultList() as $r) { $enCourses[] = $r; }

$enYearLevels = array('1st Year', '2nd Year', '3rd Year', '4th Year');
$enSemesters  = array('1st Semester', '2nd Semester', 'Summer');
$enCategories = array('New', 'Old', 'Transferee', 'Returnee', 'Shiftee');

/* Every status a record can carry. Registered/Assigned/Sectioned/Paid/Enrolled
   are the normal pipeline; Dropped/Completed are manual overrides only
   reachable from the Edit form. */
$enStatuses = array('Registered', 'Assigned', 'Sectioned', 'Paid', 'Enrolled', 'Dropped', 'Completed');

/* Counters for the filter chips. */
$statusCounts = array();
foreach ($enStatuses as $st) {
  $mydb->setQuery("SELECT ENROLLMENT_ID FROM `tblenrollment` WHERE STATUS = '".$mydb->escape_value($st)."'");
  $statusCounts[$st] = $mydb->num_rows();
}
$mydb->setQuery("SELECT ENROLLMENT_ID FROM `tblenrollment`");
$cntAll = $mydb->num_rows();
?>
<section class="content">
  <div class="container-fluid">
    <?php check_message(); ?>

    <!-- The flow, spelled out, so it is obvious which step comes next -->
    <div class="en-flow">
      <span class="lbl"><i class="fas fa-route"></i> Enrollment flow</span>
      <span class="ss-st st-grey"><i class="fas fa-user-plus"></i> Registered</span><i class="fas fa-chevron-right arrow"></i>
      <span class="ss-st st-blue"><i class="fas fa-book"></i> Assigned</span><i class="fas fa-chevron-right arrow"></i>
      <span class="ss-st st-indigo"><i class="fas fa-chalkboard"></i> Sectioning</span><i class="fas fa-chevron-right arrow"></i>
      <span class="ss-st st-amber"><i class="fas fa-cash-register"></i> Payment</span><i class="fas fa-chevron-right arrow"></i>
      <span class="ss-st st-purple"><i class="fas fa-check"></i> Paid</span><i class="fas fa-chevron-right arrow"></i>
      <span class="ss-st st-green"><i class="fas fa-user-check"></i> Enrolled</span>
    </div>

    <div class="row">
      <div class="col-12">

        <div class="card ss-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-clipboard-list"></i>Enrollment Records</h3>
            <div class="card-tools">
              <button type="button" class="btn en-pending" data-toggle="modal" data-target="#pendingPaymentsModal">
                <i class="fas fa-clock"></i> Pending Online Payments <span class="badge badge-warning" id="pendingCountBadge" style="display:none;">0</span>
              </button>
              <div class="en-chips" id="statusFilters">
                <button type="button" class="btn active" data-filter="">All (<?php echo $cntAll; ?>)</button>
                <?php foreach (array('Registered','Assigned','Sectioned','Paid','Enrolled') as $st) { ?>
                <button type="button" class="btn" data-filter="<?php echo $st; ?>"><?php echo $st; ?> (<?php echo $statusCounts[$st]; ?>)</button>
                <?php } ?>
              </div>
            </div>
          </div>

          <div class="card-body">
            <table id="tblenrollmentlist" class="table table-bordered table-striped" style="width:100%">
              <thead>
                <tr>
                  <th>#</th>
                  <th>ID No.</th>
                  <th>Student Name</th>
                  <th>Course</th>
                  <th>Academic Year</th>
                  <th>Semester</th>
                  <th>Year Level</th>
                  <th>Section</th>
                  <th>Amount Due</th>
                  <th>Amount Paid</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>

            <div class="ss-hint mt-3 mb-0">
              <i class="fa fa-info-circle"></i>
              <span>New records start from <strong>Student &gt; Reg</strong>. This screen completes them.</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>


<!-- =================================================================
     STAGE 2: ASSIGN SUBJECTS
     Pick which subjects the student is taking this term. The total
     amount due is calculated live from each subject's unit price.
     ================================================================= -->
<div class="modal fade ss-modal" id="assignModal">
  <div class="modal-dialog modal-lg">
    <form action="controller.php?action=assign" method="POST" id="assignForm">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-book"></i></span>Assign Subjects</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="A_EID" id="A_EID" value="">

          <div class="callout callout-info py-2 mb-3">
            <div class="row">
              <div class="col-sm-4">
                <small class="text-muted d-block">ID No.</small>
                <strong id="A_IDNO_TEXT">-</strong>
              </div>
              <div class="col-sm-8">
                <small class="text-muted d-block">Student Name</small>
                <strong id="A_NAME_TEXT">-</strong>
              </div>
            </div>
          </div>

          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th width="10%"><input type="checkbox" id="A_SELECT_ALL" title="Select / Deselect All"> Take</th>
                <th>Code</th>
                <th>Description</th>
                <th width="10%">Units</th>
                <th width="15%">Amount</th>
              </tr>
            </thead>
            <tbody id="A_SUBJECT_ROWS">
              <tr><td colspan="5" class="text-center text-muted">Loading subjects...</td></tr>
            </tbody>
          </table>

          <div class="text-right">
            <strong>Total Units: <span id="A_TOTAL_UNITS">0</span></strong>
            &nbsp;&nbsp;
            <strong>Total Amount Due: &#8369;<span id="A_TOTAL_AMOUNT">0.00</span></strong>
          </div>

        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Subjects</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- =================================================================
     STAGE 3: SECTIONING
     ================================================================= -->
<div class="modal fade ss-modal" id="sectioningModal">
  <div class="modal-dialog">
    <form action="controller.php?action=section" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-chalkboard"></i></span>Sectioning</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="SEC_EID" id="SEC_EID" value="">
          <input type="hidden" name="SEC_COURSE" id="SEC_COURSE" value="">
          <input type="hidden" name="SEC_SY" id="SEC_SY" value="">

          <div class="callout callout-info py-2 mb-3">
            <div class="row">
              <div class="col-sm-4">
                <small class="text-muted d-block">ID No.</small>
                <strong id="SEC_IDNO_TEXT">-</strong>
              </div>
              <div class="col-sm-8">
                <small class="text-muted d-block">Student Name</small>
                <strong id="SEC_NAME_TEXT">-</strong>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-sm-6">
                <small class="text-muted d-block">Course</small>
                <strong id="SEC_COURSE_TEXT">-</strong>
              </div>
              <div class="col-sm-6">
                <small class="text-muted d-block">AY / Semester</small>
                <strong id="SEC_TERM_TEXT">-</strong>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12">
              <div class="form-group">
                <label for="SEC_SECTION" class="col-form-label col-form-label-sm">Section</label>
                <div class="ss-input"><i class="fa fa-users"></i><select class="form-control" name="SEC_SECTION" id="SEC_SECTION" required>
                  <option value="">Loading...</option>
                </select></div>
                <small class="text-muted d-block mt-1">Only sections belonging to this course and academic year are listed.</small>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" name="section"><i class="fa fa-check"></i> Save Section</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- =================================================================
     STAGE 4: PAYMENT
     ================================================================= -->
<div class="modal fade ss-modal" id="paymentModal">
  <div class="modal-dialog">
    <form action="controller.php?action=pay" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-cash-register"></i></span>Payment</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="P_EID" id="P_EID" value="">

          <div class="callout callout-info py-2 mb-3">
            <div class="row">
              <div class="col-sm-4">
                <small class="text-muted d-block">ID No.</small>
                <strong id="P_IDNO_TEXT">-</strong>
              </div>
              <div class="col-sm-8">
                <small class="text-muted d-block">Student Name</small>
                <strong id="P_NAME_TEXT">-</strong>
              </div>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-6">
              <div class="callout callout-warning py-2 mb-0">
                <small class="text-muted d-block">Registration Fee (flat &#8369;1,000 - unlocks enrollment)</small>
                <div class="row">
                  <div class="col-4"><small class="text-muted">Paid</small><br><strong>&#8369;<span id="P_REG_PAID_TEXT">0.00</span></strong></div>
                  <div class="col-4"><small class="text-muted">Due</small><br><strong>&#8369;<span id="P_REG_DUE_TEXT">1000.00</span></strong></div>
                  <div class="col-4"><small class="text-muted">Balance</small><br><strong class="text-danger">&#8369;<span id="P_REG_BALANCE_TEXT">1000.00</span></strong></div>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="callout callout-info py-2 mb-0">
                <small class="text-muted d-block">Tuition / Units (payable any time, before or after enrollment)</small>
                <div class="row">
                  <div class="col-4"><small class="text-muted">Paid</small><br><strong>&#8369;<span id="P_PAID_TEXT">0.00</span></strong></div>
                  <div class="col-4"><small class="text-muted">Due</small><br><strong>&#8369;<span id="P_DUE_TEXT">0.00</span></strong></div>
                  <div class="col-4"><small class="text-muted">Balance</small><br><strong class="text-danger">&#8369;<span id="P_BALANCE_TEXT">0.00</span></strong></div>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12"><div class="ss-group"><i class="fa fa-cash-register"></i>Record a Payment</div></div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="P_TYPE" class="col-form-label col-form-label-sm">This payment is for</label>
                <div class="ss-input"><i class="fa fa-tags"></i><select class="form-control" name="P_TYPE" id="P_TYPE" required>
                  <option value="Registration">Registration Fee</option>
                  <option value="Tuition">Tuition / Units</option>
                </select></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="P_AMOUNT" class="col-form-label col-form-label-sm">Amount to Pay</label>
                <div class="ss-input"><i class="fa fa-money-bill"></i><input type="number" step="0.01" min="0.01" class="form-control" name="P_AMOUNT" id="P_AMOUNT" required></div>
                <small class="text-muted">Registration and tuition are tracked separately - a student can be enrolled as soon as the &#8369;1,000 registration fee is paid, then settle tuition later.</small>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="P_OR" class="col-form-label col-form-label-sm">OR Number</label>
                <div class="ss-input"><i class="fa fa-receipt"></i><input type="text" class="form-control" name="P_OR" id="P_OR" placeholder="e.g. OR-00123"></div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="P_CASHIER" class="col-form-label col-form-label-sm">Cashier</label>
                <div class="ss-input"><i class="fa fa-user-tie"></i><input type="text" class="form-control" name="P_CASHIER" id="P_CASHIER" placeholder="Enter cashier name" required></div>
              </div>
            </div>
          </div>

          <div id="P_PENDING_WRAP" style="display:none;">
            <hr>
            <small class="text-muted d-block mb-1"><i class="fas fa-clock text-warning"></i> Awaiting Verification (submitted online)</small>
            <table class="table table-sm table-bordered">
              <thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Ref #</th><th>Proof</th><th>Action</th></tr></thead>
              <tbody id="P_PENDING_ROWS"></tbody>
            </table>
          </div>

          <div id="P_HISTORY_WRAP" style="display:none;">
            <hr>
            <small class="text-muted d-block mb-1">Payment History</small>
            <table class="table table-sm table-bordered">
              <thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>OR #</th><th>Cashier</th></tr></thead>
              <tbody id="P_HISTORY_ROWS"></tbody>
            </table>
          </div>

        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Record Payment</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- =================================================================
     Pending Online Payments - registrar-wide queue
     ================================================================= -->
<div class="modal fade ss-modal" id="pendingPaymentsModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fas fa-clock"></i></span>Pending Online Payments</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-sm table-bordered mb-0">
          <thead>
            <tr><th>Date</th><th>ID No.</th><th>Student</th><th>Type</th><th>Amount</th><th>Ref #</th><th>Proof</th><th>Action</th></tr>
          </thead>
          <tbody id="PENDING_QUEUE_ROWS">
            <tr><td colspan="8" class="text-center text-muted py-3">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<!-- =================================================================
     Proof of Payment preview - opens ON TOP of whichever modal is
     already open, so closing it (X, backdrop click, or Esc) returns
     you exactly where you were instead of navigating away.
     ================================================================= -->
<div class="modal fade ss-modal" id="proofPreviewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title"><span class="ss-badge"><i class="fas fa-receipt"></i></span>Proof of Payment</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body text-center" style="max-height:75vh; overflow:auto;">
        <img id="proofPreviewImg" src="" style="max-width:100%; display:none;">
        <div id="proofPreviewPdfNote" style="display:none;">
          <p class="text-muted">This proof was uploaded as a PDF and can't be shown inline.</p>
          <a id="proofPreviewPdfLink" href="#" target="_blank" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Open PDF in a new tab</a>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<!-- =================================================================
     EDIT an existing enrollment record
     ================================================================= -->
<div class="modal fade ss-modal" id="editEnrollmentModal">
  <div class="modal-dialog">
    <form action="controller.php?action=edit" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-edit"></i></span>Edit Enrollment</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="E_EID" id="E_EID" value="">

          <div class="callout callout-info py-2 mb-3">
            <div class="row">
              <div class="col-sm-4">
                <small class="text-muted d-block">ID No.</small>
                <strong id="E_IDNO_TEXT">-</strong>
              </div>
              <div class="col-sm-8">
                <small class="text-muted d-block">Student Name</small>
                <strong id="E_NAME_TEXT">-</strong>
              </div>
            </div>
          </div>

          <div class="row">

            <div class="col-12"><div class="ss-group"><i class="fa fa-calendar"></i>Term</div></div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="E_SY" class="col-form-label col-form-label-sm">Academic Year</label>
                <div class="ss-input"><i class="fa fa-calendar-o"></i><select class="form-control" name="E_SY" id="E_SY" required>
                  <?php foreach ($enSchoolYears as $sy) { ?>
                  <option value="<?php echo $sy->SY_ID; ?>"><?php echo htmlspecialchars($sy->SCHOOL_YEAR); ?></option>
                  <?php } ?>
                </select></div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="E_SEMESTER" class="col-form-label col-form-label-sm">Semester</label>
                <div class="ss-input"><i class="fa fa-flag"></i><select class="form-control" name="E_SEMESTER" id="E_SEMESTER" required>
                  <?php foreach ($enSemesters as $sem) { ?>
                  <option value="<?php echo $sem; ?>"><?php echo $sem; ?></option>
                  <?php } ?>
                </select></div>
              </div>
            </div>

            <div class="col-12"><div class="ss-group"><i class="fa fa-graduation-cap"></i>Course & Placement</div></div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="E_COURSE" class="col-form-label col-form-label-sm">Course</label>
                <div class="ss-input"><i class="fa fa-book"></i><select class="form-control" name="E_COURSE" id="E_COURSE" required>
                  <?php foreach ($enCourses as $c) { ?>
                  <option value="<?php echo $c->COURSE_ID; ?>"><?php echo htmlspecialchars($c->COURSE_CODE.' - '.$c->COURSE_NAME); ?></option>
                  <?php } ?>
                </select></div>
              </div>
            </div>

            <div class="col-sm-12">
              <div class="form-group">
                <label for="E_SECTION" class="col-form-label col-form-label-sm">Section</label>
                <div class="ss-input"><i class="fa fa-users"></i><select class="form-control" name="E_SECTION" id="E_SECTION">
                  <option value="">Not sectioned yet</option>
                </select></div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="E_YEARLEVEL" class="col-form-label col-form-label-sm">Year Level</label>
                <div class="ss-input"><i class="fa fa-level-up"></i><select class="form-control" name="E_YEARLEVEL" id="E_YEARLEVEL" required>
                  <?php foreach ($enYearLevels as $yl) { ?>
                  <option value="<?php echo $yl; ?>"><?php echo $yl; ?></option>
                  <?php } ?>
                </select></div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="E_CURRICULUM" class="col-form-label col-form-label-sm">Curriculum Yr</label>
                <div class="ss-input"><i class="fa fa-file-text-o"></i><input type="text" class="form-control" name="E_CURRICULUM" id="E_CURRICULUM" placeholder="e.g. 2023-2024"></div>
              </div>
            </div>

            <div class="col-12"><div class="ss-group"><i class="fa fa-toggle-on"></i>Status & Dates</div></div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="E_CATEGORY" class="col-form-label col-form-label-sm">Category</label>
                <div class="ss-input"><i class="fa fa-tag"></i><select class="form-control" name="E_CATEGORY" id="E_CATEGORY" required>
                  <?php foreach ($enCategories as $cat) { ?>
                  <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                  <?php } ?>
                </select></div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="E_STATUS" class="col-form-label col-form-label-sm">Status</label>
                <div class="ss-input"><i class="fa fa-toggle-on"></i><select class="form-control" name="E_STATUS" id="E_STATUS" required>
                  <?php foreach ($enStatuses as $st) { ?>
                  <option value="<?php echo $st; ?>"><?php echo $st; ?></option>
                  <?php } ?>
                </select></div>
                <small class="text-muted">Manually overriding this skips the normal flow guards.</small>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="E_DATE_RESERVED" class="col-form-label col-form-label-sm">Date Registered</label>
                <div class="ss-input"><i class="fa fa-calendar-check-o"></i><input type="date" class="form-control" name="E_DATE_RESERVED" id="E_DATE_RESERVED"></div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="form-group">
                <label for="E_DATE_ENROLLED" class="col-form-label col-form-label-sm">Date Enrolled</label>
                <div class="ss-input"><i class="fa fa-calendar-check-o"></i><input type="date" class="form-control" name="E_DATE_ENROLLED" id="E_DATE_ENROLLED"></div>
              </div>
            </div>

          </div>

        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" name="edit"><i class="fa fa-save"></i> Save changes</button>
        </div>
      </div>
    </form>
  </div>
</div>