<?php
session_start();
$_SESSION = array();
session_destroy();
setcookie("ro_info", "", time() - 3600, "/");
header("Location: ROsignin.html");
exit();
?>
