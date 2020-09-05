<?php
session_start();

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Import Captcha class into the global namespace
use LoginSystem\Captcha;

if(isset($_SESSION["user_id"]) || isset($_SESSION['user_email'])){
    header("Location: ./");
    exit();
}


require('dbConnection/db.php');   // database connection
require("includes/Captcha.php");   // captcha class
$captcha = new Captcha();


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])){
    // Escape all $_POST variables to protect against SQL injections
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $errorMsg = '';
    $firstname = test_input($_POST['firstname']);
    $lastname = test_input($_POST['lastname']);
    $email = test_input($_POST['email']);
    $password = test_input($_POST['password']);
    $passwordAgain = test_input($_POST['passwordAgain']);
    $token = md5(mt_rand(1000, 9999999).$email);
    $userCaptcha = filter_var($_POST["captcha_code"], FILTER_SANITIZE_STRING);

    $isValidCaptcha = $captcha->validateCaptcha($userCaptcha);

    if(empty($firstname) || empty($lastname) || empty($email) || empty($password) ||  empty($passwordAgain)){
        $errorMsg = "All fields are required.";
    } else{
        if(!preg_match("/^[a-zA-Z]*$/", $firstname)){
            $errorMsg = 'First name: Invalid character!';
        } else{
            if(!preg_match("/^[a-zA-Z]*$/", $lastname)){
                $errorMsg = 'Last name: Invalid character!';
            } else{
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $errorMsg = 'Invalid email address!';
                } else{
                    if(strlen($password) < 4){
                        $errorMsg = 'Password must be 4 or more character!';
                    } else{
                        if($password != $passwordAgain){
                            $errorMsg = 'Password do not match!';
                        } else{
                            if(!$isValidCaptcha){
                                $errorMsg = "Incorrect Captcha Code!";
                            } else{
                                // Check if user with that email already exists
                                $sql_stmt = "SELECT * FROM users WHERE email = '$email'";
                                $query = mysqli_query($conn, $sql_stmt) or die(mysqli_error($conn));
                                if($query){
                                    if(mysqli_num_rows($query) == 0){
                                        $sql_insert_stmt = "INSERT INTO users (firstname, lastname, email, password, token) VALUES ('$firstname', '$lastname', '$email', md5('$password'), '$token')";
                                        $query_insert = mysqli_query($conn, $sql_insert_stmt) or die(mysqli_error($conn));
                                        if($query_insert){
                                            // Send registration confirmation link (verify-account.php)
                                            // Load Composer's autoloader
                                            require('vendor/autoload.php');
                                            require("includes/email_config.php");

                                            $url = PROJECT_HOME_LINK."verify-account.php?email=".$email."&token=".$token;
                                            $mail_variables = array();

                                            $mail_variables['APP_NAME'] = SENDER_NAME;
                                            $mail_variables['username'] = $firstname." ".$lastname;
                                            $mail_variables['email'] = $email;
                                            $mail_variables['url'] = $url;

                                            $message = file_get_contents("includes/email_templates/registration.php");

                                            foreach($mail_variables as $key => $value) {
                                                $message = str_replace('{{ '.$key.' }}', $value, $message);
                                            }

                                            $mail = new PHPMailer();

                                            $mail->IsSMTP();
                                            $mail->SMTPDebug = 0;
                                            $mail->SMTPAuth = true;
                                            $mail->SMTPSecure = "ssl";
                                            $mail->Port     = PORT;  
                                            $mail->Username = MAIL_USERNAME;
                                            $mail->Password = MAIL_PASSWORD;
                                            $mail->Host     = MAIL_HOST;

                                            $mail->SetFrom(SERDER_EMAIL, SENDER_NAME);
                                            $mail->AddReplyTo(SERDER_EMAIL, SENDER_NAME);
                                            $mail->ReturnPath = SERDER_EMAIL;	
                                            $mail->AddAddress($email, $firstname." ".$lastname);
                                            $mail->Subject = "Verify Your Account";		
                                            $mail->MsgHTML($message);
                                            $mail->IsHTML(true);

                                            if(!$mail->Send()){
                                                $errorMsg = "Unable to send email, please contact us immediately.";
                                            } else {
                                                $_SESSION['registration-success'] = "Registration is successful, follow the click in your email to confirm your account.";
                                                header("Location: login.php");
                                                exit();
                                            }
                                        }
                                    } else{
                                        $errorMsg = 'This email <b>('.$email.')</b> is already associated with another account!';
                                    }
                                }
                            }
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
    <title>Register | PHP Login System</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6 m-auto">
                <h3>PHP Login System with recaptha security code</h3>
                <div>
                <!--  -->
                    <form action="" method="POST" onSubmit="return validate_registration()">
                        <div class="form-row">
                            <div class="form-group col">
                                <label for="">First Name</label>
                                <input type="text" name="firstname" id="firstname" class="form-control">
                                <small class="error" id="firstname_err"></small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col">
                                <label for="">Last Name</label>
                                <input type="text" name="lastname" id="lastname" class="form-control">
                                <small class="error" id="lastname_err"></small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col">
                                <label for="">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control">
                                <small class="error" id="email_err"></small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col">
                                <label for="">Password</label>
                                <input type="password" name="password" id="password" class="form-control">
                                <small class="error" id="password_err"></small>
                            </div>
                            <div class="form-group col">
                                <label for="">Password Again</label>
                                <input type="password" name="passwordAgain" id="passwordAgain" class="form-control">
                                <small class="error" id="passwordAgain_err"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row form-group">
                                <label>Captcha Code : </label> <small id="captcha_code_err" class="error"></small>
                                <input name="captcha_code" type="text" id="captcha_code" class="form-control captcha-input">
                                <small class="form-text text-muted">Enter the captcha code above to proceed</small>
                            </div>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary px-3">Register</button>

                        <?php if(isset($errorMsg)){ ?>
                        <div class="alert alert-danger mt-3">
                            <?php echo $errorMsg; ?>
                        </div>
                        <?php } ?>

                        <div class="mt-4">
                            <p>Already have an account? <a href="login.php">Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    <script src="js/validation.js" type="text/javascript"></script>
</body>
</html>