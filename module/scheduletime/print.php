<?php

require_once("../../include/initialize.php");

confirm_logged_in();

global $mydb;


$id = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;


$mydb->setQuery("
    SELECT
        id,
        time_start,
        time_end,
        description
    FROM tblscheduletime
    WHERE id = " . $id . "
    LIMIT 1
");


$schedule_time = $mydb->loadSingleResult();


if (!$schedule_time) {

    echo "Schedule time not found.";

    exit;

}

$school = array(
    'name'    => 'COLEGIO DE SANTA RITA DE SAN CARLOS, INC.',
    'address' => 'Atienza Avenue, San Carlos City, Negros Occidental',
    'logo'    => WEB_ROOT . 'csr.png'
);

function pi_e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <title>Schedule Time Information</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>

        @page { size: A4 portrait; margin: 15mm; }

        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body {
            font-family: Cambria, "Times New Roman", Times, serif;
            color: #000;
            margin: 0;
            padding: 30px;
            font-size: 14px;
        }

        .sheet { max-width: 800px; margin: 0 auto; }

        .toolbar { text-align: center; margin-bottom: 22px; font-family: Arial, Helvetica, sans-serif; }
        .toolbar button, .toolbar a {
            display: inline-block;
            font-size: 14px; font-weight: bold;
            padding: 10px 26px; margin: 0 4px;
            border: 0; border-radius: 30px; cursor: pointer;
            color: #fff; text-decoration: none;
        }
        .toolbar .p { background: linear-gradient(120deg, #8b0000, #c0392b); box-shadow: 0 3px 10px rgba(0,0,0,.25); }
        .toolbar .b { background: #6c757d; }

        .letterhead { display: flex; align-items: center; }
        .letterhead .logo { width: 80px; height: 80px; object-fit: contain; flex: 0 0 80px; }
        .letterhead .text { flex: 1; text-align: center; padding-right: 80px; line-height: 1.35; }
        .school-name { font-size: 19px; font-weight: bold; letter-spacing: .3px; }
        .school-line { font-size: 12px; }
        .rule { height: 4px; background: linear-gradient(90deg, #8b0000, #c0392b, #8b0000); border-radius: 4px; margin: 10px 0 4px; }

        .title { text-align: center; margin: 18px 0 20px; }
        .title h2 { margin: 0; font-size: 22px; letter-spacing: 2px; color: #8b0000; text-transform: uppercase; }
        .title p { margin: 3px 0 0; font-size: 12px; color: #555; }

        table.info { width: 100%; border-collapse: collapse; border-radius: 8px; overflow: hidden; }
        table.info th, table.info td { border: 1px solid #b9b9b9; padding: 11px 12px; text-align: left; vertical-align: top; }
        table.info th { width: 28%; background: #f6e3e1; color: #5c0000; }

        .signatures { display: flex; justify-content: space-between; margin-top: 70px; }
        .sig { width: 42%; text-align: center; }
        .sig .line { border-bottom: 1px solid #333; height: 34px; margin-bottom: 5px; }
        .sig .label { font-size: 12px; color: #8b0000; font-weight: bold; }

        .generated { margin-top: 36px; padding-top: 8px; border-top: 1px dashed #bbb; text-align: center; font-size: 10px; color: #555; }

        @media print {
            body { padding: 0; }
            .toolbar { display: none; }
            .signatures { page-break-inside: avoid; }
        }

    </style>

</head>

<body>

<div class="sheet">

    <div class="toolbar">
        <button type="button" class="p" onclick="window.print();">&#128424; Print</button>
        <a class="b" href="javascript:window.close();">Close</a>
    </div>

    <div class="letterhead">
        <img class="logo" src="<?php echo pi_e($school['logo']); ?>" alt="" onerror="this.style.visibility='hidden';">
        <div class="text">
            <div class="school-name"><?php echo pi_e($school['name']); ?></div>
            <div class="school-line"><?php echo pi_e($school['address']); ?></div>
        </div>
    </div>
    <div class="rule"></div>

    <div class="title">
        <h2>Schedule Time Information</h2>
        <p>Date Printed: <?php echo date('F j, Y'); ?></p>
    </div>

    <?php
    $st = date("h:i A", strtotime($schedule_time->time_start));
    $en = date("h:i A", strtotime($schedule_time->time_end));

    $diff = strtotime($schedule_time->time_end) - strtotime($schedule_time->time_start);
    $dur  = '-';
    if ($diff > 0) {
        $h = floor($diff / 3600);
        $m = floor(($diff % 3600) / 60);
        $parts = array();
        if ($h > 0) { $parts[] = $h . ' hr' . ($h > 1 ? 's' : ''); }
        if ($m > 0) { $parts[] = $m . ' min' . ($m > 1 ? 's' : ''); }
        $dur = implode(' ', $parts);
    }
    ?>
    <table class="info">
        <tr>
            <th>Schedule Time ID</th>
            <td><?php echo (int)$schedule_time->id; ?></td>
        </tr>
        <tr>
            <th>Time Start</th>
            <td><?php echo pi_e($st); ?></td>
        </tr>
        <tr>
            <th>Time End</th>
            <td><?php echo pi_e($en); ?></td>
        </tr>
        <tr>
            <th>Duration</th>
            <td><?php echo pi_e($dur); ?></td>
        </tr>
        <tr>
            <th>Description</th>
            <td><?php echo nl2br(pi_e($schedule_time->description)); ?></td>
        </tr>
    </table>

    <div class="signatures">
        <div class="sig"><div class="line"></div><div class="label">Prepared By</div></div>
        <div class="sig"><div class="line"></div><div class="label">Approved By</div></div>
    </div>

    <div class="generated">Generated from the School Information System</div>

</div>

<script>
    window.addEventListener('load', function () { window.print(); });
</script>

</body>
</html>