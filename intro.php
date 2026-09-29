<?php
require_once("include/initialize.php");
if (isset($_SESSION['UID'])) {
  redirect("index.php");
}
// The intro video now plays inside login.php so it can blend straight into the login screen.
header("Location: " . WEB_ROOT . "login.php?intro=1");
exit;