<?php

require_once("include/initialize.php");

if (!isset($_SESSION['UID'])) {
    redirect(WEB_ROOT . "login.php");
}

$title = "Messenger";

$content = "module/messenger/index.php";

require_once("theme/template.php");

?>