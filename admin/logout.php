<?php
require_once '../include/conn.php';

setcookie("h_userName", '',NULL,'/');
setcookie("h_passWord", '',NULL,'/');

session_unset();
session_destroy();

footer();

turnToPage("login.php");
?>