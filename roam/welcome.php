<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) { 
    session_unset();
    session_destroy();
    header('Location: in.php');
    exit;
}

include('pnc/link.php');


// print_r($_SESSION);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="welcome.css">
</head>
<body>
    <header>
        <div class="left">
            <i class="fa-solid fa-plus"></i>
        </div>

        <div class="username">
            arie01823 <i class="fa-solid fa-chevron-down"></i>
        </div>

        <div class="right">
            <i class="fa-brands fa-threads"></i>
            <i class="fa-solid fa-bars"></i>
        </div>
    </header>

    <!-- Profile Section -->
    <section class="profile">

        <div class="profile-top">
            <div class="avatar">
                <img src="https://via.placeholder.com/120" alt="">
                <span class="add-btn">+</span>
            </div>

            <div class="stats">
                <div>
                    <h3>1</h3>
                    <p>posts</p>
                </div>

                <div>
                    <h3>0</h3>
                    <p>followers</p>
                </div>

                <div>
                    <h3>0</h3>
                    <p>following</p>
                </div>
            </div>
        </div>

        <div class="bio">
            <h3>Loretta Rose 🌹</h3>
        </div>

        <div class="buttons">
            <button>Edit profile</button>
            <button>Share profile</button>
            <button class="icon-btn">
                <i class="fa-solid fa-user-plus"></i>
            </button>
        </div>
    </section>

    <!-- Tabs -->
    <div class="tabs">
        <div class="active">
            <i class="fa-solid fa-table-cells"></i>
        </div>
        <div>
            <i class="fa-regular fa-id-badge"></i>
        </div>
    </div>

    <!-- Feed Error -->
    <section class="feed">
        <div class="error-box">
            Couldn't refresh feed
        </div>

        <div class="reload">
            <i class="fa-solid fa-rotate-right"></i>
        </div>
    </section>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <i class="fa-solid fa-house"></i>
        <i class="fa-regular fa-circle-play"></i>
        <i class="fa-regular fa-paper-plane"></i>
        <i class="fa-solid fa-magnifying-glass"></i>
        <i class="fa-regular fa-circle-user"></i>
        <div>
         <h1>welcome <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></h1>
        <form method="POST">
            <button type="submit" name="logout">Log Out</button>
        </form>

    </div>
    </nav>
    
</body>
</html>