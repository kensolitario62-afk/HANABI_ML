<?php
// Solitario Solutions
// Generic "View" page. Only reachable for tables flagged has_view in
// config.php (currently just Sections) - index.php already redirects
// anything else back to list.php before this file is ever loaded.
// Built off the same DESC()-driven column list the rest of this module
// uses, so any other table can opt in later just by flagging
// has_view => true, without needing its own bespoke view page.

require_once(__DIR__."/config.php");
require_once(__DIR__."/style.php");

$table = isset($_GET['t']) ? $_GET['t'] : '';
$cfg   = generic_table_config($table);
if (!$cfg || empty($cfg['has_view'])) { redirect(WEB_ROOT."module/generic/index.php?t=".urlencode($table)); exit; }

$rec     = new GenericRecord($table);
$columns = generic_visible_columns($rec, $rec->columns());

$id     = isset($_GET['id']) ? $_GET['id'] : '';
$record = ($id !== '') ? $rec->single($id) : null;

if (!function_exists('generic_field_icon')) {
  function generic_field_icon($col)
  {
    $f = strtoupper($col->Field);
    $t = strtolower($col->Type);
    if (strpos($f, 'PASSWORD') !== false) { return 'fa-lock'; }
    if (strpos($f, 'EMAIL') !== false)    { return 'fa-envelope'; }
    if (strpos($f, 'NAME') !== false)     { return 'fa-user'; }
    if ($f === 'STATUS')                  { return 'fa-toggle-on'; }
    if ($f === 'YEAR_LEVEL')              { return 'fa-level-up'; }
    if ($f === 'SEMESTER')                { return 'fa-flag'; }
    if ($f === 'SY_ID')                   { return 'fa-calendar-o'; }
    if ($f === 'COURSE_ID')               { return 'fa-book'; }
    if ($f === 'SECTION_ID')              { return 'fa-users'; }
    if ($f === 'SUBJECT_ID')              { return 'fa-bookmark'; }
    if ($f === 'S_ID')                    { return 'fa-user-graduate'; }
    if (strpos($t, 'date') === 0 || strpos($t, 'timestamp') === 0) { return 'fa-calendar'; }
    if (strpos($t, 'int') !== false || strpos($t, 'decimal') !== false) { return 'fa-hashtag'; }
    if (strpos($t, 'text') !== false)     { return 'fa-align-left'; }
    return 'fa-pencil';
  }
}

/* One icon tile in the info grid. */
function gv_tile($label, $value, $icon)
{
  echo '<div class="ss-tile"><div><i class="fa ' . $icon . '"></i>'
    . '<div><span>' . htmlspecialchars($label) . '</span><b>' . $value . '</b></div></div></div>';
}

/* Title shown on the hero card = the first visible column's label value. */
$heroTitle = '';
if ($record && count($columns) > 0) {
  $firstField = $columns[0]->Field;
  $heroTitle  = generic_fk_label($firstField, isset($record->$firstField) ? $record->$firstField : '', $table);
}

$students = array();

if ($record && $table === 'tblsections') {
  global $mydb;
  $mydb->setQuery("SELECT s.IDNO, s.LNAME, s.FNAME, s.MNAME, e.YEAR_LEVEL, e.SEMESTER, e.STATUS
    FROM `tblenrollment` e
    JOIN `tblstudent` s ON s.S_ID = e.S_ID
    WHERE e.SECTION_ID = '".$mydb->escape_value($id)."'
    ORDER BY s.LNAME ASC, s.FNAME ASC");
  $found = $mydb->loadResultList();
  if ($found) { $students = $found; }
}
?>
<section class="content">
  <div class="container-fluid">

  <?php if (!$record): ?>
    <div class="alert alert-warning">
      Record not found. Please go back to the <a href="<?php echo WEB_ROOT; ?>module/generic/index.php?t=<?php echo urlencode($table); ?>">list</a>.
    </div>

  <?php else: ?>

    <div class="row">

      <!-- ================= LEFT ================= -->
      <div class="col-md-4">
        <div class="card ss-hero">
          <div class="ss-cover">
            <div style="width:84px;height:84px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:2.4rem;">
              <i class="fa <?php echo htmlspecialchars($cfg['icon']); ?>"></i>
            </div>
            <h3><?php echo htmlspecialchars((string)$heroTitle); ?></h3>
            <small><?php echo htmlspecialchars($cfg['title']); ?></small>
          </div>
          <div class="ss-body">
            <?php if ($table === 'tblsections'): ?>
              <div class="ss-stat"><i class="fa fa-users"></i><div><span>Students</span><b><?php echo count($students); ?></b></div></div>
            <?php endif; ?>
            <a href="<?php echo WEB_ROOT; ?>module/generic/index.php?t=<?php echo urlencode($table); ?>" class="btn ss-btn main btn-block mt-3">
              <i class="fa fa-arrow-left"></i> Back to List
            </a>
          </div>
        </div>
      </div>

      <!-- ================= RIGHT ================= -->
      <div class="col-md-8">

        <div class="card ss-panel">
          <div class="card-header">
            <h3 class="card-title"><i class="fa <?php echo htmlspecialchars($cfg['icon']); ?> mr-1"></i> <?php echo htmlspecialchars($cfg['title']); ?> Details</h3>
          </div>
          <div class="card-body">
            <div class="ss-info">
              <?php foreach ($columns as $col):
                  $field = $col->Field;
                  $rawValue = isset($record->$field) ? $record->$field : '';
                  $displayValue = generic_fk_label($field, $rawValue, $table);
                  $shown = ($displayValue !== '' && $displayValue !== null)
                      ? nl2br(htmlspecialchars((string)$displayValue))
                      : '<span class="text-muted">-</span>';
                  gv_tile(generic_nice_label($field), $shown, generic_field_icon($col));
              endforeach; ?>
            </div>
          </div>
        </div>

        <?php if ($table === 'tblsections'): ?>
        <div class="card ss-panel">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-user-graduate mr-1"></i> Students in this Section</h3>
          </div>
          <div class="card-body p-0">
            <?php if (count($students) < 1): ?>
              <div class="ss-empty"><i class="fa fa-inbox"></i>No students are currently assigned to this section.</div>
            <?php else: ?>
              <div class="table-responsive">
              <table class="table table-sm mb-0 ss-mini">
                <thead>
                  <tr>
                    <th>ID No.</th>
                    <th>Name</th>
                    <th>Year Level</th>
                    <th>Semester</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $s): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($s->IDNO); ?></strong></td>
                    <td><?php echo htmlspecialchars(trim($s->LNAME.', '.$s->FNAME.' '.$s->MNAME)); ?></td>
                    <td><?php echo htmlspecialchars($s->YEAR_LEVEL); ?></td>
                    <td><?php echo htmlspecialchars($s->SEMESTER); ?></td>
                    <td><span class="ss-pill"><?php echo htmlspecialchars($s->STATUS); ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              </div>
              <div class="p-2 pl-3 text-muted small">Total: <?php echo count($students); ?> student(s)</div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->

  <?php endif; ?>

  </div>
</section>