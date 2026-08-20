<?php
session_start();
include('pnc/link.php');



 if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['submit'])){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $reg_date = date('Y-m-d H:i:s');

    $errors = [];

    if($email === ''){
        $errors[] = 'email required';
    }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = 'Email is invalid';
    }

    if($password === ''){
        $errors[] = 'password required';
    }elseif(strlen($password) < 6){
        $errors[] = 'Password must be greater than six';
    }

    if($fullname === ''){
        $errors[] = 'Fullname required';
    }

    if($username === ''){
        $errors[] = 'username required';
    }

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);


    if (empty($errors)) {
        
        $_SESSION['username'] = $username;

        $query_insert = "INSERT INTO new_data (email, password, fullname, username, reg_date) 
        VALUES('$email', '$hashPassword', '$fullname', '$username', '$reg_date')";

        $result = mysqli_query($con, $query_insert);

        if($result){
            header("location: sign.php?new_data=1");
            exit;
        } else {
            echo "Data could not submit: " . mysqli_error($con);
        }
    }
    }

 






?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div class="container">

  <?php 
        if(isset($_POST['submit'])){
    ?>

    <div class="errors">
        <?php
            foreach($errors as $error){ 
                echo "<p class='error'>$error</p>"; 
            }
        ?>
    </div>
    <?php }?>

    <div class="signup-box">
      <img src="image/instagram edited.png" alt="">
      <p>Signup to see photos and videos<br>from your friends.</p>
      <button class="fb-login">Log in with facebook</button>
      <div class="divider"><span>OR</span></div>

      
      <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
        <input type="text" placeholder="Mobile Number or Email" name="email"><br>
        <input type="text" placeholder="Password" name="password"><br>
        <input type="text" placeholder="Full Name" name="fullname"><br>
        <input type="text" placeholder="Username" name="username"><br>
        <p class="info">
          People who use our service may have uploaded your contact information to Instagram. <a href="#"
            class="col">Learn<br>More.</a>
        </p>
        <p class="terms">
          By signing up, you agree to our <a href="#" class="col">Terms</a>, <a href="#" class="col">Privacy,
            Policy</a>, and <a href="#" class="col">Cookies Policy</a>.
        </p>
        <button type="submit" class="signup-btn" name="submit">Sign up</button>
      </form>
    </div>
  </div>
</body>

</html>