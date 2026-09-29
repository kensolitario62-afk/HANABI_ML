<?php

require_once("../../include/initialize.php");

confirm_logged_in();

$view = isset($_GET['view']) ? $_GET['view'] : '';

switch ($view) {

    case 'view':

        $content = 'view.php';
        $title = 'Department Module';
        $header = 'Department';

        break;

    default:

        $content = 'list.php';
        $title = 'Department Module';
        $header = 'Department';

        break;
}

require_once("../../theme/template.php");

?>

<script>

$(document).ready(function () {

    /* =====================================================
       DEPARTMENT DATATABLE
       ===================================================== */

    var departmentTable = $('#tbldepartment').DataTable({

        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        pageLength: 10,

        ajax: {
            url: '<?php echo WEB_ROOT; ?>module/department/ajax.php',
            type: 'POST'
        },

        columns: [
            { width: '5%',  orderable: false, searchable: false, className: 'text-center' },
            { width: '30%' },
            { width: '45%' },
            { width: '20%', orderable: false, searchable: false, className: 'text-center' }
        ],

        order: [[1, 'asc']]

    });

    $(window).on('resize', function () {
        departmentTable.columns.adjust();
    });


    /* =====================================================
       EDIT DEPARTMENT
       ===================================================== */

    $(document).on('click', '.editDepartment', function (e) {

        e.preventDefault();

        var id = $(this).attr('data-id');

        if (!id) {
            Swal.fire('Oops', 'Invalid department ID.', 'error');
            return;
        }

        /* Clear previous values */
        $('#ID, #NAME1, #DESCRIPTION1').val('');

        $.ajax({

            url: '<?php echo WEB_ROOT; ?>module/department/ajax.php',
            type: 'POST',
            data: { ID: id },
            dataType: 'json',

            success: function (data) {

                if (data && data.status === 'success' && data.data) {

                    $('#ID').val(data.data.id);
                    $('#NAME1').val(data.data.name);
                    $('#DESCRIPTION1').val(data.data.description);

                    $('#editEntry').modal('show');

                } else {

                    Swal.fire('Oops', (data && data.message) || 'Unable to load department.', 'error');

                }

            },

            error: function (xhr) {

                console.log('Department AJAX Error:', xhr.responseText);

                Swal.fire('Oops', 'Unable to load department information.', 'error');

            }

        });

    });


    /* =====================================================
       DELETE DEPARTMENT
       ===================================================== */

    $(document).on('click', '.deleteDepartment', function (e) {

        e.preventDefault();

        var id = $(this).attr('data-id');

        if (!id) {
            Swal.fire('Oops', 'Invalid department ID.', 'error');
            return;
        }

        Swal.fire({

            title: 'Delete this department?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'

        }).then(function (result) {

            if (result.value) {

                window.location.href =
                    '<?php echo WEB_ROOT; ?>module/department/controller.php?action=delete&id='
                    + encodeURIComponent(id);

            }

        });

    });

});

</script>