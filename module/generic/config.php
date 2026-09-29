<?php
// Solitario Solutions

$GENERIC_TABLES = array(
	'alumni_details' => array(
		'title' => 'Alumni Details',
		'icon'  => 'fa-id-card',
	),
	'tblschoolyear' => array(
		'title' => 'School Year',
		'icon'  => 'fa-calendar-alt',
	),
	'tblsections' => array(
		'title'    => 'Sections',
		'icon'     => 'fa-chalkboard',
		/* Sections is the one generic table where a plain edit form
		   isn't enough - you also want to see who's actually assigned
		   to it. has_view turns on the View button (generic_ajax.php)
		   and the ?view=view routing (index.php) for this table only. */
		'has_view' => true,
	),
	/* tblenrollment is NOT listed here on purpose. It now has its own
	   module (module/enrollment) because the two-stage Reserve ->
	   Sectioning flow needs custom screens the generic builder cannot
	   produce. Adding it back here would give you a second, conflicting
	   way to edit the same table. */
	'tblenrollment_details' => array(
		'title' => 'Enrollment Details',
		'icon'  => 'fa-list-alt',
	),
	'tblgrades' => array(
		'title' => 'Grades',
		'icon'  => 'fa-graduation-cap',
	),
);

/* column name => [table to pull options from, PK column, SQL expression for the label] */
$GENERIC_FK_MAP = array(
	'COURSE_ID'  => array('table' => 'tblcourses',   'pk' => 'COURSE_ID',  'label' => "CONCAT(COURSE_CODE, ' - ', COURSE_NAME)"),
	'SECTION_ID' => array('table' => 'tblsections',  'pk' => 'SECTION_ID', 'label' => 'SECTION_NAME'),
	'SY_ID'      => array('table' => 'tblschoolyear','pk' => 'SY_ID',      'label' => 'SCHOOL_YEAR'),
	'S_ID'       => array('table' => 'tblstudent',   'pk' => 'S_ID',       'label' => "CONCAT(LNAME, ', ', FNAME)"),
	'SUBJECT_ID' => array('table' => 'tblsubjects',  'pk' => 'SUBJECT_ID', 'label' => 'SUBJECT_NAME'),
	'ENROLLMENT_ID' => array('table' => 'tblenrollment', 'pk' => 'ENROLLMENT_ID', 'label' => 'ENROLLMENT_ID'),
	'UID'        => array('table' => 'tblusers',     'pk' => 'UID',        'label' => 'USERNAME'),
	'TYPEID'     => array('table' => 'tblusertype',  'pk' => 'TYPEID',     'label' => 'USERTYPE'),
	'AddedBy'    => array('table' => 'tblusers',     'pk' => 'UID',        'label' => 'DISPLAYNAME'),
);

/* STATUS means different things in different tables, so the generic form
   must not assume Active/Inactive everywhere. Keyed by table name. */
$GENERIC_STATUS_CHOICES = array(
	'tblenrollment' => array('Enrolled', 'Dropped', 'Completed'),
);

/* column name (exact, case-insensitive) => fixed dropdown choices, used
   instead of a plain text box in the Add/Edit forms. */
$GENERIC_FIELD_CHOICES = array(
	'YEAR_LEVEL' => array('1st Year', '2nd Year', '3rd Year', '4th Year'),
	'SEMESTER'   => array('1st Semester', '2nd Semester', 'Summer'),
	'REMARKS'    => array('Passed', 'Failed', 'Incomplete', 'Dropped'),
	'STATUS'     => array('Active', 'Inactive'),
);

/* substring (case-insensitive) => nicer label to show instead of the
   auto-generated one. Used e.g. so an ADVISER column in Sections is
   labeled "Program Head" everywhere in the UI. Checked AFTER the exact
   map below, since a substring match is more likely to catch something
   unintended. */
$GENERIC_LABEL_OVERRIDES = array(
	'ADVIS' => 'Program Head',
);

/* column name (exact, case-insensitive) => nicer label. Checked first,
   so common ID/FK columns read the same way the hand-built modules
   (Course, Subject, Enrollment...) already label them, instead of the
   raw "Course Id" / "Sy Id" auto-generated version. */
$GENERIC_EXACT_LABEL_OVERRIDES = array(
	'COURSE_ID'     => 'Course',
	'SECTION_ID'    => 'Section',
	'SY_ID'         => 'School Year',
	'S_ID'          => 'Student',
	'SUBJECT_ID'    => 'Subject',
	'ENROLLMENT_ID' => 'Enrollment',
	'UID'           => 'User',
	'TYPEID'        => 'User Type',
	'ADDEDBY'       => 'Added By',
);

/* Returns the config for a table if (and only if) it is whitelisted -
   protects the generic module from being pointed at an arbitrary table
   name via the URL. */
function generic_table_config($tableKey) {
	global $GENERIC_TABLES;
	return isset($GENERIC_TABLES[$tableKey]) ? $GENERIC_TABLES[$tableKey] : null;
}

/* Nice display label for a column, exact match first (COURSE_ID ->
   "Course"), falling back to the substring map, then a generic
   Title Case of the raw field name. */
function generic_nice_label($field) {
	global $GENERIC_LABEL_OVERRIDES, $GENERIC_EXACT_LABEL_OVERRIDES;
	$key = strtoupper($field);
	if (isset($GENERIC_EXACT_LABEL_OVERRIDES[$key])) {
		return $GENERIC_EXACT_LABEL_OVERRIDES[$key];
	}
	foreach ($GENERIC_LABEL_OVERRIDES as $pattern => $niceLabel) {
		if (stripos($field, $pattern) !== false) { return $niceLabel; }
	}
	return ucwords(strtolower(str_replace('_', ' ', $field)));
}

/* The columns a list/form should actually show: every DESC() column
   except the auto-increment primary key. Hand-built modules (Course,
   Subject, ...) never expose their own raw ID column either, so the
   generic module now matches that instead of showing a redundant ID
   column - which, for a self-referencing FK column (e.g. tblschoolyear's
   own SY_ID), was also rendering as a duplicate of the real label
   column since the FK-label lookup pointed the ID back at itself. */
function generic_visible_columns($rec, $columns) {
	$visible = array();
	foreach ($columns as $c) {
		if ($c->Field == $rec->pk && $rec->isAutoIncrement($rec->pk)) { continue; }
		$visible[] = $c;
	}
	return $visible;
}

/* Resolves a raw FK id into its human-readable label (e.g. COURSE_ID 3
   -> "BSIT - Bachelor of Science in Information Technology"), with a
   small in-request cache so the same id isn't looked up twice across
   different rows. Shared by generic_ajax.php (list rows) and view.php
   (detail page) so both display FK columns the same way.

   Skips the lookup when the FK's target table IS the table currently
   being viewed - e.g. tblschoolyear's own SY_ID column pointing back at
   tblschoolyear.SCHOOL_YEAR - since that's the row's own id, not a
   reference to a different record. */
function generic_fk_label($field, $value, $currentTable) {
	global $GENERIC_FK_MAP, $mydb;
	static $cache = array();
	if ($value === null || $value === '') { return ''; }
	if (!isset($GENERIC_FK_MAP[$field])) { return $value; }
	$fk = $GENERIC_FK_MAP[$field];
	if ($fk['table'] === $currentTable) { return $value; }
	$cacheKey = $field.':'.$value;
	if (isset($cache[$cacheKey])) { return $cache[$cacheKey]; }
	if (!$mydb->tableExists($fk['table'])) { return $value; }
	$mydb->setQuery("SELECT ".$fk['label']." as fk_label FROM `".$fk['table']."` WHERE `".$fk['pk']."` = '".$mydb->escape_value($value)."' LIMIT 1");
	$row = $mydb->loadSingleResult();
	$label = ($row && isset($row->fk_label)) ? $row->fk_label : $value;
	$cache[$cacheKey] = $label;
	return $label;
}
?>