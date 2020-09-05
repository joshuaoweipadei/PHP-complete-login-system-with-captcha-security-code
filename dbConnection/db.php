<?php
/* Database connection settings */
$ServerName = 'localhost';
$username = 'root';
$password = '';
$dbName = 'login_system';

$conn = mysqli_connect($ServerName, $username, $password, $dbName);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}
