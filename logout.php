<?php
session_start();
$_SESSION = array();
session_destroy();
setcookie("user_info", "", time() - 3600, "/");
header("Location: signin.html");
exit();
?>
