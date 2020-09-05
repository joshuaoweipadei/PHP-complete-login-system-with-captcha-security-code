<?php
session_start();

if(isset($_SESSION["user_id"]) || isset($_SESSION['user_email'])){
    header("Location: ./");
    exit();
}

require('dbConnection/db.php');   // database connection

if(isset($_GET['token']) && !empty($_GET['token']) && isset($_GET['email']) && !empty($_GET['email'])){
    $sql_validate = "SELECT * FROM users WHERE email = '".$_GET['email']."' AND token = '".$_GET['token']."'";
    $query_validate = mysqli_query($conn, $sql_validate) or die(mysqli_error($conn));
    if(mysqli_num_rows($query_validate) != 1){
        header("Location: login.php");
        exit();
    }
} else{
    header("Location: login.php");
    exit();
}


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset-password'])){
    // Escape all $_POST variables to protect against SQL injections
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $password = test_input($_POST['password']);
    $passwordAgain = test_input($_POST['passwordAgain']);

    if(empty($password) || empty($passwordAgain)){
        $errorMsg = "Enter your passwords";
    } else{
        if($password != $passwordAgain){
            $errorMsg = "Passworss do not match!";
        } else{
            $sql_stmt = "UPDATE users SET password = md5('$password') WHERE email = '".$_GET['email']."' AND token = '".$_GET['token']."'";
            $query = mysqli_query($conn, $sql_stmt) or die(mysqli_error($conn));
            if($query){
                $_SESSION["reset-password-success"] = "Password reset is successful, you can login now.";
                header("Location: login.php");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <title>Reset Password | PHP Login System</title>
</head>
<body>
<div class="container h-100">
        <div class="row mt-5">
            <div class="col-md-4 m-auto">
                <h4 class="text-center">Reset Password</h4>

                <?php if(isset($success)){ ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php } ?>

                <div>
                    <form action="" method="POST" onSubmit="return validate_login()">
                        <div class="form-row">
                            <div class="form-group col">
                                <label for="">Password</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col">
                                <label for="">Password Again</label>
                                <input type="password" name="passwordAgain" class="form-control">
                            </div>
                        </div>

                        <button type="submit" name="reset-password" class="btn btn-primary btn-block mt-">Reset Password</button>

                        <?php if(isset($errorMsg)){ ?>
                        <div class="alert alert-danger mt-3">
                            <?php echo $errorMsg; ?>
                        </div>
                        <?php } ?>

                        <div class="d-flex justify-content-between mt-4">
                            <p><a href="login.php">Login</a></p>
                            <p><a href="register.php">Register</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>
</html>
<?php
unset($_SESSION['registration-success']);