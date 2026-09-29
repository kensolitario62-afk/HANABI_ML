<?php
require_once("../../include/initialize.php");
if (!isset($_SESSION['UID'])){
    redirect(WEB_ROOT."login.php");
}

global $mydb;
$mydb->setQuery("SELECT * FROM `tblusers` WHERE `UID` = '".intval($_SESSION['UID'])."' LIMIT 1");
$me = $mydb->loadSingleResult();

$title = "My Profile";
$content = 'myprofile_content.php';
require_once("../../theme/template.php");
?>