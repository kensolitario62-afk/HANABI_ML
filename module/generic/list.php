<?php
// Solitario Solutions

require_once(__DIR__."/config.php");
require_once(__DIR__."/style.php");

$table = isset($_GET['t']) ? $_GET['t'] : '';
$cfg   = generic_table_config($table);
if (!$cfg) { redirect(WEB_ROOT."module/error/index.php?view=list"); exit; }

$rec     = new GenericRecord($table);
$columns = generic_visible_columns($rec, $rec->columns());

/* fixed dropdown choices for a column (YEAR_LEVEL, SEMESTER, REMARKS, ...) */
function generic_choice_options($field) {
	global $GENERIC_FIELD_CHOICES;
	$key = strtoupper($field);
	return isset($GENERIC_FIELD_CHOICES[$key]) ? $GENERIC_FIELD_CHOICES[$key] : null;
}

/* pull [id => label] options for a foreign-key-looking column */
function generic_fk_options($field) {
	global $GENERIC_FK_MAP, $mydb;
	if (!isset($GENERIC_FK_MAP[$field])) { return null; }
	$fk = $GENERIC_FK_MAP[$field];
	if (!$mydb->tableExists($fk['table'])) { return null; }
	$mydb->setQuery("SELECT `".$fk['pk']."` as fk_id, ".$fk['label']." as fk_label FROM `".$fk['table']."` ORDER BY fk_id DESC");
	$rows = $mydb->loadResultList();
	$out = array();
	foreach ($rows as $r) { $out[$r->fk_id] = $r->fk_label; }
	return $out;
}

function generic_input_type($col) {
	$t = strtolower($col->Type);
	if (stripos($col->Field, 'PASSWORD') !== false) { return 'password'; }
	if (strpos($t, 'date') === 0 || strpos($t, 'timestamp') === 0) { return 'date'; }
	if (strpos($t, 'int') !== false || strpos($t, 'decimal') !== false || strpos($t, 'float') !== false || strpos($t, 'double') !== false) { return 'number'; }
	if (strpos($t, 'text') !== false) { return 'textarea'; }
	return 'text';
}

/* Picks a small icon for a form field from its name / type. */
function generic_field_icon($col)
{
	$f = strtoupper($col->Field);
	$t = strtolower($col->Type);

	if (strpos($f, 'PASSWORD') !== false)                          { return 'fa-lock'; }
	if (strpos($f, 'EMAIL') !== false)                             { return 'fa-envelope'; }
	if (strpos($f, 'CONTACT') !== false || strpos($f, 'PHONE') !== false) { return 'fa-phone'; }
	if (strpos($f, 'ADDR') !== false)                              { return 'fa-home'; }
	if (strpos($f, 'NAME') !== false)                              { return 'fa-user'; }
	if ($f === 'STATUS')                                           { return 'fa-toggle-on'; }
	if ($f === 'YEAR_LEVEL')                                       { return 'fa-level-up'; }
	if ($f === 'SEMESTER')                                         { return 'fa-flag'; }
	if ($f === 'SY_ID' || strpos($f, 'SCHOOL_YEAR') !== false)     { return 'fa-calendar-o'; }
	if ($f === 'COURSE_ID')                                        { return 'fa-book'; }
	if ($f === 'SECTION_ID')                                       { return 'fa-users'; }
	if ($f === 'SUBJECT_ID')                                       { return 'fa-bookmark'; }
	if ($f === 'S_ID')                                             { return 'fa-user-graduate'; }
	if (strpos($f, 'REMARK') !== false || strpos($f, 'GRADE') !== false) { return 'fa-star'; }
	if (strpos($t, 'date') === 0 || strpos($t, 'timestamp') === 0) { return 'fa-calendar'; }
	if (strpos($t, 'int') !== false || strpos($t, 'decimal') !== false || strpos($t, 'float') !== false || strpos($t, 'double') !== false) { return 'fa-hashtag'; }
	if (strpos($t, 'text') !== false)                              { return 'fa-align-left'; }
	return 'fa-pencil';
}

/* Renders one form-group for the Add or Edit modal. $idPrefix is prepended
   to the DOM id (edit_ for the edit modal, empty for add) so both modals can
   live on the same page without id clashes; the `name` stays the raw
   column name in both so the controller can read $_POST[name] directly. */
function generic_render_field($col, $rec, $idPrefix = '') {
	$field = $col->Field;
	if ($field == $rec->pk && $rec->isAutoIncrement($field)) { return; }

	$domId = $idPrefix.$field;
	$requiredAttr = (strtoupper($col->Null) === 'NO') ? ' required' : '';
	$type = generic_input_type($col);
	$icon = generic_field_icon($col);
	$niceLabel = htmlspecialchars(generic_nice_label($field));

	/* Long text takes the full row; everything else sits two per row. */
	$colClass = ($type === 'textarea') ? 'col-sm-12' : 'col-sm-6';

	echo '<div class="'.$colClass.'"><div class="form-group">';
	echo '<label for="'.$domId.'">'.$niceLabel.'</label>';
	echo '<div class="ss-input"><i class="fa '.$icon.'"></i>';

	if (strtoupper($field) === 'STATUS') {
		global $GENERIC_STATUS_CHOICES, $table;
		$statusOpts = isset($GENERIC_STATUS_CHOICES[$table])
			? $GENERIC_STATUS_CHOICES[$table]
			: array('Active', 'Inactive');
		echo '<select class="form-control" name="'.$field.'" id="'.$domId.'"'.$requiredAttr.'>';
		foreach ($statusOpts as $opt) {
			echo '<option value="'.htmlspecialchars($opt).'">'.htmlspecialchars($opt).'</option>';
		}
		echo '</select>';

	} elseif (($choices = generic_choice_options($field)) !== null) {
		echo '<select class="form-control" name="'.$field.'" id="'.$domId.'"'.$requiredAttr.'>';
		echo '<option value="">Select '.$niceLabel.'</option>';
		foreach ($choices as $choice) {
			echo '<option value="'.htmlspecialchars($choice).'">'.htmlspecialchars($choice).'</option>';
		}
		echo '</select>';

	} elseif (($fkOptions = generic_fk_options($field)) !== null) {
		echo '<select class="form-control" name="'.$field.'" id="'.$domId.'"'.$requiredAttr.'>';
		echo '<option value="">Select '.$niceLabel.'</option>';
		foreach ($fkOptions as $optId => $optLabel) {
			echo '<option value="'.htmlspecialchars($optId).'">'.htmlspecialchars($optLabel).'</option>';
		}
		echo '</select>';

	} elseif ($type === 'textarea') {
		echo '<textarea class="form-control" name="'.$field.'" id="'.$domId.'" rows="3" placeholder="'.$niceLabel.'"'.$requiredAttr.'></textarea>';

	} else {
		echo '<input type="'.$type.'" class="form-control" name="'.$field.'" id="'.$domId.'" placeholder="'.$niceLabel.'"'.$requiredAttr.'>';
	}

	echo '</div></div></div>';
}
?>
<section class="content">

  <div class="container-fluid">
    <?php check_message(); ?>
    <div class="row">
      <div class="col-12">

        <div class="card ss-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fa <?php echo htmlspecialchars($cfg['icon']); ?>"></i>List of <?php echo htmlspecialchars($cfg['title']); ?></h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <table id="tblgeneric" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th width="5%">#</th>
                <?php foreach ($columns as $col): ?>
                  <th><?php echo htmlspecialchars(generic_nice_label($col->Field)); ?></th>
                <?php endforeach; ?>
                <th>Action</th>
              </tr>
              </thead>
              <tbody></tbody>
              <tfoot></tfoot>
            </table>
            <div class="ss-actions mt-3">
              <button type="button" class="btn btn-add" data-toggle="modal" data-target="#AddNewEntry"><i class="fa fa-plus"></i> Add New</button>
            </div>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div>
  <!-- /.container-fluid -->
</section>

<!-----START of Add Form---->
<div class="modal fade ss-modal" id="AddNewEntry">
  <div class="modal-dialog modal-lg">
    <form action="controller.php?action=add&amp;t=<?php echo urlencode($table); ?>" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-plus"></i></span>Add New <?php echo htmlspecialchars($cfg['title']); ?></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12"><div class="ss-group"><i class="fa <?php echo htmlspecialchars($cfg['icon']); ?>"></i><?php echo htmlspecialchars($cfg['title']); ?> Details</div></div>
            <?php foreach ($columns as $col) { generic_render_field($col, $rec, ''); } ?>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" name="save"><i class="fa fa-save"></i> Save changes</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </form>
  </div>
  <!-- /.modal-dialog -->
</div>
<!-----End of Add Form---->

<!-----Start of Edit Form---->
<div class="modal fade ss-modal" id="editEntry">
  <div class="modal-dialog modal-lg">
    <form action="controller.php?action=edit&amp;t=<?php echo urlencode($table); ?>" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-edit"></i></span>Modify <?php echo htmlspecialchars($cfg['title']); ?></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <input type="hidden" name="record_pk" id="record_pk" value="">
            <div class="col-12"><div class="ss-group"><i class="fa <?php echo htmlspecialchars($cfg['icon']); ?>"></i><?php echo htmlspecialchars($cfg['title']); ?> Details</div></div>
            <?php foreach ($columns as $col) { generic_render_field($col, $rec, 'edit_'); } ?>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" name="edit"><i class="fa fa-save"></i> Save changes</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </form>
  </div>
  <!-- /.modal-dialog -->
</div>
<!-----End of Edit Form---->