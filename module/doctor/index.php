<?php
// Solitario Solution
/* Doctor Module - manage the clinic's doctor profiles (specialization,
   license no., contact, and schedule), separate from the login account
   itself (tblusers). A profile can optionally be linked to a Doctor-type
   user account via UID so their name/type stays in sync with the login. */
require_once("../../include/initialize.php");
if (!isset($_SESSION['UID'])){
  redirect(WEB_ROOT."login.php");
}

$title   = "Doctor Module";
$content = 'list.php';

require_once("../../theme/template.php");
?>

<script type="text/javascript">
  $(document).ready(function() {

    var t = $('#tbldoctors').DataTable( {
      "processing": true,
      "serverSide": true,
      "autoWidth": false,
      "ajax": {
        url: "<?php echo WEB_ROOT; ?>module/doctor/ajax.php",
        type: "POST"
      },
      "columnDefs": [
        { "targets": 0,  "orderable": false, "searchable": false, "className": "text-center" },
        { "targets": -1, "orderable": false, "searchable": false, "className": "text-center" }
      ],
      "scrollX": true,
      "order": [[ 1, 'asc' ]]
    } );

    $(window).on('resize', function () {
      t.columns.adjust();
    });

  });
</script>

<script type="text/javascript">
  $('#AddNewEntry').on('show.bs.modal', function () {
    var $form = $(this).find('form')[0];
    if ($form) { $form.reset(); }
  });
</script>

<script type="text/javascript">
  $(document).on('click', '.editEntry', function(){
    var uid = $(this).attr("UID");
    $.ajax({
      url:"<?php echo WEB_ROOT; ?>module/doctor/ajax.php",
      method:"POST",
      data:{UID:uid},
      dataType:"json",
      success:function(data)
      {
        $('#DOCTOR_ID').val(data.DOCTOR_ID);
        $('#UID1').val(data.UID ? data.UID : '');
        $('#FULLNAME1').val(data.FULLNAME);
        $('#SPECIALIZATION1').val(data.SPECIALIZATION);
        $('#LICENSE_NO1').val(data.LICENSE_NO);
        $('#CONTACT_NO1').val(data.CONTACT_NO);
        $('#SCHEDULE_DAYS1').val(data.SCHEDULE_DAYS);
        $('#SCHEDULE_TIME1').val(data.SCHEDULE_TIME);
        $('#STATUS1').val(data.STATUS ? data.STATUS : 'Active');

        $('#editEntry').modal('show');
      },
      error:function()
      {
        Swal.fire('Oops', 'Could not load the doctor record.', 'error');
      }
    });
  });
</script>

<script type="text/javascript">
  $(document).on('click', '.deleteEntry', function(){
    var did = $(this).attr("DID");

    Swal.fire({
      title: 'Delete this doctor profile?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it',
      cancelButtonText: 'Cancel'
    }).then(function (result) {
      if (result.value) {
        window.location.href = "<?php echo WEB_ROOT; ?>module/doctor/controller.php?action=delete&id=" + encodeURIComponent(did);
      }
    });
  });
</script>