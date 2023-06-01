<?php
// sending mail
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
$err = false;
$success = false;
$mailSent = false;
$mailErr = false;

include 'config.php';
session_start();
$error = '';

if(isset($_POST['submit'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $select = "SELECT * FROM user_form WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $select);
    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        if($row['user_type'] == 'admin'){
            $_SESSION['admin_name'] = $row['firstname'];
            header('location:admin_page.php');
        } elseif($row['user_type'] == 'user'){
            $_SESSION['user_name'] = $row['firstname'];
            header('location:user_page.php');
        }
    } else {
        $error = 'Incorrect username or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login Form</title>

   <!-- Custom CSS file link  -->
   <link rel="stylesheet" href="css\style.css">
</head>
<body> 
<div class="form-container">
   <form action="" method="post">
      <h3>Login now</h3>
      <?php if(!empty($error)){ ?>
          <span class="error-msg"><?php echo $error; ?></span>
      <?php } ?>
      <br>
      <input type="text" name="username" required placeholder="Enter your username">
      <input type="password" name="password" required placeholder="Enter your password">
      <input type="submit" name="logn" value="Login now" class="form-btn">
      <a href="forgot_password.php">Forgot password?</a>
      <p>Don't have an account? <a href="register_form.php">Register now</a></p>
   </form>
</div>
</body>
</html>
