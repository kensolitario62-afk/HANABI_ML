<?php
require_once("../../include/initialize.php");
// if (!isset($_SESSION['ACCOUNT_ID'])){
  //    redirect(web_root."/index.php");
//     }

$view = (isset($_GET['view']) && $_GET['view'] != '') ? $_GET['view'] : '';
 $title="Student Module"; 
 $header=$view; 
switch ($view) {
    case 'list' :
        $content    = 'list.php';       
        break;

    case 'add' :
        $content    = 'add.php';        
        break;

    case 'edit' :
        $content    = 'edit.php';       
        break;
    case 'view' :
        $content    = 'view.php';       
        break;

    default :
        $content    = 'list.php';       
}
require_once ("../../theme/template.php");

?>
  
 <script type="text/javascript">
        $(document).ready(function() {
            var t = $('#tblstudent').DataTable( {
            "processing":true,
            "serverSide":true,
            "order":[],
            "ajax":{
              url:"<?php echo WEB_ROOT; ?>module/student/ajax.php",
              type:"POST"
            },
                "columnDefs": [ {
                    "searchable": true,
                    "orderable": true,
                    "targets": 1
                } ],
                //vertical scroll
                 "scrollY":        "400px",
                "scrollCollapse": true,
                //ordering start at column 2
               "order": [[ 2, 'asc' ]]
            } );

                t.on( 'order.dt search.dt', function () {
                t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i+1;
                } );
            } ).draw();
         
        });
    </script>
           <script type="text/javascript">
            $(function () {
                $('#reservationdate').datetimepicker({
                    format: 'L'
                });
            });
        </script>
        <script type="text/javascript">
          $(document).ready( function() {
      $(document).on('change', '.btn-file :file', function() {
    var input = $(this),
      label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.trigger('fileselect', [label]);
    });

    $('.btn-file :file').on('fileselect', function(event, label) {
        
        var input = $(this).parents('.input-group').find(':text'),
            log = label;
        
        if( input.length ) {
            input.val(log);
        } else {
            if( log ) alert(log);
        }
      
    });
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function (e) {
                $('#img-upload').attr('src', e.target.result);
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#imgInp").change(function(){
        readURL(this);
    });   
  });
        </script>
<script type="text/javascript">
  $(document).on('click', '.editEntry', function(){
    var uid = $(this).attr("UID");
    $.ajax({
      url:"<?php echo WEB_ROOT; ?>module/student/ajax.php",
      method:"POST",
      data:{UID:uid},
      dataType:"json",
      success:function(data)
      {
       $('#UID').val(data.UID);
       $('#IDNO1').val(data.IDNO);
       $('#FNAME1').val(data.FNAME);
       $('#MNAME1').val(data.MNAME);
       $('#LNAME1').val(data.LNAME);

       $('#EDIT_IDNO_TEXT').text(data.IDNO ? data.IDNO : '-');
       $('#EDIT_NAME_TEXT').text($.trim((data.FNAME || '') + ' ' + (data.MNAME || '') + ' ' + (data.LNAME || '')) || '-');

       var sex = data.SEX ? $.trim(data.SEX) : '';
       if (sex !== 'Male' && sex !== 'Female') { sex = ''; }
       $('#SEX1').val(sex);

       $('#BDAY1').val(data.BDAY ? data.BDAY : '');

       $('#BPLACE1').val(data.BPLACE ? data.BPLACE : '');
       $('#AGE1').val(data.AGE ? data.AGE : '');
       $('#NATIONALITY1').val(data.NATIONALITY ? data.NATIONALITY : '');
       $('#RELIGION1').val(data.RELIGION ? data.RELIGION : '');
       $('#CONTACT_NO1').val(data.CONTACT_NO ? data.CONTACT_NO : '');
       $('#HOME_ADD1').val(data.HOME_ADD ? data.HOME_ADD : '');
       $('#EMAIL1').val(data.EMAIL ? data.EMAIL : '');
       $('#currentPhoto').attr('src', data.photo ? "<?php echo WEB_ROOT; ?>module/student/" + data.photo : '').toggle(!!data.photo);

       $('#editEntry').modal('show');
      }
    })
  });
</script>

<script type="text/javascript">
$(document).on('click', '.registerEntry', function(){
  var uid = $(this).attr("UID");

  $.ajax({
    url:"<?php echo WEB_ROOT; ?>module/student/ajax.php",
    method:"POST",
    data:{act:'register_info', UID:uid},
    dataType:"json",
    success:function(data)
    {
      $('#R_SID').val(data.S_ID);
      $('#R_IDNO_TEXT').text(data.IDNO ? data.IDNO : '-');
      $('#R_NAME_TEXT').text(data.FULLNAME ? data.FULLNAME : '-');

      $('#R_SEMESTER').val('');
      $('#R_YEARLEVEL').val('');

      $('#R_SY').val(data.ACTIVE_SY ? data.ACTIVE_SY : '');
      $('#R_COURSE').val(data.COURSE_ID ? data.COURSE_ID : '');
      $('#R_CURRICULUM').val(data.ACTIVE_AY ? data.ACTIVE_AY : '');
      $('#R_CATEGORY').val(data.SUGGEST_CATEGORY ? data.SUGGEST_CATEGORY : 'New');

      $('#registerEntry').modal('show');
    },
    error:function()
    {
      alert('Could not load the student record.');
    }
  });
});
</script>

<script type="text/javascript">
/* ---------------------------------------------------------------
   Record Consultation - Doctor-facing. The Consult button on a
   student's row opens this modal with the student already
   identified; the doctor just answers the chief complaint (and
   optional notes), then it's submitted to the Doctor module's
   controller, which creates/reuses the Patient record and logs
   the Visit.
   --------------------------------------------------------------- */
$(document).on('click', '.consultEntry', function(){
  var sid = $(this).attr("S_ID");
  var name = $(this).attr("NAME");

  $('#C_SID').val(sid);
  $('#C_NAME_TEXT').text(name ? name : '-');
  $('#C_CHIEF_COMPLAINT').val('');
  $('#C_NOTES').val('');
  $('#C_RETURN_URL').val(window.location.href);

  $('#consultEntryModal').modal('show');
});
</script>