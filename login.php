<?php
session_start();

// Import Captcha class into the global namespace
use LoginSystem\Captcha;

if(isset($_SESSION["user_id"]) || isset($_SESSION['user_email'])){
    header("Location: ./");
    exit();
}

require('dbConnection/db.php');   // database connection
require("includes/Captcha.php");   // captcha class
$captcha = new Captcha();


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])){
    // Escape all $_POST variables to protect against SQL injections
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $email = test_input($_POST['email']);
    $password = test_input($_POST['password']);
    $userCaptcha = filter_var($_POST["captcha_code"], FILTER_SANITIZE_STRING);

    $isValidCaptcha = $captcha->validateCaptcha($userCaptcha);

    if(empty($email) || empty($password)){
        $errorMsg = "Enter email and password.";
    } else{
        if(empty($userCaptcha)){
            $errorMsg = "Enter captcha code!";
        } else{
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $errorMsg = "Invalid email address!";
            } else{
                if(!$isValidCaptcha){
                    $errorMsg = "Incorrect Captcha Code!";
                } else{
                    $sql_stmt = "SELECT * FROM users WHERE email = '$email' AND password = md5('$password')";
                    $query = mysqli_query($conn, $sql_stmt) or die(mysqli_error($conn));
                    if($query){
                        if(mysqli_num_rows($query) == 1){
                            $user = mysqli_fetch_array($query);
                            if($user['emailVerified'] == 1){
                                $_SESSION['user_id'] = $user['userId'];
                                $_SESSION['user_email'] = $user['email'];
                                $_SESSION['login_timeout'] = time(); 
            
                                header("Location: ./");
                                exit();
                            } else{
                                $errorMsg = "Account not verified yet. Please follow the link in your email to verify this account.";
                            }
                        } else{
                            $errorMsg = "Incorrect email or password.";
                        }
                    }
                }
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
    <style>
        .error{
            color: #ff0000
        }
        .captcha-input {
            background: #FFF url(includes/captchaImageSource.php) repeat-y left center;
            padding-left: 85px;
        }
    </style>
    <title>Login | PHP Login System</title>
</head>
<body>
<div class="container">
        <div class="row mt-5">
            <div class="col-md-4 m-auto">
                <h4 class="text-center">Login</h4>

                <?php if(isset($_GET["timeout"])){ ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Login time session has expired
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php } ?>
                
                <?php if(isset($_SESSION['registration-success'])){ ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['registration-success']; ?>
                    </div>
                <?php } ?>

                <?php if(isset($_SESSION["reset-password-success"])){ ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION["reset-password-success"]; ?>
                    </div>
                <?php } ?>

                <div>
                    <form action="" method="POST" onSubmit="return validate_login()">
                        <div class="form-row">
                            <div class="form-group col">
                                <label for="">Email</label>
                                <input type="email" name="email" id="email" class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col">
                                <label for="">Password</label>
                                <input type="password" name="password" id="passwordAgain" class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col">
                                <label>Captcha Code : </label> <small id="captcha_code_err" class="error"></small>
                                <input name="captcha_code" type="text" id="captcha_code" class="form-control captcha-input">
                            </div>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary btn-block mt-">Login</button>

                        <?php if(isset($errorMsg)){ ?>
                        <div class="alert alert-danger mt-3">
                            <?php echo $errorMsg; ?>
                        </div>
                        <?php } ?>

                        <div class="d-flex justify-content-between mt-4">
                            <p><a href="forgot-password.php">Forget Password</a></p>
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
unset($_SESSION["reset-password-success"]);