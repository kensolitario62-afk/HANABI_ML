<?php

// Enrollment module entry point

// Load the system initialization file and database connection.
require_once("../../include/initialize.php");

// Check if the user is logged in.
if (!isset($_SESSION['UID'])) {

  // Redirect the user to the login page if not logged in.
  redirect(WEB_ROOT."login.php");
}

// Set the page title.
$title = "Enrollment Module";

// Set list.php as the main content of the enrollment module.
$content = 'list.php';

// Load the main website template.
require_once("../../theme/template.php");
?>

<script type="text/javascript">
$(document).ready(function () {

  // Store the currently selected status filter.
  var currentFilter = '';

  // Create the enrollment DataTable.
  var table = $('#tblenrollmentlist').DataTable({
    processing: true,
    serverSide: true,
    autoWidth: false,
    scrollX: true,
    order: [],
    columnDefs: [
      { targets: 0,  orderable: false, searchable: false, className: 'text-center' },
      { targets: -1, orderable: false, searchable: false, className: 'text-center' }
    ],

    // Get enrollment data from ajax.php.
    ajax: {
      url: "<?php echo WEB_ROOT; ?>module/enrollment/ajax.php",
      type: "POST",

      // Send the selected status filter to the server.
      data: function (d) { d.status_filter = currentFilter; }
    }
  });


  /* Status filter chips */

  // Run this code when a status filter button is clicked.
  $('#statusFilters button').on('click', function () {

    // Remove the active style from all filter buttons.
    $('#statusFilters button').removeClass('active');

    // Add the active style to the clicked button.
    $(this).addClass('active');

    // Get the filter value from the clicked button.
    currentFilter = $(this).data('filter') || '';

    // Reload the enrollment table using the new filter.
    table.ajax.reload();
  });


  /* -----------------------------------------------------------
     STAGE: ASSIGN SUBJECTS
     ----------------------------------------------------------- */


  // Create the subject rows inside the assignment table.
  function renderSubjectRows(subjects) {

    // Check if there are no subjects available.
    if (subjects.length < 1) {

      // Display a message when no subjects are found.
      $('#A_SUBJECT_ROWS').html(
        '<tr><td colspan="5" class="text-center text-muted">No subjects found for this course, year level, and semester.</td></tr>'
      );

      // Stop the function.
      return;
    }

    // Create an empty string for the table rows.
    var html = '';

    // Loop through every subject.
    $.each(subjects, function (i, s) {

      // Check if the subject was already selected.
      var checked = s.CHECKED ? 'checked' : '';

      // Calculate the total price of the subject.
      var amount = (s.UNITS * s.PRICE_PER_UNIT).toFixed(2);

      // Create a table row for the subject.
      html += '<tr>' +

        // Create the checkbox used to select the subject.
        '<td class="text-center"><input type="checkbox" name="SUBJECTS[]" value="' +
        s.SUBJECT_ID +
        '" class="subjectCheck" data-units="' +
        s.UNITS +
        '" data-amount="' +
        amount +
        '" ' +
        checked +
        '></td>' +

        // Display the subject code.
        '<td>' + s.SUBJECT_CODE + '</td>' +

        // Display the subject name.
        '<td>' + s.SUBJECT_NAME + '</td>' +

        // Display the number of units.
        '<td>' + s.UNITS + '</td>' +

        // Display the subject price.
        '<td>&#8369;' + amount + '</td>' +

        '</tr>';
    });

    // Insert the generated subject rows into the table.
    $('#A_SUBJECT_ROWS').html(html);

    // Reset the header checkbox each time the list is (re)loaded, then
    // sync it to whatever came back already checked (e.g. re-opening
    // Edit Subjects on a record that's already assigned).
    syncSelectAllState();

    // Calculate the total units and amount.
    recalcAssignTotals();
  }


  // Keeps the header "Select All" checkbox in sync with the rows below it.
  function syncSelectAllState() {
    var total = $('.subjectCheck').length;
    var checked = $('.subjectCheck:checked').length;
    $('#A_SELECT_ALL').prop('checked', total > 0 && checked === total);
  }


  // Calculate the total selected units and amount.
  function recalcAssignTotals() {

    // Start the total units and amount at zero.
    var units = 0, amount = 0;

    // Check every selected subject.
    $('.subjectCheck:checked').each(function () {

      // Add the subject units to the total.
      units += parseFloat($(this).data('units'));

      // Add the subject amount to the total.
      amount += parseFloat($(this).data('amount'));
    });

    // Display the total number of units.
    $('#A_TOTAL_UNITS').text(units);

    // Display the total amount with two decimal places.
    $('#A_TOTAL_AMOUNT').text(amount.toFixed(2));
  }


  // Recalculate the totals whenever a subject checkbox changes, and keep
  // the header checkbox's state truthful to what's actually checked.
  $(document).on('change', '.subjectCheck', function () {
    recalcAssignTotals();
    syncSelectAllState();
  });


  // Clicking the header checkbox checks/unchecks every subject row.
  $(document).on('change', '#A_SELECT_ALL', function () {
    $('.subjectCheck').prop('checked', $(this).is(':checked'));
    recalcAssignTotals();
  });


  // Open the subject assignment modal when Assign is clicked.
  $(document).on('click', '.doAssign', function () {

    // Get the enrollment ID from the clicked button.
    var eid = $(this).attr("EID");

    // Store the enrollment ID in the hidden form field.
    $('#A_EID').val(eid);

    // Show a loading message while subjects are being loaded.
    $('#A_SUBJECT_ROWS').html(
      '<tr><td colspan="5" class="text-center text-muted">Loading subjects...</td></tr>'
    );


    // Request the student's subjects from the server.
    $.ajax({
      url: "<?php echo WEB_ROOT; ?>module/enrollment/ajax.php",
      method: "POST",

      // Send the action and enrollment ID.
      data: {
        act: 'assign_data',
        ENROLLMENT_ID: eid
      },

      // Expect the server response in JSON format.
      dataType: "json",

      // Run when the request succeeds.
      success: function (data) {

        // Display the student's ID number.
        $('#A_IDNO_TEXT').text(data.idno || '-');

        // Display the student's name.
        $('#A_NAME_TEXT').text(data.name || '-');

        // Display the available subjects.
        renderSubjectRows(data.subjects);

        // Show the subject assignment modal.
        $('#assignModal').modal('show');
      },

      // Show an error message if the request fails.
      error: function () {
        Swal.fire('Oops', 'Could not load the subject list.', 'error');
      }
    });
  });


  /* -----------------------------------------------------------
     STAGE: SECTIONING
     ----------------------------------------------------------- */


  // Open the sectioning modal when the Section button is clicked.
  $(document).on('click', '.doSectioning', function () {

    // Get the enrollment ID from the clicked button.
    var eid = $(this).attr("EID");


    // Request the enrollment information from the server.
    $.ajax({
      url: "<?php echo WEB_ROOT; ?>module/enrollment/ajax.php",
      method: "POST",

      // Ask the server for one enrollment record.
      data: {
        act: 'row',
        ENROLLMENT_ID: eid
      },

      // Expect JSON data from the server.
      dataType: "json",

      // Run when the enrollment information is received.
      success: function (data) {

        // Store the enrollment ID in the sectioning form.
        $('#SEC_EID').val(data.ENROLLMENT_ID);

        // Store the course ID.
        $('#SEC_COURSE').val(data.COURSE_ID);

        // Store the school year ID.
        $('#SEC_SY').val(data.SY_ID);

        // Display the student's ID number.
        $('#SEC_IDNO_TEXT').text(data.IDNO || '-');

        // Display the student's name.
        $('#SEC_NAME_TEXT').text(data.FULLNAME || '-');

        // Display the student's course.
        $('#SEC_COURSE_TEXT').text(data.COURSE_TEXT || '-');

        // Display the school year and semester.
        $('#SEC_TERM_TEXT').text(
          (data.SCHOOL_YEAR || '-') + ' / ' + (data.SEMESTER || '-')
        );


        // Show a loading option while sections are being loaded.
        $('#SEC_SECTION').html('<option value="">Loading...</option>');


        // Request sections for the student's course and school year.
        $.ajax({
          url: "<?php echo WEB_ROOT; ?>module/enrollment/ajax.php",
          method: "POST",

          // Send the course and school year IDs.
          data: {
            act: 'sections',
            COURSE_ID: data.COURSE_ID,
            SY_ID: data.SY_ID
          },

          // Expect the section list in JSON format.
          dataType: "json",

          // Run when the section list is received.
          success: function (sections) {

            // Check if there are no available sections.
            if (sections.length < 1) {

              // Display a message when no sections exist.
              $('#SEC_SECTION').html(
                '<option value="">No sections available for this course/year</option>'
              );

              // Stop the function.
              return;
            }

            // Add the default section option.
            var html = '<option value="">Select Section</option>';

            // Loop through all available sections.
            $.each(sections, function (i, s) {

              // Select the student's current section if it exists.
              var sel = (
                String(s.SECTION_ID) === String(data.SECTION_ID)
              ) ? 'selected' : '';

              // Add the section as an option.
              html += '<option value="' +
                s.SECTION_ID +
                '" ' +
                sel +
                '>' +
                s.SECTION_NAME +
                ' (' +
                s.YEAR_LEVEL +
                ')</option>';
            });

            // Display all sections in the dropdown.
            $('#SEC_SECTION').html(html);
          }
        });


        // Show the sectioning modal.
        $('#sectioningModal').modal('show');
      },

      // Show an error if the enrollment record cannot be loaded.
      error: function () {
        Swal.fire('Oops', 'Could not load the enrollment record.', 'error');
      }
    });
  });


  /* -----------------------------------------------------------
     STAGE: PAYMENT
     ----------------------------------------------------------- */


  // Open the payment modal when the Payment button is clicked.
  $(document).on('click', '.doPayment', function () {

    // Get the enrollment ID from the button.
    var eid = $(this).attr("EID");


    // Request the student's payment information.
    $.ajax({
      url: "<?php echo WEB_ROOT; ?>module/enrollment/ajax.php",
      method: "POST",

      // Send the payment data request.
      data: {
        act: 'payment_data',
        ENROLLMENT_ID: eid
      },

      // Expect JSON data.
      dataType: "json",

      // Run when payment information is received.
      success: function (data) {

        // Store the enrollment ID.
        $('#P_EID').val(eid);

        // Display the student's ID number.
        $('#P_IDNO_TEXT').text(data.idno || '-');

        // Display the student's name.
        $('#P_NAME_TEXT').text(data.name || '-');

        // Registration Fee ledger.
        $('#P_REG_PAID_TEXT').text((parseFloat(data.reg_paid) || 0).toFixed(2));
        $('#P_REG_DUE_TEXT').text((parseFloat(data.reg_due) || 0).toFixed(2));
        $('#P_REG_BALANCE_TEXT').text((parseFloat(data.reg_balance) || 0).toFixed(2));

        // Tuition/units ledger.
        $('#P_DUE_TEXT').text((parseFloat(data.due) || 0).toFixed(2));
        $('#P_PAID_TEXT').text((parseFloat(data.paid) || 0).toFixed(2));
        $('#P_BALANCE_TEXT').text((parseFloat(data.balance) || 0).toFixed(2));

        // Default to whichever ledger still has a balance; Registration first.
        $('#P_TYPE').val(parseFloat(data.reg_balance) > 0 ? 'Registration' : 'Tuition');

        // Keep the Amount field's max in step with whichever ledger is selected.
        function syncPaymentMax() {
          var max = ($('#P_TYPE').val() === 'Registration') ? data.reg_balance : data.balance;
          $('#P_AMOUNT').attr('max', max > 0 ? max : '');
        }
        syncPaymentMax();
        $('#P_TYPE').off('change.paymentMax').on('change.paymentMax', syncPaymentMax);

        // Clear the payment amount field.
        $('#P_AMOUNT').val('');

        // Clear the official receipt number.
        $('#P_OR').val('');

        // Clear the cashier name.
        $('#P_CASHIER').val('');


        // Check if the student has payment history.
        if (data.history && data.history.length > 0) {

          // Start an empty table row string.
          var html = '';

          // Loop through each payment record.
          $.each(data.history, function (i, h) {

            // Create a row showing the payment details.
            html += '<tr>' +
              '<td>' + h.DATE + '</td>' +
              '<td>' + (h.TYPE || '-') + '</td>' +
              '<td>&#8369;' + parseFloat(h.AMOUNT).toFixed(2) + '</td>' +
              '<td>' + (h.OR || '-') + '</td>' +
              '<td>' + (h.CASHIER || '-') + '</td>' +
              '</tr>';
          });

          // Display the payment history.
          $('#P_HISTORY_ROWS').html(html);

          // Show the payment history section.
          $('#P_HISTORY_WRAP').show();

        } else {

          // Hide the payment history if there are no records.
          $('#P_HISTORY_WRAP').hide();
        }

        // Render this student's pending online submissions, if any.
        if (data.pending && data.pending.length > 0) {

          var pendHtml = '';
          $.each(data.pending, function (i, p) {
            var proofLink = p.PROOF ? '<a href="#" class="viewProof" data-proof="' + p.PROOF + '">View</a>' : '-';
            pendHtml += '<tr>' +
              '<td>' + p.DATE + '</td>' +
              '<td>' + p.TYPE + '</td>' +
              '<td>&#8369;' + parseFloat(p.AMOUNT).toFixed(2) + '</td>' +
              '<td>' + (p.REFERENCE || '-') + '</td>' +
              '<td>' + proofLink + '</td>' +
              '<td>' +
                '<a href="<?php echo WEB_ROOT; ?>module/enrollment/controller.php?action=verify_payment&pid=' + p.PAYMENT_ID + '&decision=approve" ' +
                  'class="btn btn-success btn-xs confirmLink" data-msg="Approve this payment and apply it to the balance?">Approve</a> ' +
                '<a href="<?php echo WEB_ROOT; ?>module/enrollment/controller.php?action=verify_payment&pid=' + p.PAYMENT_ID + '&decision=reject" ' +
                  'class="btn btn-danger btn-xs confirmLink" data-msg="Reject this payment submission?">Reject</a>' +
              '</td>' +
              '</tr>';
          });
          $('#P_PENDING_ROWS').html(pendHtml);
          $('#P_PENDING_WRAP').show();

        } else {
          $('#P_PENDING_WRAP').hide();
        }


        // Show the payment modal.
        $('#paymentModal').modal('show');
      },

      // Show an error if payment information cannot be loaded.
      error: function () {
        Swal.fire('Oops', 'Could not load payment information.', 'error');
      }
    });
  });


  /* -----------------------------------------------------------
     Registrar-wide Pending Online Payments queue
     ----------------------------------------------------------- */
  function loadPendingQueue() {
    $.ajax({
      url: "<?php echo WEB_ROOT; ?>module/enrollment/ajax.php",
      method: "POST",
      data: { act: 'pending_payments' },
      dataType: "json",
      success: function (rows) {

        // Update the toolbar badge.
        if (rows.length > 0) {
          $('#pendingCountBadge').text(rows.length).show();
        } else {
          $('#pendingCountBadge').hide();
        }

        // Render the queue table.
        if (rows.length < 1) {
          $('#PENDING_QUEUE_ROWS').html('<tr><td colspan="8" class="text-center text-muted py-3">No pending online payments.</td></tr>');
          return;
        }

        var html = '';
        $.each(rows, function (i, p) {
          var proofLink = p.PROOF ? '<a href="#" class="viewProof" data-proof="' + p.PROOF + '">View</a>' : '-';
          html += '<tr>' +
            '<td>' + p.DATE + '</td>' +
            '<td>' + p.IDNO + '</td>' +
            '<td>' + p.NAME + '</td>' +
            '<td>' + p.TYPE + '</td>' +
            '<td>&#8369;' + parseFloat(p.AMOUNT).toFixed(2) + '</td>' +
            '<td>' + (p.REFERENCE || '-') + '</td>' +
            '<td>' + proofLink + '</td>' +
            '<td>' +
              '<a href="<?php echo WEB_ROOT; ?>module/enrollment/controller.php?action=verify_payment&pid=' + p.PAYMENT_ID + '&decision=approve" ' +
                'class="btn btn-success btn-xs confirmLink" data-msg="Approve this payment and apply it to the balance?">Approve</a> ' +
              '<a href="<?php echo WEB_ROOT; ?>module/enrollment/controller.php?action=verify_payment&pid=' + p.PAYMENT_ID + '&decision=reject" ' +
                'class="btn btn-danger btn-xs confirmLink" data-msg="Reject this payment submission?">Reject</a>' +
            '</td>' +
            '</tr>';
        });
        $('#PENDING_QUEUE_ROWS').html(html);
      }
    });
  }

  // Load the badge count on page load, and refresh the table each time the modal opens.
  $(document).ready(function () { loadPendingQueue(); });
  $('#pendingPaymentsModal').on('show.bs.modal', loadPendingQueue);


  /* -----------------------------------------------------------
     Proof of Payment preview - opens on top of whichever modal
     triggered it, so closing it returns you exactly where you
     were instead of navigating the browser away.
     ----------------------------------------------------------- */
  $(document).on('click', '.viewProof', function (e) {
    e.preventDefault();

    var proofPath = $(this).data('proof');
    var url = "<?php echo WEB_ROOT; ?>portal/" + proofPath;
    var isPdf = /\.pdf($|\?)/i.test(proofPath);

    if (isPdf) {
      $('#proofPreviewImg').hide();
      $('#proofPreviewPdfLink').attr('href', url);
      $('#proofPreviewPdfNote').show();
    } else {
      $('#proofPreviewPdfNote').hide();
      $('#proofPreviewImg').attr('src', url).show();
    }

    $('#proofPreviewModal').modal('show');
  });


  /* -----------------------------------------------------------
     STAGE: ENROLL
     This stage is locked until the payment status is Paid.
     ----------------------------------------------------------- */


  // Run when the Enroll button is clicked.
  $(document).on('click', '.doEnroll', function (e) {

    e.preventDefault();

    // Get the enrollment ID.
    var eid = $(this).attr("EID");

    // Redirect to the controller to finalize enrollment.
    function goEnroll() {
      window.location.href = "controller.php?action=enroll&id=" + eid;
    }

    // If the SweetAlert popup library did not load on this page, fall back
    // to the browser's own confirm box so the button still works.
    if (typeof Swal === 'undefined') {
      if (window.confirm('Finalize enrollment? This will mark the student as Enrolled.')) {
        goEnroll();
      }
      return;
    }

    // Ask the user to confirm the enrollment.
    Swal.fire({
      title: 'Finalize enrollment?',
      text: 'This will mark the student as Enrolled.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, enroll',
      cancelButtonText: 'Cancel'
    }).then(function (result) {
      // "isConfirmed" is used by newer SweetAlert2, "value" by older ones.
      if (result.isConfirmed || result.value === true) {
        goEnroll();
      }
    });
  });


  /* -----------------------------------------------------------
     Shared confirmation for Delete / Approve / Reject links
     ----------------------------------------------------------- */
  $(document).on('click', '.confirmLink', function (e) {
    e.preventDefault();

    var href = $(this).attr('href');
    var msg  = $(this).data('msg') || 'Are you sure?';

    Swal.fire({
      title: msg,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, continue',
      cancelButtonText: 'Cancel'
    }).then(function (result) {
      if (result.value) {
        window.location.href = href;
      }
    });
  });


  /* -----------------------------------------------------------
     EDIT
     ----------------------------------------------------------- */


  // Open the edit enrollment modal when the Edit button is clicked.
  $(document).on('click', '.editEnrollment', function () {

    // Get the enrollment ID.
    var eid = $(this).attr("EID");


    // Request the enrollment record from the server.
    $.ajax({
      url: "<?php echo WEB_ROOT; ?>module/enrollment/ajax.php",
      method: "POST",

      // Send the request to get one enrollment record.
      data: {
        act: 'row',
        ENROLLMENT_ID: eid
      },

      // Expect JSON data from the server.
      dataType: "json",

      // Run when the enrollment data is received.
      success: function (data) {

        // Fill the edit form with the enrollment information.
        $('#E_EID').val(data.ENROLLMENT_ID);

        // Display the student's ID number.
        $('#E_IDNO_TEXT').text(data.IDNO || '-');

        // Display the student's full name.
        $('#E_NAME_TEXT').text(data.FULLNAME || '-');

        // Set the school year.
        $('#E_SY').val(data.SY_ID);

        // Set the semester.
        $('#E_SEMESTER').val(data.SEMESTER);

        // Set the course.
        $('#E_COURSE').val(data.COURSE_ID);

        // Set the year level.
        $('#E_YEARLEVEL').val(data.YEAR_LEVEL);

        // Set the curriculum year.
        $('#E_CURRICULUM').val(data.CURRICULUM_YR);

        // Set the student category.
        $('#E_CATEGORY').val(data.CATEGORY);

        // Set the enrollment status.
        $('#E_STATUS').val(data.STATUS);

        // Set the reservation date.
        $('#E_DATE_RESERVED').val(data.DATE_RESERVED);

        // Set the enrollment date.
        $('#E_DATE_ENROLLED').val(data.DATE_ENROLLED);


        // Show a loading option while sections are being loaded.
        $('#E_SECTION').html('<option value="">Loading...</option>');


        // Request sections based on the selected course and school year.
        $.ajax({
          url: "<?php echo WEB_ROOT; ?>module/enrollment/ajax.php",
          method: "POST",

          // Send the course and school year IDs.
          data: {
            act: 'sections',
            COURSE_ID: data.COURSE_ID,
            SY_ID: data.SY_ID
          },

          // Expect the section list in JSON format.
          dataType: "json",

          // Run when the sections are received.
          success: function (sections) {

            // Add the default "Not sectioned" option.
            var html = '<option value="">Not sectioned</option>';

            // Loop through all available sections.
            $.each(sections, function (i, s) {

              // Select the student's current section.
              var sel = (
                String(s.SECTION_ID) === String(data.SECTION_ID)
              ) ? 'selected' : '';

              // Add the section to the dropdown.
              html += '<option value="' +
                s.SECTION_ID +
                '" ' +
                sel +
                '>' +
                s.SECTION_NAME +
                ' (' +
                s.YEAR_LEVEL +
                ')</option>';
            });

            // Display the sections in the dropdown.
            $('#E_SECTION').html(html);
          }
        });


        // Show the edit enrollment modal.
        $('#editEnrollmentModal').modal('show');
      },

      // Show an error if the enrollment record cannot be loaded.
      error: function () {
        Swal.fire('Oops', 'Could not load the enrollment record.', 'error');
      }
    });
  });

});
</script>