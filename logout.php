<?php
session_start();

$url = "login.php";

if(isset($_GET["timeout"]) && $_GET["timeout"] == true){
    $url .= "?timeout=".$_GET["timeout"];
}

unset($_SESSION["user_id"]);
unset($_SESSION['user_email']);
unset($_SESSION['login_timeout']);

header("Location: $url");