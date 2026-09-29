<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;

header('Content-Type: application/json; charset=utf-8');

$act = isset($_POST['act']) ? $_POST['act'] : '';

/* -----------------------------------------------------------------
   One enrollment row, for the Sectioning/Edit modals.
   ----------------------------------------------------------------- */
if ($act === 'row') {

    $id = intval($_POST['ENROLLMENT_ID']);
    $output = array();

    $mydb->setQuery("SELECT e.*, 
            s.IDNO, s.LNAME, s.FNAME, s.MNAME, 
            c.COURSE_CODE, c.COURSE_NAME, 
            sy.SCHOOL_YEAR 
        FROM `tblenrollment` e 
        JOIN `tblstudent`    s  ON s.S_ID       = e.S_ID 
        JOIN `tblcourses`    c  ON c.COURSE_ID  = e.COURSE_ID 
        JOIN `tblschoolyear` sy ON sy.SY_ID     = e.SY_ID 
        WHERE e.ENROLLMENT_ID = '".$id."' LIMIT 1");

    foreach ($mydb->loadResultList() as $r) {
        $output['ENROLLMENT_ID'] = $r->ENROLLMENT_ID;
        $output['S_ID']          = $r->S_ID;
        $output['IDNO']          = $r->IDNO;
        $output['FULLNAME']      = trim($r->LNAME.', '.$r->FNAME.' '.$r->MNAME);
        $output['COURSE_ID']     = $r->COURSE_ID;
        $output['COURSE_TEXT']   = $r->COURSE_CODE.' - '.$r->COURSE_NAME;
        $output['SY_ID']         = $r->SY_ID;
        $output['SCHOOL_YEAR']   = $r->SCHOOL_YEAR;
        $output['SEMESTER']      = $r->SEMESTER;
        $output['YEAR_LEVEL']    = $r->YEAR_LEVEL;
        $output['CATEGORY']      = $r->CATEGORY;
        $output['CURRICULUM_YR'] = ($r->CURRICULUM_YR === null) ? '' : $r->CURRICULUM_YR;
        $output['SECTION_ID']    = ($r->SECTION_ID === null) ? '' : $r->SECTION_ID;
        $output['STATUS']        = $r->STATUS;
        $output['AMOUNT_DUE']    = $r->AMOUNT_DUE;
        $output['AMOUNT_PAID']   = $r->AMOUNT_PAID;

        $output['DATE_RESERVED'] = ($r->DATE_RESERVED === null || $r->DATE_RESERVED == '0000-00-00') ? '' : substr($r->DATE_RESERVED, 0, 10);
        $output['DATE_ENROLLED'] = ($r->DATE_ENROLLED === null || $r->DATE_ENROLLED == '0000-00-00') ? '' : substr($r->DATE_ENROLLED, 0, 10);
    }

    echo json_encode($output);
    exit;
}

/* -----------------------------------------------------------------
   Subjects available for the Assign modal: every subject that
   matches this enrollment's course, year level and semester, flagged
   with whether it is already assigned.
   ----------------------------------------------------------------- */
if ($act === 'assign_data') {

    $EID = intval($_POST['ENROLLMENT_ID']);
    $output = array('subjects' => array(), 'idno' => '', 'name' => '');

    $mydb->setQuery("SELECT e.COURSE_ID, e.YEAR_LEVEL, e.SEMESTER, s.IDNO, s.LNAME, s.FNAME, s.MNAME 
        FROM `tblenrollment` e 
        JOIN `tblstudent` s ON s.S_ID = e.S_ID 
        WHERE e.ENROLLMENT_ID = '".$EID."' LIMIT 1");
    $rows = $mydb->loadResultList();
    if (count($rows) < 1) { echo json_encode($output); exit; }
    $rec = $rows[0];

    $output['idno'] = $rec->IDNO;
    $output['name'] = trim($rec->LNAME.', '.$rec->FNAME.' '.$rec->MNAME);

    $mydb->setQuery("SELECT SUBJECT_ID FROM `tblenrollment_details` WHERE ENROLLMENT_ID = '".$EID."'");
    $already = array();
    foreach ($mydb->loadResultList() as $d) { $already[] = $d->SUBJECT_ID; }

    $mydb->setQuery("SELECT SUBJECT_ID, SUBJECT_CODE, SUBJECT_NAME, UNITS, PRICE_PER_UNIT 
        FROM `tblsubjects` 
        WHERE COURSE_ID = '".intval($rec->COURSE_ID)."' 
          AND YEAR_LEVEL = '".$mydb->escape_value($rec->YEAR_LEVEL)."' 
          AND SEMESTER   = '".$mydb->escape_value($rec->SEMESTER)."' 
        ORDER BY SUBJECT_CODE ASC");

    foreach ($mydb->loadResultList() as $subj) {
        $output['subjects'][] = array(
            'SUBJECT_ID'     => $subj->SUBJECT_ID,
            'SUBJECT_CODE'   => $subj->SUBJECT_CODE,
            'SUBJECT_NAME'   => $subj->SUBJECT_NAME,
            'UNITS'          => $subj->UNITS,
            'PRICE_PER_UNIT' => $subj->PRICE_PER_UNIT,
            'CHECKED'        => in_array($subj->SUBJECT_ID, $already)
        );
    }

    echo json_encode($output);
    exit;
}

/* -----------------------------------------------------------------
   Payment summary + history for the Payment modal.
   ----------------------------------------------------------------- */
if ($act === 'payment_data') {

    $EID = intval($_POST['ENROLLMENT_ID']);

    /* Same flat registration fee the controller enforces. */
    $regFee = 1000;

    $output = array(
        'idno' => '', 'name' => '',
        'reg_due' => $regFee, 'reg_paid' => 0, 'reg_balance' => $regFee,
        'due' => 0, 'paid' => 0, 'balance' => 0,
        'history' => array(), 'pending' => array()
    );

    $mydb->setQuery("SELECT e.AMOUNT_DUE, e.AMOUNT_PAID, e.REG_FEE_PAID, s.IDNO, s.LNAME, s.FNAME, s.MNAME 
        FROM `tblenrollment` e 
        JOIN `tblstudent` s ON s.S_ID = e.S_ID 
        WHERE e.ENROLLMENT_ID = '".$EID."' LIMIT 1");
    $rows = $mydb->loadResultList();
    if (!$rows || count($rows) < 1) { echo json_encode($output); exit; }
    $rec = $rows[0];

    $regPaid = (float)$rec->REG_FEE_PAID;

    $output['idno']        = $rec->IDNO;
    $output['name']        = trim($rec->LNAME.', '.$rec->FNAME.' '.$rec->MNAME);
    $output['reg_paid']    = $regPaid;
    $output['reg_balance'] = max(0, $regFee - $regPaid);
    $output['due']         = (float)$rec->AMOUNT_DUE;
    $output['paid']        = (float)$rec->AMOUNT_PAID;
    $output['balance']     = max(0, $rec->AMOUNT_DUE - $rec->AMOUNT_PAID);

    /* Payments already applied to the balance (cashier entries + approved online ones). */
    $mydb->setQuery("SELECT AMOUNT, PAYMENT_TYPE, OR_NUMBER, CASHIER, DATE_PAID 
        FROM `tblpayments` 
        WHERE ENROLLMENT_ID = '".$EID."' 
          AND (PAY_STATUS IS NULL OR PAY_STATUS NOT IN ('Pending', 'Rejected')) 
        ORDER BY PAYMENT_ID DESC");
    $hist = $mydb->loadResultList();
    if ($hist) {
        foreach ($hist as $p) {
            $output['history'][] = array(
                'DATE'    => $p->DATE_PAID,
                'TYPE'    => $p->PAYMENT_TYPE,
                'AMOUNT'  => $p->AMOUNT,
                'OR'      => $p->OR_NUMBER,
                'CASHIER' => $p->CASHIER
            );
        }
    }

    /* Online submissions still waiting for the registrar. */
    $mydb->setQuery("SELECT PAYMENT_ID, AMOUNT, PAYMENT_TYPE, OR_NUMBER, PROOF_FILE, DATE_PAID 
        FROM `tblpayments` 
        WHERE ENROLLMENT_ID = '".$EID."' AND PAY_STATUS = 'Pending' 
        ORDER BY PAYMENT_ID ASC");
    $pend = $mydb->loadResultList();
    if ($pend) {
        foreach ($pend as $p) {
            $output['pending'][] = array(
                'PAYMENT_ID' => $p->PAYMENT_ID,
                'DATE'       => $p->DATE_PAID,
                'TYPE'       => $p->PAYMENT_TYPE,
                'AMOUNT'     => $p->AMOUNT,
                'REFERENCE'  => $p->OR_NUMBER,
                'PROOF'      => $p->PROOF_FILE
            );
        }
    }

    echo json_encode($output);
    exit;
}

/* -----------------------------------------------------------------
   Sections available for a given course + school year.
   ----------------------------------------------------------------- */
if ($act === 'sections') {

    $course = intval($_POST['COURSE_ID']);
    $sy     = intval($_POST['SY_ID']);
    $rows   = array();

    if ($course > 0 && $sy > 0) {
        $mydb->setQuery("SELECT SECTION_ID, SECTION_NAME, YEAR_LEVEL 
            FROM `tblsections` 
            WHERE COURSE_ID = '".$course."' AND SY_ID = '".$sy."' 
            ORDER BY YEAR_LEVEL ASC, SECTION_NAME ASC");
        foreach ($mydb->loadResultList() as $row) {
            $rows[] = array(
                'SECTION_ID'   => $row->SECTION_ID,
                'SECTION_NAME' => $row->SECTION_NAME,
                'YEAR_LEVEL'   => $row->YEAR_LEVEL
            );
        }
    }

    echo json_encode($rows);
    exit;
}

/* -----------------------------------------------------------------
   Registrar-wide queue of online payments still awaiting verification.
   Returns a plain array of payment rows (not the DataTables shape) -
   this is what index.php's loadPendingQueue() expects.
   ----------------------------------------------------------------- */
if ($act === 'pending_payments') {

    $output = array();

    $mydb->setQuery("SELECT p.PAYMENT_ID, p.DATE_PAID, p.AMOUNT, p.PAYMENT_TYPE, 
            p.OR_NUMBER, p.PROOF_FILE, 
            s.IDNO, s.LNAME, s.FNAME, s.MNAME 
        FROM `tblpayments` p 
        JOIN `tblenrollment` e ON e.ENROLLMENT_ID = p.ENROLLMENT_ID 
        JOIN `tblstudent` s ON s.S_ID = e.S_ID 
        WHERE p.SOURCE = 'Online' AND p.PAY_STATUS = 'Pending' 
        ORDER BY p.DATE_PAID ASC, p.PAYMENT_ID ASC");

    foreach ($mydb->loadResultList() as $p) {
        $output[] = array(
            'PAYMENT_ID' => $p->PAYMENT_ID,
            'DATE'       => $p->DATE_PAID,
            'IDNO'       => $p->IDNO,
            'NAME'       => trim($p->LNAME.', '.$p->FNAME.' '.$p->MNAME),
            'TYPE'       => $p->PAYMENT_TYPE,
            'AMOUNT'     => $p->AMOUNT,
            'REFERENCE'  => $p->OR_NUMBER,
            'PROOF'      => $p->PROOF_FILE
        );
    }

    echo json_encode($output);
    exit;
}

/* -----------------------------------------------------------------
   DataTables list
   ----------------------------------------------------------------- */
$base = "FROM `tblenrollment` e 
    JOIN `tblstudent`     s   ON s.S_ID      = e.S_ID 
    JOIN `tblcourses`     c   ON c.COURSE_ID = e.COURSE_ID 
    JOIN `tblschoolyear`  sy  ON sy.SY_ID    = e.SY_ID 
    LEFT JOIN `tblsections` sec ON sec.SECTION_ID = e.SECTION_ID ";

$where = " WHERE 1=1 ";

$filter = isset($_POST['status_filter']) ? trim($_POST['status_filter']) : '';
if ($filter !== '') {
    $where .= " AND e.STATUS = '".$mydb->escape_value($filter)."' ";
}

if (isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '') {
    $s = $mydb->escape_value($_POST["search"]["value"]);
    $where .= " AND (s.LNAME LIKE '%".$s."%' 
                 OR s.FNAME LIKE '%".$s."%' 
                 OR s.IDNO  LIKE '%".$s."%' 
                 OR c.COURSE_CODE LIKE '%".$s."%' 
                 OR e.STATUS LIKE '%".$s."%') ";
}

$orderCols = array(
    0 => 'e.ENROLLMENT_ID',
    1 => 's.IDNO',
    2 => 's.LNAME',
    3 => 'c.COURSE_CODE',
    4 => 'sy.SCHOOL_YEAR',
    5 => 'e.SEMESTER',
    6 => 'e.YEAR_LEVEL',
    7 => 'sec.SECTION_NAME',
    8 => 'e.AMOUNT_DUE',
    9 => 'e.AMOUNT_PAID',
    10 => 'e.STATUS'
);

$orderBy = " ORDER BY e.ENROLLMENT_ID DESC ";
if (isset($_POST['order'][0]['column'])) {
    $ci = intval($_POST['order'][0]['column']);
    $dir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) == 'asc') ? 'ASC' : 'DESC';
    if (isset($orderCols[$ci])) {
        $orderBy = " ORDER BY ".$orderCols[$ci]." ".$dir." ";
    }
}

$limit = "";
if (isset($_POST['length']) && $_POST['length'] != -1) {
    $limit = " LIMIT ".intval($_POST['start']).", ".intval($_POST['length'])." ";
}

$select = "SELECT e.ENROLLMENT_ID, e.YEAR_LEVEL, e.SEMESTER, e.CATEGORY, e.CURRICULUM_YR, 
        e.DATE_RESERVED, e.DATE_ENROLLED, e.STATUS, e.AMOUNT_DUE, e.AMOUNT_PAID, 
        s.IDNO, s.LNAME, s.FNAME, s.MNAME, 
        c.COURSE_CODE, sy.SCHOOL_YEAR, sec.SECTION_NAME ";

$mydb->setQuery($select.$base.$where.$orderBy.$limit);
$rows = $mydb->loadResultList();

if (!$rows) {
    $rows = array();
}

$mydb->setQuery("SELECT COUNT(*) AS total ".$base.$where);
$fRow = $mydb->loadSingleResult();
$filtered = $fRow ? intval($fRow->total) : 0;

$mydb->setQuery("SELECT COUNT(*) AS total FROM `tblenrollment`");
$tRow = $mydb->loadSingleResult();
$total = $tRow ? intval($tRow->total) : 0;

$data = array();
$i = isset($_POST['start']) ? intval($_POST['start']) + 1 : 1;

foreach ($rows as $r) {

    $pillMap = array(
        'Registered' => 'st-grey',
        'Assigned'   => 'st-blue',
        'Sectioned'  => 'st-indigo',
        'Paid'       => 'st-purple',
        'Enrolled'   => 'st-green',
        'Dropped'    => 'st-red',
        'Completed'  => 'st-dark'
    );
    $pillClass = isset($pillMap[$r->STATUS]) ? $pillMap[$r->STATUS] : 'st-grey';

    $section = ($r->SECTION_NAME === null || $r->SECTION_NAME == '')
        ? '<span class="text-muted">Not sectioned</span>'
        : htmlspecialchars($r->SECTION_NAME);

    /* Only one "next step" button is shown at a time, matching the
       stage the record is currently sitting at. */
    $stageBtn = '';
    if ($r->STATUS == 'Registered') {
        $stageBtn = '<button type="button" EID="'.$r->ENROLLMENT_ID.'" class="btn btn-info btn-xs doAssign" title="Assign Subjects"><span class="fa fa-book"></span> Assign</button>';
    } elseif ($r->STATUS == 'Assigned') {
        $stageBtn = '<button type="button" EID="'.$r->ENROLLMENT_ID.'" class="btn btn-primary btn-xs doSectioning" title="Sectioning"><span class="fa fa-chalkboard"></span> Section</button>';
    } elseif ($r->STATUS == 'Sectioned') {
        $stageBtn = '<button type="button" EID="'.$r->ENROLLMENT_ID.'" class="btn btn-warning btn-xs doPayment" title="Payment"><span class="fa fa-cash-register"></span> Payment</button>';
    } elseif ($r->STATUS == 'Paid') {
        $stageBtn = '<button type="button" EID="'.$r->ENROLLMENT_ID.'" class="btn btn-success btn-xs doEnroll" title="Enroll"><span class="fa fa-user-check"></span> Enroll</button>';
    } elseif ($r->STATUS == 'Enrolled') {
        $stageBtn = '<a href="print.php?id='.$r->ENROLLMENT_ID.'" target="_blank" class="btn btn-success btn-xs" title="Print Registration Form"><span class="fa fa-print"></span> Print</a>';
    }

    /* Assign and Payment stay reachable even later, so a subject can be
       corrected or an additional payment logged after the main step. */
    $secondaryBtns = '';
    if ($r->STATUS == 'Sectioned' || $r->STATUS == 'Paid' || $r->STATUS == 'Enrolled') {
        $secondaryBtns .= '<button type="button" EID="'.$r->ENROLLMENT_ID.'" class="btn btn-outline-info btn-xs doAssign" title="Edit Subjects"><span class="fa fa-book"></span></button>';
        $secondaryBtns .= '<button type="button" EID="'.$r->ENROLLMENT_ID.'" class="btn btn-outline-warning btn-xs doPayment" title="Payment"><span class="fa fa-cash-register"></span></button>';
    }

    $actions = '
        <div class="text-nowrap">
            '.$stageBtn.'
            '.$secondaryBtns.'
            <button type="button" EID="'.$r->ENROLLMENT_ID.'" class="btn btn-warning btn-xs editEnrollment" title="Edit"><span class="fa fa-edit"></span></button>
            <a href="controller.php?action=delete&id='.$r->ENROLLMENT_ID.'" class="btn btn-danger btn-xs confirmLink" data-msg="Delete this enrollment record?" title="Delete"><span class="fa fa-trash"></span></a>
        </div>';

    $data[] = array(
        $i,
        htmlspecialchars($r->IDNO),
        htmlspecialchars(trim($r->LNAME.', '.$r->FNAME.' '.$r->MNAME)),
        htmlspecialchars($r->COURSE_CODE),
        htmlspecialchars($r->SCHOOL_YEAR),
        htmlspecialchars($r->SEMESTER),
        htmlspecialchars($r->YEAR_LEVEL),
        $section,
        '&#8369;'.number_format($r->AMOUNT_DUE, 2),
        '&#8369;'.number_format($r->AMOUNT_PAID, 2),
        '<span class="ss-st '.$pillClass.'">'.htmlspecialchars($r->STATUS).'</span>',
        $actions
    );
    $i++;
}

echo json_encode(array(
    'data'            => $data,
    'recordsTotal'    => $total,
    'recordsFiltered' => $filtered
));
?>