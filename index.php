<?php 

//EJB SOLUTIONS - BATUTO 2025

require_once("include/initialize.php");
   if (!isset($_SESSION['UID'])){
      redirect(WEB_ROOT."login.php");
 //   header("Location: login.php");

     }else{
      
     } 

/* Doctor and Registrar accounts each land on their own dashboard
   instead of the regular alumni/enrollment overview. */
$accountType = isset($_SESSION['TYPE']) ? $_SESSION['TYPE'] : '';

if ($accountType == 'Doctor') {
    $defaultTitle   = 'Doctor Dashboard';
    $defaultContent = 'doctor_home.php';
} elseif ($accountType == 'Registrar') {
    $defaultTitle   = 'Registrar Dashboard';
    $defaultContent = 'registrar_home.php';
} else {
    $defaultTitle   = 'Home';
    $defaultContent = 'home.php';
}

$title = $defaultTitle;
$content = $defaultContent;
$view = (isset($_GET['page']) && $_GET['page'] != '') ? $_GET['page'] : '';
switch ($view) {
  case '1' :
         $title = $defaultTitle; 
     $content = $defaultContent; 
    
    break;  
  default :
    $title = $defaultTitle;
    $content = $defaultContent; 
}
require_once("theme/template.php");
?>