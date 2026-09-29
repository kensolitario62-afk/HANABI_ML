<?php
require_once("../../include/initialize.php");
if (!isset($_SESSION['UID'])){
    redirect(WEB_ROOT."login.php");
}

$view = (isset($_GET['view']) && $_GET['view'] != '') ? $_GET['view'] : '';
$title = "Patient Module";
$header = $view;
switch ($view) {
	case 'view' :
		$content = 'view.php';
		break;

	default :
		$content = 'list.php';
}
require_once("../../theme/template.php");
?>

<script type="text/javascript">
    $(document).ready(function() {
        var t = $('#tblpatient').DataTable( {
        "processing":true,
        "serverSide":true,
        "order":[],
        "ajax":{
          url:"<?php echo WEB_ROOT; ?>module/patient/ajax.php",
          type:"POST"
        }
        } );
    });
</script>

<script type="text/javascript">
  $(document).on('click', '.editEntry', function(){
    var id = $(this).attr("PATIENT_ID");
    $.ajax({
      url:"<?php echo WEB_ROOT; ?>module/patient/ajax.php",
      method:"POST",
      data:{PATIENT_ID:id},
      dataType:"json",
      success:function(data)
      {
       $('#editEntry').modal('show');
       $('#PATIENT_ID').val(data.PATIENT_ID);
       $('#FNAME1').val(data.FNAME);
       $('#MNAME1').val(data.MNAME);
       $('#LNAME1').val(data.LNAME);
       $('#SEX1').val(data.SEX);
       $('#BDAY1').val(data.BDAY);
       $('#AGE1').val(data.AGE);
       $('#CONTACT_NO1').val(data.CONTACT_NO);
       $('#ADDRESS1').val(data.ADDRESS);
       $('#STATUS1').val(data.STATUS);
       $('.modal-title').text("Modify Patient");
      }
    })
  });
</script>

<script type="text/javascript">
  $(document).on('click', '.deleteEntry', function(){
    var id = $(this).attr("PATIENT_ID");
    Swal.fire({
      title: 'Delete Patient?',
      text: "This action cannot be undone.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.value) {
        window.location.href = "<?php echo WEB_ROOT; ?>module/patient/controller.php?action=delete&id=" + id;
      }
    });
  });
</script>

<script type="text/javascript">
  /* Edit Visit modal - used on the View page to fill in Diagnosis/Notes. */
  $(document).on('click', '.editVisit', function(){
    $('#EV_VISIT_ID').val($(this).data('visit-id'));
    $('#EV_PATIENT_ID').val($(this).data('patient-id'));
    $('#EV_DIAGNOSIS').val($(this).data('diagnosis'));
    $('#EV_NOTES').val($(this).data('notes'));
    $('#editVisitModal').modal('show');
  });
</script>