<?php
session_start();

require('includes/login-session.php');

if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    header("Location: logout.php");
    exit();
} else{
    if(isLoginSessionExpired()){
        header("Location: logout.php?timeout=true");
        exit();
    }
}

require('dbConnection/db.php');   // database connection

$sql_stmt = "SELECT * FROM users WHERE userId = '".$_SESSION['user_id']."' AND email = '".$_SESSION['user_email']."'";
$query = mysqli_query($conn, $sql_stmt) or die(mysqli_error($conn));
if($query){
    if($row = mysqli_fetch_array($query)){

    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <title>PHP Login System</title>
</head>
<body>
<div class="container">
        <div class="row mt-3">
            <div class="col-md-12">
                <h5>Profile page</h5>
                <a href="logout.php">Logout</a>
                <div>
                    Welcome to your profile page, <b class="text-capitalize"><?php echo $row["firstname"]." ".$row["lastname"]; ?></b>
                </div>
            </div>
        </div>
    </div>
</body>
</html>