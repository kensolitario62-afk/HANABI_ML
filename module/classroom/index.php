<?php

require_once("../../include/initialize.php");

confirm_logged_in();

$view = isset($_GET['view']) ? $_GET['view'] : '';

switch ($view) {

    case 'view':
        $content = 'view.php';
        $title = 'Classroom Module';
        $header = 'Classroom';
        break;

    default:
        $content = 'list.php';
        $title = 'Classroom Module';
        $header = 'Classroom';
        break;
}

require_once("../../theme/template.php");

?>

<script>

$(document).ready(function () {

    /* =====================================================
       CLASSROOM DATATABLE
       ===================================================== */

    var classroomTable = $('#tblclassroom').DataTable({

        processing: true,
        serverSide: true,
        autoWidth: false,

        ajax: {

            url: '<?php echo WEB_ROOT; ?>module/classroom/ajax.php',
            type: 'POST',

            dataSrc: function (json) {

                if (json.error) {
                    console.error('Classroom AJAX Error:', json.error);
                    return [];
                }

                return json.data;
            },

            error: function (xhr, error, thrown) {
                console.error('Classroom AJAX Error', xhr.status, xhr.responseText, error, thrown);
            }

        },

        columns: [
            { data: 0, width: '5%',  orderable: false, searchable: false, className: 'text-center' },
            { data: 1, width: '30%' },
            { data: 2, width: '45%' },
            { data: 3, width: '20%', orderable: false, searchable: false, className: 'text-center' }
        ],

        order: [[1, 'asc']],

        pageLength: 10,

        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ]

    });

    $(window).on('resize', function () {
        classroomTable.columns.adjust();
    });


    /* =====================================================
       EDIT CLASSROOM
       ===================================================== */

    $(document).on('click', '.editEntry', function (e) {

        e.preventDefault();

        var id = $(this).attr('data-id');

        if (!id) {
            Swal.fire('Oops', 'Invalid classroom ID.', 'error');
            return;
        }

        $.ajax({

            url: '<?php echo WEB_ROOT; ?>module/classroom/ajax.php',
            type: 'POST',
            dataType: 'json',
            data: { ID: id },

            success: function (response) {

                if (response.status === 'success') {

                    $('#ID').val(response.data.id);
                    $('#NAME1').val(response.data.name);
                    $('#DESCRIPTION1').val(response.data.description);

                    $('#editClassroomModal').modal('show');

                } else {

                    Swal.fire('Oops', response.message || 'Unable to load classroom.', 'error');

                }

            },

            error: function (xhr) {

                console.error('Edit AJAX Error:', xhr.responseText);

                Swal.fire('Oops', 'Unable to load classroom.', 'error');

            }

        });

    });


    /* =====================================================
       DELETE CLASSROOM
       ===================================================== */

    $(document).on('click', '.deleteEntry', function (e) {

        e.preventDefault();

        var id = $(this).attr('data-id');

        if (!id) {
            Swal.fire('Oops', 'Invalid classroom ID.', 'error');
            return;
        }

        Swal.fire({

            title: 'Delete this classroom?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'

        }).then(function (result) {

            if (result.value) {

                window.location.href =
                    '<?php echo WEB_ROOT; ?>module/classroom/controller.php?action=delete&id='
                    + encodeURIComponent(id);

            }

        });

    });

});

</script>