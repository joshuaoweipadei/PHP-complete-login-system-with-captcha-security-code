<?php
session_start();

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


if(isset($_SESSION["user_id"]) || isset($_SESSION['user_email'])){
    header("Location: ./");
    exit();
}

require('dbConnection/db.php');   // database connection

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['forgot-password'])){
    // Escape all $_POST variables to protect against SQL injections
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $email = test_input($_POST['email']);

    if(empty($email)){
        $errorMsg = "Enter email address";
    } else{
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errorMsg = "Invalid email address!";
        } else{
            $sql_stmt = "SELECT * FROM users WHERE email = '$email'";
            $query = mysqli_query($conn, $sql_stmt) or die(mysqli_error($conn));
            if($query){
                if(mysqli_num_rows($query) == 1){
                    $user = mysqli_fetch_array($query);

                    // Send password reset link
                    require('vendor/autoload.php');
                    require("includes/email_config.php");

                    $url = PROJECT_HOME_LINK."reset-password.php?token=".$user["token"]."&email=".$user["email"];
                    $mail_variables = array();

                    $mail_variables['APP_NAME'] = SENDER_NAME;
                    $mail_variables['email'] = $user["email"];
                    $mail_variables['url'] = $url;

                    $message = file_get_contents("includes/email_templates/reset_password.php");

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
                    $mail->AddAddress($user["email"]);
                    $mail->Subject = "Reset Your Password";		
                    $mail->MsgHTML($message);
                    $mail->IsHTML(true);

                    if(!$mail->Send()) {
                        $errorMsg = 'Problem in Sending Password Recovery Email';
                    } else {
                        $success = 'Please check your email for a password reset link!';
                    }
                } else{
                    $errorMsg = "There is no account associated with this email address!";
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
    <title>Forget Password | PHP Login System</title>
</head>
<body>
<div class="container h-100">
        <div class="row mt-5">
            <div class="col-md-4 m-auto">
                <h4 class="text-center">Forget Password</h4>

                <?php if(isset($success)){ ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
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

                        <button type="submit" name="forgot-password" class="btn btn-primary btn-block mt-">Submit</button>

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