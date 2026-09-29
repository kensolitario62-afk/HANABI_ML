<?php

require_once("../../include/initialize.php");

$view = isset($_GET['view']) ? $_GET['view'] : '';

$title = "Instructor Module";
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

<script>

$(document).ready(function() {

    /* =====================================================
       INSTRUCTOR DATATABLE
       ===================================================== */

    var instructorTable = $('#tblinstructor').DataTable({

        "processing": true,
        "serverSide": true,
        "autoWidth": false,
        "responsive": false,

        "ajax": {
            "url": "ajax.php",
            "type": "POST"
        },

        "pageLength": 10,

        "lengthMenu": [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
        ],

        "columns": [
            { "width": "5%",  "orderable": false, "searchable": false },
            { "width": "18%" },
            { "width": "25%" },
            { "width": "32%" },
            { "width": "20%", "orderable": false, "searchable": false }
        ],

        "order": [[2, "asc"]]

    });

    $(window).on('resize', function() {
        instructorTable.columns.adjust();
    });


    /* =====================================================
       EDIT INSTRUCTOR

       Loads the record from the server (ajax.php, action=get)
       so the values are exact - not scraped from the table text.
       ===================================================== */

    $(document).on('click', '.editEntry', function(e) {

        e.preventDefault();

        var id = $(this).attr('data-id');

        if (!id) {
            Swal.fire('Oops', 'Invalid instructor ID.', 'error');
            return;
        }

        $.ajax({

            url: "ajax.php",
            type: "POST",
            dataType: "json",
            data: { action: 'get', ID: id },

            success: function(response) {

                if (!response.success) {
                    Swal.fire('Oops', response.message || 'Unable to load instructor.', 'error');
                    return;
                }

                $('#ID').val(response.data.id);
                $('#INSTRUCTOR_ID1').val(response.data.instructor_id);
                $('#NAME1').val(response.data.name);
                $('#DESCRIPTION1').val(response.data.description);

                $('#editEntry').modal('show');

            },

            error: function(xhr) {
                console.error(xhr.responseText);
                Swal.fire('Oops', 'Unable to load instructor.', 'error');
            }

        });

    });


    /* Clear the Edit modal when it closes */

    $('#editEntry').on('hidden.bs.modal', function() {
        $('#ID, #INSTRUCTOR_ID1, #NAME1, #DESCRIPTION1').val('');
    });


    /* =====================================================
       DELETE INSTRUCTOR
       ===================================================== */

    $(document).on('click', '.deleteEntry', function(e) {

        e.preventDefault();

        var id = $(this).attr('data-id');

        if (!id) {
            Swal.fire('Oops', 'Invalid instructor ID.', 'error');
            return;
        }

        Swal.fire({

            title: 'Delete this instructor?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'

        }).then(function(result) {

            if (result.value) {
                window.location.href = "controller.php?action=delete&id=" + id;
            }

        });

    });

});

</script>