<?php

require_once("../../include/initialize.php");

confirm_logged_in();

$view = (isset($_GET['view']) && $_GET['view'] != '')
    ? $_GET['view']
    : '';

$title = "Schedule Day Module";
$header = $view;

switch ($view) {

    case 'list':
        $content = 'list.php';
        break;

    case 'view':
        $content = 'view.php';
        break;

    default:
        $content = 'list.php';
        break;
}

require_once("../../theme/template.php");

?>

<script type="text/javascript">

$(document).ready(function() {

    var t = $('#tblscheduleday').DataTable({

        "processing": true,

        "serverSide": true,

        "autoWidth": false,

        "ajax": {
            "url": "<?php echo WEB_ROOT; ?>module/scheduleday/ajax.php",
            "type": "POST"
        },

        "columnDefs": [

            { "searchable": false, "orderable": false, "targets": 0, "className": "text-center" },

            { "searchable": false, "orderable": false, "targets": 3, "className": "text-center" }

        ],

        "order": [
            [1, "asc"]
        ]

    });

    $(window).on('resize', function() {
        t.columns.adjust();
    });


    /*
    |--------------------------------------------------------------------------
    | EDIT SCHEDULE DAY
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.editEntry', function() {

        var ID = $(this).attr('ID');

        $.ajax({

            url: "<?php echo WEB_ROOT; ?>module/scheduleday/ajax.php",

            type: "POST",

            data: {
                ID: ID
            },

            dataType: "json",

            success: function(data) {

                if (!data || data.error) {

                    Swal.fire(
                        'Error',
                        (data && data.error) || 'Schedule day not found.',
                        'error'
                    );

                    return;
                }


                $('#ID').val(data.ID);

                $('#NAME1').val(data.NAME);

                $('#DESCRIPTION1').val(data.DESCRIPTION);

                $('#editEntry').modal('show');

            },

            error: function(xhr) {

                console.log(xhr.responseText);

                Swal.fire(
                    'Error',
                    'Unable to load schedule day.',
                    'error'
                );

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | DELETE SCHEDULE DAY
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.deleteEntry', function() {

        var ID = $(this).attr('ID');

        Swal.fire({

            title: 'Delete Schedule Day?',

            text: 'This action cannot be undone.',

            icon: 'warning',

            showCancelButton: true,

            confirmButtonText: 'Yes, delete it',

            cancelButtonText: 'Cancel'

        }).then(function(result) {

            if (result.value) {

                window.location.href =
                    "<?php echo WEB_ROOT; ?>module/scheduleday/controller.php?action=delete&id="
                    + encodeURIComponent(ID);

            }

        });

    });

});

</script>