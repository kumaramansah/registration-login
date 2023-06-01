<?php
  // sending mail
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  require 'PHPMailer/src/Exception.php';
  require 'PHPMailer/src/PHPMailer.php';
  require 'PHPMailer/src/SMTP.php';
  $showAlert = false;
  $success = false;
  $error = "";
include 'config.php';

if(isset($_POST['submit'])){
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    // Check if username is already taken
    $check_username = "SELECT * FROM user_form WHERE username = ?";
    $stmt = $conn->prepare($check_username);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    /*if($result->num_rows > 0) {
         $error[] = "Username is already taken.";
     }*/
    // Validate fields
    $name_regex = "/^[a-zA-Z]+$/";
    if(empty($firstname) || !preg_match($name_regex, $firstname)) {
        $error[] = "Invalid first name.";
    }
    if(empty($lastname) || !preg_match($name_regex, $lastname)) {
        $error[] = "Invalid last name.";
    }
    if(empty($dob)) {
        $error[] = "Date of birth is required.";
    } elseif(strtotime($dob) > time()) {
        $error[] = "Invalid date of birth.";
    }
    
    if(empty($gender)) {
        $error[] = "Gender is required.";
    }
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "Invalid email format.";
    }
    if(empty($username)) {
        $error[] = "Username is required.";
    }
    // Password validation
    $password_regex = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+}{\"';:?><,.\\[\\]\\\\|\\-=])(?=.*[^\dA-Za-z]).{8,}$/";

    if(empty($password) || !preg_match($password_regex, $password)) {
        $error[] = "Password must contain at least 8 characters, one capital letter, one small letter, one number, and one special character.";
    }

    if(empty($error)) {
        // Database connection
        if($conn->connect_error){
            echo "$conn->connect_error";
            die("Connection Failed : ". $conn->connect_error);
        } else {
            // Insert new user
            $insert = "INSERT INTO user_form (firstname, lastname, dob, gender, username, email, password, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert);
            $stmt->bind_param("ssssssss", $firstname, $lastname, $dob, $gender, $username, $email, $password, $user_type);
            $stmt->execute();
            echo "Registration successful.";
            $stmt->close();
            $conn->close();
    
            // Send confirmation email
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'amanmjh10@gmail.com'; // Your Gmail address
                $mail->Password   = 'efnwneinkfwpmeac'; // Your Gmail password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->setFrom('amanmjh10@gmail.com', 'Aman Sah');
    
                //Recipients
                $mail->addAddress($email, $firstname);
    
                //Content
                $mail->isHTML(true);
                $mail->Subject = 'Registration Confirmation';
                $mail->Body    = 'Your account has been successfully created.';
                $mail->AltBody = 'Your account has been successfully created.';
    
                $mail->send();
                $success = true;
            } catch (Exception $e) {
                $mail_error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
    
    // Check if there are any errors and handle them accordingly
    if (!empty($error) || isset($mail_error)) {
        $showAlert = true;
        $success = false;
        if (!empty($error)) {
            $error = implode("<br>", $error);
        }
        if (isset($mail_error)) {
            $error .= "<br>" . $mail_error;
        }
    }
}    

?>
<!DOCTYPE html>
<html lang="en" >
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register form</title>
   <!-- custom css file link  -->
   <link rel="stylesheet" href="style.css">
</head>
<body>
   
   <div class="wrapper">
      <div class="title-text">
         <div class="title login">Login Form</div>
         <div class="title signup">Registration Form</div>
      </div>
      <div class="form-container">
         <div class="slide-controls">
            <input type="radio" name="slide" id="login" checked>
            <input type="radio" name="slide" id="signup">
            <label for="login" class="slide login">Login</label>
            <label for="signup" class="slide signup">Signup</label>
            <div class="slider-tab"></div>
         </div>
         <div class="form-inner">
            <form action="" method="post" class="login">
               <div class="field">
                  <?php if(isset($error) && !empty($error)){ ?>
                     <span class="error-msg"><?php echo $error; ?></span>
                  <?php } ?>
                  <br>
                  <input type="text" name="username" required placeholder="Enter your username">
               </div>
               <div class="field">
                  <input type="password" name="password" required placeholder="Enter your password">
               </div>
               <div class="pass-link"><a href="#">Forgot password?</a></div>
               <div class="field btn">
                  <div class="btn-layer"></div>
                  <input type="submit" name="submit" value="Login">
                  <a href="forgot_password.php">Forgot password?</a>
               </div>
               <div class="signup-link">Don't have an account? <a href="register_form.php">Register now</a></div>
            </form>
            <form action="" method="post" class="signup">
                  </nav>
            <p style="color:red;">
  <?php
  if(isset($error)) {
    echo $error;
  }
  ?>
  </p>
  <?php
  if($success) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Registration Successfull!</strong> You can login now.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>';
  }
  else if($showAlert) {
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <strong>Error!</strong> Username already exists.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>';
  }
  ?>
  <!-- <?php 
      if(isset($error)){
        foreach($error as $error){
           echo '<span class="error-msg">'.$error.'</span>';
        };
     };
     ?>
     -->
  <div class="field">
    <label for="firstname">First Name:</label>
    <input type="text" id="firstname" name="firstname" required placeholder="Enter your firstname">
    <span id="firstname-error" class="error"></span><br>
  </div>
  <div class="field">
    <label for="lastname">Last Name:</label>
    <input type="text" id="lastname" name="lastname" required placeholder="Enter your lastname">
    <span id="lastname-error" class="error"></span><br>
  </div>
  <div class="field">
    <label for="dob">Date of Birth:</label>
    <input type="date" id="dob" name="dob" required>
    <span class="error" id="dob-error"></span><br>
  </div>
  <div class="field">
    <label for="gender">Gender:</label>
    <select id="gender" name="gender">
      <option value="">Select</option>
      <option value="male" <?php if (isset($gender) && $gender=="male") echo "selected";?>>Male</option>
      <option value="female" <?php if (isset($gender) && $gender=="female") echo "selected";?>>Female</option>
      <option value="rather_not" <?php if (isset($gender) && $gender=="rather_not") echo "selected";?>>Rather not to say</option>
    </select>
    <br><br>
  </div>
  <div class="field">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required placeholder="enter your email">
    <span class="error" id="email-error"></span><br>
  </div>
  <div class="field">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" pattern="[a-zA-Z0-9._-]{3,}" title="Username must be at least 3 characters and can contain letters, numbers, dots, underscores, and hyphens" required placeholder="enter your username">
    <span class="error" id="username-error"></span><br>
  </div>
  <div class="field">
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" minlength="8" required>
    <span class="error" id="password-error"></span><br>
  </div>
  <div class="field">
    <select name="user_type">
      <option value="user">user</option>
      <option value="admin">admin</option>
    </select>
  </div>
  <div class="field btn">
    <div class="btn-layer"></div>
    <input type="submit" name="submit" value="Signup" class="form-btn">
    <p>Already have an account? <a href="login_form.php">Login now</a></p>
  </div>
  <div class="pass-link">if youhave already account? <a href="">Login now</a></div>
</form>

     <!-- javascript -->

     <script>
  // Selecting all input fields and form
const form = document.querySelector('form');
const firstname = document.querySelector('#firstname');
const lastname = document.querySelector('#lastname');
const dob = document.querySelector('#dob');
const email = document.querySelector('#email');
const username = document.querySelector('#username');
const password = document.querySelector('#password');

// Function to display error message
function showError(input, message) {
  const errorElement = document.querySelector(`#${input.id}-error`);
  errorElement.textContent = message;
}

// Function to hide error message
function hideError(input) {
  const errorElement = document.querySelector(`#${input.id}-error`);
  errorElement.textContent = '';
}

// Function to validate First Name
function validateFirstName() {
  const firstnameValue = firstname.value.trim();
  if (firstnameValue === '') {
    showError(firstname, 'First Name is required');
  } else if (!/^[a-zA-Z ]{2,30}$/.test(firstnameValue)) {
    showError(firstname, 'Invalid(only alphabets and more than 1 char)');
  } else {
    hideError(firstname);
  }
}

// Function to validate Last Name
function validateLastName() {
  const lastnameValue = lastname.value.trim();
  if (lastnameValue === '') {
    showError(lastname, 'Last Name is required');
  } else if (!/^[a-zA-Z ]{2,30}$/.test(lastnameValue)) {
    showError(lastname, 'Invalid(only alphabets and more than 1 char)');
  } else {
    hideError(lastname);
  }
}

// Function to validate Date of Birth
function validateDOB() {
  const dobValue = dob.value.trim();
  const currentDate = new Date().toISOString().split('T')[0];
  if (dobValue === '') {
    showError(dob, 'Date of Birth is required');
  } else if (dobValue >= currentDate) {
    showError(dob, 'Date of Birth should be before current date');
  } else {
    hideError(dob);
  }
}

// Function to validate Email
function validateEmail() {
  const emailValue = email.value.trim();
  if (emailValue === '') {
    showError(email, 'Email is required');
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
    showError(email, 'Email is not valid');
  } else {
    hideError(email);
  }
}

// Function to validate Username
function validateUsername() {
  const usernameValue = username.value.trim();
  if (usernameValue === '') {
    showError(username, 'Username is required');
  } else if (!/^[a-zA-Z0-9._-]{3,}$/.test(usernameValue)) {
    showError(username, 'Username at least 3 characters(it may be .,_,-)');
  } else {
    hideError(username);
  }
}

function validatePassword() {
  const passwordValue = password.value.trim();
  const passwordRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=]).{8,}$/;

  if (passwordValue === '') {
    showError(password, 'Password is required');
  } else if (!passwordRegex.test(passwordValue)) {
    showError(password, 'Invalid(min 8 char,1 cap,1 special char and 1 no.)');
  } else {
    hideError(password);
  }
}

// Adding event listeners to form and input fields
form.addEventListener('submit', function (event) {
  event.preventDefault();
  validateFirstName();
  validateLastName();
  validateDOB();
  validateEmail();
  validateUsername();
  validatePassword();
});

firstname.addEventListener('keyup', validateFirstName);
lastname.addEventListener('keyup', validateLastName);
dob.addEventListener('change', validateDOB);
email.addEventListener('keyup', validateEmail);
username.addEventListener('keyup', validateUsername);
password.addEventListener('keyup', validatePassword);
</script>
</div>
 <script>
      const loginText = document.querySelector(".title-text .login");
      const loginForm = document.querySelector("form.login");
      const loginBtn = document.querySelector("label.login");
      const signupBtn = document.querySelector("label.signup");
      const signupLink = document.querySelector("form .signup-link a");
      signupBtn.onclick = (()=>{
        loginForm.style.marginLeft = "-50%";
        loginText.style.marginLeft = "-50%";
      });
      loginBtn.onclick = (()=>{
        loginForm.style.marginLeft = "0%";
        loginText.style.marginLeft = "0%";
      });
      signupLink.onclick = (()=>{
        signupBtn.click();
        return false;
      });
    </script>
</body>
</html>
