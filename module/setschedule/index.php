<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;

$view = isset($_GET['view']) ? $_GET['view'] : '';

$title = "Set Schedule Module";

$header = $view;

$content = ($view == 'view') ? 'view.php' : 'list.php';

require_once("../../theme/template.php");

?>


<?php if ($content == 'list.php'): ?>

<script>

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | DATATABLE
    |--------------------------------------------------------------------------
    */

    $('#tblsetschedule').DataTable({

        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,

        ajax: {
            url: "<?php echo WEB_ROOT; ?>module/setschedule/ajax.php",
            type: "POST",
            dataSrc: function (json) {
                if (json.error) {
                    console.error(json.error);
                }
                return json.data;
            }
        },

        columns: [
            { data: 0, width: "4%", orderable: false },
            { data: 1, width: "13%" },
            { data: 2, width: "9%" },
            { data: 3, width: "9%" },
            { data: 4, width: "23%" },
            { data: 5, width: "6%" },
            { data: 6, width: "10%" },
            { data: 7, width: "13%" },
            { data: 8, width: "10%" },
            { data: 9, width: "10%", orderable: false, searchable: false }
        ],

        order: [[1, 'asc']],

        pageLength: 10,

        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ]

    });


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    | Loads the record, then hands Course / Section / School Year / Subject /
    | Semester to SetScheduleForm.fillEdit() (defined in list.php) so the
    | dependent dropdowns are built in the right order.
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.editEntry', function (e) {

        e.preventDefault();

        var id = $(this).attr('ID');

        if (!id) {
            alert('Invalid schedule ID.');
            return;
        }

        $.ajax({

            url: "<?php echo WEB_ROOT; ?>module/setschedule/ajax.php",
            type: "POST",
            dataType: "json",
            data: { ID: id },

            success: function (response) {

                if (response.status !== 'success') {
                    alert(response.message || 'Unable to load schedule.');
                    return;
                }

                var data = response.data;

                $('#EDIT_ID').val(data.id);
                $('#EDIT_DEPARTMENT_ID').val(data.department_id);
                $('#EDIT_CLASSROOM_ID').val(data.classroom_id);
                $('#EDIT_DAY_ID').val(data.day_id);
                $('#EDIT_TIME_ID').val(data.time_id);
                $('#EDIT_INSTRUCTOR_ID').val(data.instructor_id ? data.instructor_id : '');

                if (window.SetScheduleForm) {
                    window.SetScheduleForm.fillEdit(data);
                }

                $('#editEntry').modal('show');

            },

            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Unable to load schedule.');
            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.deleteEntry', function (e) {

        e.preventDefault();

        var id = $(this).attr('ID');

        if (!id) {
            return;
        }

        Swal.fire({

            title: 'Delete this schedule?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'

        }).then(function (result) {

            if (result.value) {

                window.location.href =
                    "<?php echo WEB_ROOT; ?>module/setschedule/controller.php?action=delete&id=" + id;

            }

        });

    });

});

</script>

<?php endif; ?>