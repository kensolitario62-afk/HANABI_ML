<?php
/* Shared helpers for the Messenger module (used by index.php and ajax.php). */
if (session_status() === PHP_SESSION_NONE) session_start();

/* ---- adjust these two blocks to your project ---- */
const MS_DB_HOST = 'localhost', MS_DB_NAME = 'alumni_db', MS_DB_USER = 'root', MS_DB_PASS = '';
function ms_identity() {                       // who is logged in? [type, id]
    if (!empty($_SESSION['UID']))  return ['U', (int)$_SESSION['UID']];   // staff / doctor / admin
    if (!empty($_SESSION['S_ID'])) return ['S', (int)$_SESSION['S_ID']];  // student portal
    return null;
}
const MS_UPLOAD_DIR = __DIR__ . '/files/';     // must be writable
function ms_base_url() {                       // web path of this folder, worked out automatically
    $root = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $dir  = str_replace('\\', '/', __DIR__);
    return '/' . ltrim(substr($dir, strlen($root)), '/');
}
define('MS_UPLOAD_URL', ms_base_url() . '/files/');
/* -------------------------------------------------- */

function db() {
    static $p;
    if (!$p) $p = new PDO('mysql:host=' . MS_DB_HOST . ';dbname=' . MS_DB_NAME . ';charset=utf8mb4', MS_DB_USER, MS_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    return $p;
}
function q($sql, $args = []) { $s = db()->prepare($sql); $s->execute($args); return $s; }

function ms_person($t, $id) {
    if ($t === 'U') $r = q("SELECT DISPLAYNAME name, TYPE role FROM tblusers WHERE UID=?", [$id])->fetch();
    else            $r = q("SELECT CONCAT(FNAME,' ',LNAME) name, 'Student' role FROM tblstudent WHERE S_ID=?", [$id])->fetch();
    return $r ?: ['name' => 'Unknown', 'role' => ''];
}