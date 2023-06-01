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

if(isset($_POST['submit'])){
    $email = $_POST['email'];

    // Check if email exists in database
    $check_email = "SELECT * FROM user_form WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows == 0) {
        $err = true; // set error flag
        $error[] = "Email address not found.";
    } else {
        // Generate reset password token
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $user_id = $result->fetch_assoc()['id'];

        // Insert token into database
        $insert_token = "INSERT INTO reset_password_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
        $stmt = $conn->prepare($insert_token);
        $stmt->bind_param("is", $user_id, $token);
        $stmt->execute();
        $stmt->close();

        // Send email with reset password link using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'amanmjh10@gmail.com'; // Replace with your own Gmail username
            $mail->Password = 'efnwneinkfwpmeac'; // Replace with your own Gmail password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipient email
            $mail->setFrom('amanmjh10@gmail.com'); // Replace with your own Gmail username
            $mail->addAddress($email);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Reset your password';
            $mail->Body = "Click the following link to reset your password: <a href='http://example.com/reset_password.php?token=$token'>http://example.com/reset_password.php?token=$token</a>";

            // Send email
            $mail->send();
            $mailSent = true;
        } catch (Exception $e) {
            $mailErr = true;
        }
    }
}

?>
<!-- HTML form for resetting password -->
<!DOCTYPE html>
<html lang="en">
<head>
   <title>forgot password</title>
   <!-- custom css file link  -->
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
   <form method="POST" action="">
      <h2>forgot password</h2><hr><br>
    <div class="field">
         <label for="email">Email:</label>
         <input type="email" name="email" id="email" required>
      </div><br>
      <div class="field btn">
      <div class="btn-layer"></div>
         <input type="submit" name="submit" value="Reset Password">
      </div>
      <?php
// Show success or error message
if($mailSent) {
    echo "<div class='success-message'>An email with instructions to reset your password has been sent to your email address.</div>";
 } elseif($mailErr) {
    echo "<div class='error-message'>Oops! Something went wrong. Please try again later.</div>";
 } elseif($err) {
    foreach($error as $error_message) {
       echo "<div class='error-message'>$error_message</div>";
    }
 }
 ?>
</form>
</div>
</body>
</html>
