<?php
 session_start();

 include('pnc/link.php');

 if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])){ 
     
     $email = trim($_POST['email']);
     $password = trim($_POST['password']);     
     
     $errors = [];
     
     
    if(!empty($email) && !empty($password)){
        $query = "SELECT * FROM new_data WHERE email='$email' AND password='$password'";
        $result = mysqli_query($con, $query);
    
        if(mysqli_num_rows($result) == 1){
            $user = mysqli_fetch_assoc($result);
            $_SESSION['username'] = $user['username'];

            header('Location: welcome.php');
            exit;
        }else{
            $errors[] = 'Invalid email or password';
        }
    }else{
        $errors[] = "Please fill all fields";
    }
             
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="sign.css">
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
        <div class="left-panel">
            <img src="image/igpic.png" alt="">
        </div>
        <div class="right-panel">
            <h1 class="logo"></h1>


            
            <form class="login-form" method= "POST" action="">
                <img src="image/instagram edited.png" alt="">
                <input type="text" placeholder="Phone number, username, or email" name="email"/>
                <input type="password" placeholder="Password" name="password" />
                <button type="submit" name="submit">Log in</button>
                <div class="divider">OR</div>
                <a href="#" class="fb-login">Log in with Facebook</a>
                <a href="#" class="forgot">Forgot password?</a>
            </form>
            <div class="signup-box">
                Don't have an account? <a href="#">Sign up</a>
            </div>
        </div>
    </div>
    <footer>
        <ul class="footer-links">
            <li>Meta</li>
            <li>About</li>
            <li>Blog</li>
            <li>Jobs</li>
            <li>Help</li>
            <li>API</li>
            <li>Privacy</li>
            <li>Terms</li>
            <li>Locations</li>
            <li>Instagram Lite</li>
            <li>Meta AI</li>
            <li>Threads</li>
            <li>Contact Uploading & Non-Users</li>
            <li>Meta Verified</li>
        </ul>
        <div></div>
        <p class="copyright">© 2025 Instagram from Meta</p>
    </footer>

</body>

</html>