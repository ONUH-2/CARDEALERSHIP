<?php
session_start();

include('inc/connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $errors = [];

    if (!empty($email) && !empty($password)) {
        $stmt = $con->prepare("SELECT username, password FROM user_data WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $storedPassword = $user['password'];

            if (password_verify($password, $storedPassword) || $password === $storedPassword) {
                $_SESSION['username'] = $user['username'];
                header('Location: ../cardealership/index.php');
                exit;
            }

            $errors[] = 'Password does not match the stored password';
        } else {
            $errors[] = 'No account found with that email';
        }
    } else {
        $errors[] = 'Please fill all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="container">
        <?php if (isset($_POST['submit']) && !empty($errors)) { ?>
        <div class="errors">
            <?php foreach ($errors as $error) { echo "<p class='error'>$error</p>"; } ?>
        </div>
        <?php } ?>
        <form method="POST" action="">
            <input type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            <input type="password" placeholder="Password" name="password" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>">
            <button type="submit" name="submit">login</button>
        </form>
    </div>
</body>
</html>