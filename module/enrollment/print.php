<?php
// Printable Registration Form - only makes sense for an ENROLLED record,
// so it pulls real subjects, units, and the actual enrollment date.
require_once("../../include/initialize.php");
if (!isset($_SESSION['UID'])) {
	redirect(WEB_ROOT."login.php");
}
global $mydb;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$mydb->setQuery("SELECT e.ENROLLMENT_ID, e.YEAR_LEVEL, e.SEMESTER, e.DATE_ENROLLED, e.STATUS,
		s.IDNO, s.FNAME, s.MNAME, s.LNAME, s.SEX, s.CONTACT_NO, s.EMAIL, s.HOME_ADD,
		c.COURSE_CODE, c.COURSE_NAME,
		sy.SCHOOL_YEAR,
		sec.SECTION_NAME
	FROM `tblenrollment` e
	JOIN `tblstudent`    s   ON s.S_ID      = e.S_ID
	JOIN `tblcourses`    c   ON c.COURSE_ID = e.COURSE_ID
	JOIN `tblschoolyear` sy  ON sy.SY_ID    = e.SY_ID
	LEFT JOIN `tblsections` sec ON sec.SECTION_ID = e.SECTION_ID
	WHERE e.ENROLLMENT_ID = '".$id."' LIMIT 1");
$rows = $mydb->loadResultList();

if (count($rows) < 1) {
	die("Enrollment record not found.");
}
$rec = $rows[0];

$mydb->setQuery("SELECT sub.SUBJECT_CODE, sub.SUBJECT_NAME, sub.UNITS
	FROM `tblenrollment_details` d
	JOIN `tblsubjects` sub ON sub.SUBJECT_ID = d.SUBJECT_ID
	WHERE d.ENROLLMENT_ID = '".$id."'
	ORDER BY sub.SUBJECT_CODE ASC");
$subjects = $mydb->loadResultList();

$totalUnits = 0;
foreach ($subjects as $s) { $totalUnits += $s->UNITS; }

$fullFirstMid = trim($rec->FNAME.' '.$rec->MNAME);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Registration Form - <?php echo htmlspecialchars($rec->IDNO); ?></title>
<style>
	@page {
		size: letter;
		margin: 0.4in;
	}
	@media print {
		.no-print { display: none !important; }
		body { margin: 0; padding: 0; }
	}
	body {
		font-family: 'Times New Roman', Times, serif;
		color: #111;
		max-width: 850px;
		margin: 20px auto;
		padding: 20px;
	}
	.no-print { text-align: center; margin-bottom: 20px; }
	.no-print button {
		padding: 8px 20px; font-size: 14px; cursor: pointer;
		background: #8B0000; color: #fff; border: none; border-radius: 4px;
	}
	.header-row { display: flex; align-items: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 6px; }
	.header-row img { width: 65px; height: 65px; margin-right: 15px; }
	.header-text { text-align: center; flex: 1; }
	.header-text h1 { font-size: 18px; margin: 0; letter-spacing: 0.5px; }
	.header-text p { font-size: 12px; margin: 1px 0; }
	.form-title { text-align: center; margin: 6px 0 12px; }
	.form-title .dept { font-weight: bold; font-size: 14px; }
	.form-title .form { font-weight: bold; font-size: 14px; color: #2255aa; }

	.meta-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px; }
	.section { border-bottom: 1px solid #000; padding-bottom: 4px; margin-bottom: 6px; }
	.name-grid { display: flex; }
	.name-grid div { flex: 1; }
	.name-grid .val { font-weight: bold; font-size: 14px; }
	.name-grid .lbl { font-style: italic; font-size: 11px; color: #333; }

	.term-grid { display: flex; margin-top: 4px; }
	.term-grid div { flex: 1; }
	.term-grid .val { font-weight: bold; }
	.term-grid .lbl { font-style: italic; font-size: 11px; color: #333; }

	table.subjects { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 13px; }
	table.subjects th { text-align: left; border-bottom: 1px solid #2255aa; color: #2255aa; padding: 3px 6px; font-size: 12px; }
	table.subjects td { padding: 2px 6px; }
	table.subjects td.unit, table.subjects th.unit { text-align: right; }
	.total-row { text-align: right; border-top: 1px solid #000; padding-top: 4px; margin-top: 4px; font-style: italic; font-size: 13px; }

	.sign-area { display: flex; justify-content: space-between; margin-top: 25px; font-size: 12px; page-break-inside: avoid; }
	.sign-col { width: 45%; }
	.sign-col p.role-label { font-weight: bold; margin-bottom: 18px; }
	.sign-line { border-top: 1px solid #000; padding-top: 3px; text-align: center; }
	.sign-name { font-weight: bold; text-align: center; }
	.sign-title { text-align: center; color: #2255aa; font-size: 11px; margin-top: 1px; }
	.sign-block { margin-bottom: 20px; }
</style>

<style>
	/* ---------- refreshed look (school identity and form content unchanged) ---------- */
	* { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

	.no-print { font-family: Arial, Helvetica, sans-serif; }
	.no-print button, .no-print a {
		display: inline-block;
		padding: 10px 28px; font-size: 14px; font-weight: bold; cursor: pointer;
		border: 0; border-radius: 30px; color: #fff; text-decoration: none; margin: 0 4px;
	}
	.no-print button { background: linear-gradient(120deg, #8b0000, #c0392b); box-shadow: 0 3px 10px rgba(0,0,0,.25); }
	.no-print a { background: #6c757d; }

	.header-row { border-bottom: 0; padding-bottom: 6px; margin-bottom: 0; }
	.rule { height: 4px; background: linear-gradient(90deg, #8b0000, #c0392b, #8b0000); border-radius: 4px; margin: 4px 0 8px; }

	.form-title .form { color: #8b0000; letter-spacing: 2px; font-size: 16px; }

	.section { border-bottom: 1px solid #b9b9b9; }
	table.subjects th { background: #f6e3e1; color: #5c0000; border-bottom: 2px solid #8b0000; text-transform: uppercase; letter-spacing: .3px; }
	table.subjects tbody tr:nth-child(even) td { background: #fafafa; }
	.total-row { border-top: 2px solid #8b0000; color: #5c0000; }

	.sign-title { color: #8b0000; }
	.sign-line { border-top: 1px solid #333; }
</style>
</head>
<body>

	<div class="no-print">
		<button onclick="window.print()">&#128424; Print This Form</button>
		<a href="javascript:window.close();">Close</a>
	</div>

	<div class="header-row">
		<img src="<?php echo WEB_ROOT; ?>cpsu.png" alt="" onerror="this.style.display='none';">
		<div class="header-text">
			<h1>CENTRAL PHILIPPINES STATE UNIVERSITY</h1>
			<p>San Carlos City, Negros Occidental</p>
			<p>Email: cpsu_main@cpsu.edu.ph</p>
		</div>
	</div>

	<div class="rule"></div>

	<div class="form-title">
		<div class="dept">COLLEGE DEPARTMENT</div>
		<div class="form">REGISTRATION FORM</div>
	</div>

	<div class="meta-row">
		<div>
			<strong>Student ID No.:</strong><br>
			<?php echo htmlspecialchars($rec->IDNO); ?>
		</div>
		<div>
			<strong>Date Printed:</strong> <?php echo date('d/m/Y'); ?>
		</div>
	</div>

	<div class="section">
		<div class="name-grid">
			<div><div class="val"><?php echo htmlspecialchars($rec->LNAME); ?></div><div class="lbl">(Surname)</div></div>
			<div><div class="val"><?php echo htmlspecialchars($rec->FNAME); ?></div><div class="lbl">(First Name)</div></div>
			<div><div class="val"><?php echo htmlspecialchars($rec->MNAME); ?></div><div class="lbl">(Middle Name)</div></div>
			<div><div class="val"><?php echo htmlspecialchars(strtoupper($rec->SEX)); ?></div><div class="lbl">&nbsp;</div></div>
		</div>
	</div>

	<div class="section">
		<div class="val"><?php echo htmlspecialchars($rec->HOME_ADD); ?></div>
		<div class="lbl">(Address)</div>
	</div>

	<div class="section">
		<div class="term-grid">
			<div><div class="val"><?php echo htmlspecialchars($rec->CONTACT_NO); ?></div><div class="lbl">(Contact)</div></div>
			<div><div class="val"><?php echo htmlspecialchars($rec->SCHOOL_YEAR); ?></div><div class="lbl">(Academic Year)</div></div>
			<div><div class="val"><?php echo htmlspecialchars($rec->SEMESTER); ?></div><div class="lbl">(Semester)</div></div>
			<div><div class="val"><?php echo htmlspecialchars($rec->COURSE_CODE); ?></div><div class="lbl">(Course)</div></div>
			<div><div class="val"><?php echo htmlspecialchars($rec->YEAR_LEVEL); ?></div><div class="lbl">(Year Level)</div></div>
		</div>
	</div>

	<table class="subjects">
		<thead>
			<tr>
				<th style="width:20%;">Course Code</th>
				<th>Course Description</th>
				<th class="unit" style="width:10%;">Unit</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($subjects as $s) : ?>
			<tr>
				<td><?php echo htmlspecialchars($s->SUBJECT_CODE); ?></td>
				<td><?php echo htmlspecialchars($s->SUBJECT_NAME); ?></td>
				<td class="unit"><?php echo htmlspecialchars($s->UNITS); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div class="total-row">(Total Units) <strong><?php echo $totalUnits; ?></strong></div>

	<div class="sign-area">
		<div class="sign-col">
			<p class="role-label">Approved:</p>
			<div class="sign-block">
				<div class="sign-line"></div>
				<div class="sign-name">ERICK JASON J. BATUTO, MAT-MT, MIT, LPT</div>
				<div class="sign-title">PROGRAM HEAD</div>
			</div>
			<div class="sign-block">
				<div class="sign-line"></div>
				<div class="sign-name">SR. JOY A. DULA, AR, RGC, LPT</div>
				<div class="sign-title">GUIDANCE COUNSELOR</div>
			</div>
		</div>
		<div class="sign-col">
			<p class="role-label">Validated:</p>
			<div class="sign-block">
				<div class="sign-line"></div>
				<div class="sign-name">SR. MARIA SOLIDAD V. TORRES, AR, MAED, LPT</div>
				<div class="sign-title">REGISTRAR</div>
			</div>
			<div class="sign-block">
				<div class="sign-line"></div>
				<div class="sign-name">SR. MA. CORAZON J. BARROCA, AR</div>
				<div class="sign-title">TREASURER</div>
			</div>
			<div class="sign-block">
				<div class="sign-line"></div>
				<div class="sign-name">&nbsp;</div>
				<div class="sign-title">STUDENT SIGNATURE</div>
			</div>
		</div>
	</div>

	<?php /* Intentionally no auto-generated "ENROLLED" stamp here -
	         this space is left blank for the registrar to apply the
	         actual physical seal by hand once the form is printed. */ ?>

</body>
</html>