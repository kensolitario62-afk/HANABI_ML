<?php
// Solitario Solutions

require_once("../../include/initialize.php");
require_once("config.php");

$table = isset($_GET['t']) ? $_GET['t'] : '';
$cfg   = generic_table_config($table);

if (!$cfg) {
    // unknown / not-whitelisted table -> reuse the app's existing 404 page
    redirect(WEB_ROOT."module/error/index.php?view=list");
    exit;
}

$view   = isset($_GET['view']) ? $_GET['view'] : '';
$title  = $cfg['title'];
$header = $view;

// Only tables flagged has_view (see config.php) actually have a
// view.php to show - everything else just stays on the list, the same
// as before this was added.
$content = ($view === 'view' && !empty($cfg['has_view'])) ? 'view.php' : 'list.php';

require_once("../../theme/template.php");
?>

<?php if ($content === 'list.php'): ?>
<script type="text/javascript">
    $(document).ready(function() {
        var t = $('#tblgeneric').DataTable({
            "processing": true,
            "serverSide": true,
            "autoWidth": false,
            "order": [],
            "ajax": {
                url: "<?php echo WEB_ROOT; ?>module/generic/generic_ajax.php?t=<?php echo urlencode($table); ?>",
                type: "POST",
                dataSrc: function (json) {
                    if (json.error) {
                        console.error(json.error);
                        return [];
                    }
                    return json.data;
                }
            },
            "columnDefs": [
                { "targets": 0, "orderable": false, "searchable": false, "className": "text-center" },
                { "targets": -1, "orderable": false, "searchable": false, "className": "text-center" }
            ],
            "scrollX": true
        });

        $(window).on('resize', function () {
            t.columns.adjust();
        });
    });
</script>

<script type="text/javascript">
    $(document).on('click', '.editEntry', function() {
        var recId = $(this).attr('data-id');
        $.ajax({
            url: "<?php echo WEB_ROOT; ?>module/generic/generic_ajax.php?t=<?php echo urlencode($table); ?>",
            method: "POST",
            data: {record_id: recId},
            dataType: "json",
            success: function(data) {
                $.each(data, function(key, value) {
                    $('#edit_' + key).val(value);
                });
                $('#record_pk').val(recId);
                $('#editEntry').modal('show');
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                Swal.fire('Oops', 'Unable to load the record.', 'error');
            }
        });
    });
</script>

<script type="text/javascript">
    $(document).on('click', '.deleteEntry', function() {
        var recId = $(this).attr('data-id');
        Swal.fire({
            title: 'Delete this record?',
            text: "This action cannot be undone. Are you sure you want to delete it?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.value) {
                window.location.href = "<?php echo WEB_ROOT; ?>module/generic/controller.php?action=delete&t=<?php echo urlencode($table); ?>&id=" + recId;
            }
        });
    });
</script>
<?php endif; ?>