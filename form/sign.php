<?php
session_start();

include('inc/connect.php');

$errors = [];

// If DB connection failed, $con will be boolean false per connect.php
if (isset($con) && $con === false) {
    $errors[] = 'Database connection unavailable. Please try again later.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    $reg_date = date('Y-m-d H:i:s');

    $errors = [];

    if ($fullname === '') {
        $errors[] = 'Fullname required';
    }

    if ($username === '') {
        $errors[] = 'username required';
    }

    if ($email === '') {
        $errors[] = 'email required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email is invalid';
    }

    if ($password === '') {
        $errors[] = 'password required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be greater than six';
    }

    if ($confirm === '') {
        $errors[] = 'confirm password required';
    } elseif ($confirm !== $password) {
        $errors[] = 'Password does not match';
    }

    if (empty($errors)) {
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $con->prepare("INSERT INTO user_data (firstname, username, email, password, regdate) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $username, $email, $hashPassword, $reg_date);

        if ($stmt->execute()) {
            $userId = $con->insert_id;
            $_SESSION['user_id'] = (int) $userId;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            header('location: ../cardealership/index.php');
            exit;
        } else {
            $errors[] = 'Data could not submit: ' . $stmt->error;
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
    <link rel="stylesheet" href="sign.css">
</head>
<body>
    <div class="container-one">
        <?php if (isset($_POST['submit']) && !empty($errors)) { ?>
        <div class="errors">
            <?php foreach ($errors as $error) { echo "<p class='error'>$error</p>"; } ?>
        </div>
        <?php } ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="text" placeholder="Full Name" name="fullname">
            <input type="text" placeholder="Username" name="username">
            <input type="text" placeholder="E-Mail" name="email">
            <input type="password" placeholder="Password" name="password">
            <input type="text" placeholder="Confirm Password" name="confirm_password">
            <input type="submit" value="Create Account" name="submit">
        </form>
    </div>
</body>
</html>
