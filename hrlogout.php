<?php
session_start();
$_SESSION = array();
session_destroy();
setcookie('hr_info', '', time() - 3600, '/');
header("Location: hrsignin.html");
exit();
?>
