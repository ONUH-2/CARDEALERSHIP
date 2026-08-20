<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/cars.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory | Honus Autos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        :root {
            color-scheme: dark;
            --bg: #06070c;
            --surface: #12151f;
            --surface-alt: #171c29;
            --surface-strong: #1d2333;
            --text: #f2f4fa;
            --muted: #8b93a8;
            --border: rgba(255,255,255,.09);
            --border-strong: rgba(255,255,255,.18);
            --accent: #ff5b2e;
            --accent-dark: #e2461b;
            --accent-2: #19c9ff;
            --accent-grad: linear-gradient(135deg,#ff5b2e,#ff8a3d);
            --shadow: 0 25px 60px rgba(0,0,0,.55);
            --glow: 0 0 0 1px rgba(255,91,46,.3), 0 20px 45px rgba(255,91,46,.16);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 15% 0%, #151a28 0%, var(--bg) 45%);
            color: var(--text);
            overflow-x: hidden;
        }

        h1, h2, h3, h4 { font-family: 'Rajdhani', sans-serif; font-weight: 700; }

        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 18px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(8, 9, 15, 0.82);
            backdrop-filter: blur(18px);
            z-index: 1000;
            border-bottom: 1px solid var(--border);
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 2px;
            background: var(--accent-grad);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            cursor: pointer;
        }

        .nav-links { display: flex; list-style: none; flex-wrap: wrap; gap: 24px; }

        .nav-links a {
            text-decoration: none;
            color: var(--muted);
            font-weight: 600;
            transition: color 0.25s ease;
            position: relative;
        }

        .nav-links a:hover, .nav-links a.active { color: var(--text); }

        .nav-links a::after {
            content: "";
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--accent-grad);
            left: 0;
            bottom: -8px;
            transition: width 0.25s ease;
        }

        .nav-links a:hover::after, .nav-links a.active::after { width: 100%; }

        .user-area {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .user-name {
            color: var(--text);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .user-logout {
            color: var(--accent-2);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 20px;
            border-radius: 999px;
            background: var(--accent-grad);
            color: #0a0a0d;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 12px 30px rgba(255, 91, 46, .28);
            transition: all .22s ease;
        }

        .nav-cta:hover { transform: translateY(-2px); box-shadow: 0 16px 38px rgba(255,91,46,.42); }

        .hero {
            min-height: 70vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            padding: 150px 24px 70px;
            overflow: hidden;
            background:
                linear-gradient(180deg, rgba(6,7,12,.55) 0%, rgba(6,7,12,.94) 88%),
                url('images/begin.jpg') center/cover no-repeat;
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(115deg, rgba(255,91,46,.08) 0 2px, transparent 2px 90px),
                repeating-linear-gradient(65deg, rgba(25,201,255,.06) 0 2px, transparent 2px 120px);
            animation: driftLines 22s linear infinite;
            pointer-events: none;
        }

        @keyframes driftLines {
            from { background-position: 0 0, 0 0; }
            to { background-position: 400px 0, -400px 0; }
        }

        .hero-content { position: relative; z-index: 2; max-width: 860px; padding: 0 24px; }

        .eyebrow-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 999px;
            border: 1px solid var(--border-strong);
            background: rgba(255,91,46,.08);
            color: var(--accent-2);
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
            font-size: .75rem;
            margin-bottom: 22px;
        }

        .hero-content h1 {
            font-size: clamp(3rem, 6vw, 5.4rem);
            font-weight: 700;
            line-height: 1.02;
            margin-bottom: 20px;
            color: var(--text);
        }

        .hero-content h1 span {
            display: block;
            background: var(--accent-grad);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-content p {
            font-size: 1.1rem;
            color: var(--muted);
            margin-top: 18px;
            line-height: 1.8;
        }

        .inventory {
            padding: 60px 8% 120px;
            max-width: 1550px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .title { max-width: 850px; margin: 0 auto 44px; text-align: center; }

        .title h2 { font-size: clamp(2.2rem, 3vw, 2.9rem); margin-bottom: 14px; color: var(--text); }

        .title p { color: var(--muted); font-size: 1rem; line-height: 1.8; }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 32px;
            align-items: stretch;
        }

        .car-card {
            background: linear-gradient(180deg, var(--surface) 0%, var(--surface-alt) 100%);
            border: 1px solid var(--border);
            border-radius: 26px;
            overflow: hidden;
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color .35s ease, opacity 0.7s ease;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            min-height: 520px;
            opacity: 0;
            transform: translateY(24px);
        }

        .car-card.reveal { opacity: 1; transform: translateY(0); }

        .car-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 91, 46, .45);
            box-shadow: var(--glow);
        }

        .car-image {
            width: 100%;
            aspect-ratio: 16 / 9;
            min-height: 260px;
            overflow: hidden;
            background: var(--surface-strong);
            position: relative;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            transition: transform 0.4s ease;
        }

        .car-card:hover .car-image img { transform: scale(1.04); }

        .status-flag {
            position: absolute;
            top: 14px;
            left: 14px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            background: rgba(16,20,30,.78);
            border: 1px solid rgba(74, 222, 128, .5);
            color: #4ade80;
        }

        .car-info {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            flex: 1;
        }

        .car-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
        }

        .car-title-row h3 {
            font-size: 22px;
            line-height: 1.15;
            color: var(--text);
        }

        .price-pill {
            background: rgba(255,91,46,.12);
            border: 1px solid rgba(255,91,46,.35);
            color: var(--accent-2);
            padding: 9px 15px;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .car-tagline { color: var(--muted); line-height: 1.7; font-size: .96rem; min-height: 50px; }

        .car-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            color: var(--muted);
            font-size: .85rem;
        }

        .car-meta span {
            background: var(--surface-strong);
            border: 1px solid var(--border);
            padding: 6px 12px;
            border-radius: 999px;
        }

        .car-actions { margin-top: auto; }

        .btn-full {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 13px 20px;
            border-radius: 999px;
            font-weight: 800;
            background: var(--accent-grad);
            color: #0a0a0d;
            box-shadow: 0 12px 28px rgba(255,91,46,.22);
            transition: all .22s ease;
        }

        .btn-full:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(255,91,46,.36); }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--muted);
            border: 1px dashed var(--border-strong);
            border-radius: 24px;
        }

        .why-us { padding: 20px 8% 100px; max-width: 1550px; margin: 0 auto; position: relative; z-index: 2; }

        .why-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 22px;
            margin-top: 32px;
        }

        .why-box {
            background: linear-gradient(180deg, var(--surface) 0%, var(--surface-alt) 100%);
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 28px;
            box-shadow: var(--shadow);
            transition: transform .25s ease, border-color .25s ease;
        }

        .why-box:hover { transform: translateY(-4px); border-color: rgba(25,201,255,.4); }

        .why-box i { color: var(--accent-2); font-size: 1.4rem; margin-bottom: 12px; display: block; }

        .why-box h3 { margin-bottom: 10px; font-size: 1.25rem; color: var(--text); }
        .why-box p { color: var(--muted); line-height: 1.75; }

        footer { padding: 60px 8% 40px; background: var(--bg); border-top: 1px solid var(--border); position: relative; z-index: 2; }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 28px;
            margin-bottom: 24px;
        }

        .footer-section h3 { font-size: 1.1rem; margin-bottom: 16px; color: var(--text); }

        .footer-section p, .footer-section a {
            color: var(--muted);
            display: block;
            margin-bottom: 10px;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .footer-section a:hover { color: var(--accent-2); }
        footer hr { border: none; border-top: 1px solid var(--border); margin-bottom: 24px; }

        @media (max-width: 992px) { .hero-content h1 { font-size: 4rem; } }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; padding: 16px 6%; gap: 12px; }
            .nav-links { justify-content: center; }
            .hero { min-height: 60vh; padding-top: 120px; }
            .hero-content h1 { font-size: 2.8rem; }
        }

        @media (max-width: 640px) {
            .car-grid { grid-template-columns: 1fr; }
            .car-image { min-height: 220px; }
            .car-title-row { flex-direction: column; align-items: flex-start; }
            .car-title-row h3 { font-size: 20px; }
        }
    </style>

</head>

<body>

    <nav class="navbar">

        <p class="logo"><i class="fa-solid fa-bolt"></i> HONUS AUTOS</p>

        <ul class="nav-links">
            <li><a href="index.php#home">Home</a></li>
            <li><a href="inventory.php" class="active">Inventory</a></li>
            <li><a href="index.php#about">About</a></li>
            <li><a href="index.php#services">Services</a></li>
            <li><a href="index.php#reviews">Reviews</a></li>
            <li><a href="index.php#contact">Contact</a></li>
            <?php if (is_logged_in()): ?>
                <li><a href="my-appointments.php">My Appointments<?php $navCount = get_nav_appt_count(); if ($navCount > 0): ?> <span style="display:inline-flex;min-width:18px;height:18px;padding:0 5px;align-items:center;justify-content:center;border-radius:999px;background:var(--accent-grad);color:#0a0a0d;font-size:0.7rem;margin-left:4px;vertical-align:middle;font-weight:800;"><?php echo $navCount; ?></span><?php endif; ?></a></li>
            <?php endif; ?>
            <?php if (is_admin_user()): ?>
                <li><a href="admin/dashboard.php">Admin</a></li>
            <?php endif; ?>
        </ul>

        <?php if (!empty($_SESSION['username'])): ?>
            <div class="user-area">
                <span class="user-name"><i class="fa fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a class="user-logout" href="login/logout.php"><i class="fa fa-sign-out-alt"></i> Log out</a>
            </div>
        <?php else: ?>
            <a class="nav-cta" href="login/login.php?form=signup"><i class="fa fa-user-plus"></i> Sign Up</a>
        <?php endif; ?>

    </nav>

    <section class="hero" id="home">
        <div class="hero-content">
            <span class="eyebrow-tag"><i class="fa-solid fa-gauge-high"></i> Full Inventory</span>
            <h1>OUR COMPLETE<span>COLLECTION</span></h1>
            <p>Browse every vehicle currently available. Find your perfect match today.</p>
        </div>
    </section>

    <section class="inventory" id="inventory">

        <div class="title">
            <h2>All Available Vehicles</h2>
            <p>Discover our complete, hand-curated collection of premium vehicles.</p>
        </div>

        <div class="car-grid">
            <?php
            $cars = get_all_cars();
            if (!$cars):
            ?>
                <div class="empty-state" style="grid-column:1/-1">
                    <i class="fa-solid fa-car-side" style="font-size:2rem;color:var(--accent);margin-bottom:14px;display:block;"></i>
                    No vehicles in inventory yet. Check back soon.
                </div>
            <?php
            else:
                foreach ($cars as $car):
                    $image = first_car_image($car);
                    $status = $car['status'] ?? 'In Stock';
            ?>
                <article class="car-card">
                    <div class="car-image">
                        <?php if ($status && strtolower($status) !== 'hidden'): ?>
                            <span class="status-flag"><?php echo htmlspecialchars($status); ?></span>
                        <?php endif; ?>
                        <img src="<?php echo htmlspecialchars(car_image_url($image)); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>">
                    </div>
                    <div class="car-info">
                        <div class="car-title-row">
                            <h3><?php echo htmlspecialchars($car['name']); ?></h3>
                            <span class="price-pill"><?php echo htmlspecialchars($car['price']); ?></span>
                        </div>
                        <p class="car-tagline"><?php echo htmlspecialchars($car['tagline']); ?></p>
                        <div class="car-meta">
                            <span><?php echo htmlspecialchars($car['year']); ?></span>
                            <span><?php echo htmlspecialchars($car['mileage']); ?></span>
                            <span><?php echo htmlspecialchars($car['drivetrain']); ?></span>
                        </div>
                        <div class="car-actions">
                            <a class="btn-full" href="car-details.php?id=<?php echo (int)$car['id']; ?>">View Details</a>
                        </div>
                    </div>
                </article>
            <?php
                endforeach;
            endif;
            ?>
        </div>

    </section>

    <section class="why-us">

        <div class="title">
            <h2>Interested in a Vehicle?</h2>
            <p>Contact us today to schedule a test drive.</p>
        </div>

        <div class="why-grid">
            <div class="why-box">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <h3>Flexible Financing</h3>
                <p>Affordable payment plans designed for you.</p>
            </div>
            <div class="why-box">
                <i class="fa-solid fa-road"></i>
                <h3>Test Drive</h3>
                <p>Experience luxury before you buy.</p>
            </div>
            <div class="why-box">
                <i class="fa-solid fa-right-left"></i>
                <h3>Trade-In Service</h3>
                <p>Get the best value for your current vehicle.</p>
            </div>
            <div class="why-box">
                <i class="fa-solid fa-headset"></i>
                <h3>Expert Support</h3>
                <p>Our team is here to assist you anytime.</p>
            </div>
        </div>

    </section>

    <script>
        const inventoryCards = document.querySelectorAll('.car-card');
        const inventoryObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    entry.target.style.transitionDelay = (i % 3) * 0.08 + 's';
                    entry.target.classList.add('reveal');
                    inventoryObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        inventoryCards.forEach((card) => inventoryObserver.observe(card));
    </script>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>HONUS AUTOS</h3>
                <p>Luxury. Performance. Excellence.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="index.php#home">Home</a>
                <a href="inventory.php">Inventory</a>
                <a href="index.php#about">About</a>
                <a href="index.php#services">Services</a>
                <a href="index.php#contact">Contact</a>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Lagos, Nigeria</p>
                <p>+234 000 000 0000</p>
                <p>info@akazasmotors.com</p>
            </div>
        </div>
        <hr>
        <p style="text-align: center; padding: 10px 0; color: var(--muted);">© 2026 Akaza's Motors. All Rights Reserved.</p>
    </footer>

</body>

</html>
