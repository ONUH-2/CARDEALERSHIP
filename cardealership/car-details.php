<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/cars.php';
require_once __DIR__ . '/../form/inc/connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$car = get_car_by_id($id);

// ─── Handle appointment form submission ──────────────────────
$apptSuccess = '';
$apptError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $car) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $date    = trim($_POST['appointment_date'] ?? '');
    $time    = trim($_POST['appointment_time'] ?? '');
    $type    = trim($_POST['appointment_type'] ?? 'Test Drive');
    $message = trim($_POST['message'] ?? '');

    // Validate
    if ($name === '')    $apptError = 'Please enter your full name.';
    elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $apptError = 'Please enter a valid email address.';
    elseif ($phone === '')
        $apptError = 'Please enter your phone number.';
    elseif ($date === '')
        $apptError = 'Please select an appointment date.';
    elseif ($time === '')
        $apptError = 'Please select an appointment time.';
    elseif (!in_array($type, ['Test Drive', 'Vehicle Viewing', 'General Inquiry'], true))
        $apptError = 'Please select a valid appointment type.';

    // Date must be today or in the future
    if (!$apptError && $date !== '') {
        $today = date('Y-m-d');
        if ($date < $today) {
            $apptError = 'The appointment date must be today or in the future.';
        }
    }

    // Save to database
    if (!$apptError && $con && $con !== false) {
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $stmt = $con->prepare(
            'INSERT INTO appointments (user_id, car_id, customer_name, customer_email, customer_phone, appointment_date, appointment_time, appointment_type, message, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        if ($stmt) {
            $status = 'Pending';
            $stmt->bind_param('iissssssss',
                $userId, $car['id'], $name, $email, $phone,
                $date, $time, $type, $message, $status
            );
            if ($stmt->execute()) {
                $apptSuccess = 'Your appointment has been booked! We\'ll review it and get back to you shortly.';
            } else {
                $apptError = 'Could not book your appointment. Please try again.';
            }
            $stmt->close();
        } else {
            $apptError = 'Could not process your request. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $car ? htmlspecialchars($car['name']) : 'Vehicle Not Found'; ?> | Honus Autos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            color-scheme: dark;
            --bg: #06070c;
            --panel: #12151f;
            --panel-border: rgba(255,255,255,.09);
            --border-strong: rgba(255,255,255,.18);
            --surface: #171c29;
            --surface-strong: #1d2333;
            --text: #f2f4fa;
            --text-muted: #8b93a8;
            --accent: #ff5b2e;
            --accent-2: #19c9ff;
            --accent-grad: linear-gradient(135deg,#ff5b2e,#ff8a3d);
            --accent-soft: rgba(255,91,46,0.12);
            --shadow: 0 30px 80px rgba(0,0,0,.55);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: radial-gradient(circle at 12% 0%, #151a28 0%, var(--bg) 45%);
            color: var(--text);
        }
        h1, h2, h3, h4 { font-family: 'Rajdhani', sans-serif; font-weight: 700; }
        a { color: inherit; text-decoration: none; }
        img { max-width: 100%; display: block; }
        .navbar {
            position: fixed; inset: 0 0 auto 0; z-index: 20;
            display: flex; justify-content: space-between; align-items: center;
            padding: 16px 8%; background: rgba(8,9,15,.82); border-bottom: 1px solid var(--panel-border); backdrop-filter: blur(18px);
        }
        .logo { display:inline-flex; align-items:center; gap:8px; font-size: 1.3rem; font-weight: 700; letter-spacing: .2em; background:var(--accent-grad); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .nav-links { display: flex; gap: 26px; list-style: none; flex-wrap: wrap; }
        .nav-links a { font-size: 0.95rem; color: var(--text-muted); font-weight:600; transition: color 0.22s ease; position: relative; }
        .nav-links a:hover { color: var(--text); }
        .nav-links a::after { content: ''; position: absolute; left: 0; bottom: -8px; width: 0; height: 2px; background: var(--accent-grad); transition: width 0.25s ease; }
        .nav-links a:hover::after { width: 100%; }
        .user-area { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .user-name { color: var(--text); font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }
        .user-logout { color: var(--accent-2); font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }
        .nav-cta { display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; border-radius: 999px; background: var(--accent-grad); color: #0a0a0d; font-weight: 800; box-shadow:0 12px 30px rgba(255,91,46,.28); }
        .details-page { padding: 130px 8% 80px; display: flex; justify-content: center; width: 100%; }
        .details-panel { width: min(100%, 1320px); margin: 0 auto; background: linear-gradient(180deg,var(--panel) 0%, var(--surface) 100%); border: 1px solid var(--panel-border); border-radius: 28px; box-shadow: var(--shadow); overflow: hidden; }
        .details-panel.not-found { padding: 90px 60px; text-align: center; }
        .details-panel.not-found h1 { font-size: clamp(2.4rem, 3vw, 3.4rem); margin-bottom: 18px; color: var(--text); }
        .details-panel.not-found p { color: var(--text-muted); margin-bottom: 30px; font-size: 1rem; max-width: 600px; margin-left: auto; margin-right: auto; }
        .details-hero { display: grid; grid-template-columns: 1.18fr 0.82fr; gap: 28px; align-items: stretch; padding: 42px 42px 22px; }
        .details-hero-copy { display: flex; flex-direction: column; gap: 18px; }
        .eyebrow { display: inline-flex; align-items: center; gap: 10px; color: var(--accent-2); text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.78rem; font-weight:700; }
        .eyebrow::before { content: ''; width: 42px; height: 1px; background: var(--accent-2); opacity:.5; }
        .details-hero-copy h1 { font-size: clamp(2.4rem, 3.5vw, 3.6rem); line-height: 1.05; color: var(--text); }
        .subtext { color: var(--text-muted); font-size: 0.98rem; max-width: 620px; margin-bottom: 18px; }
        .details-badges { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px; }
        .badge { display: inline-flex; align-items: center; padding: 10px 16px; border-radius: 999px; background: var(--surface); border: 1px solid var(--panel-border); color: var(--text); font-size: 0.9rem; }
        .details-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 12px; }
        .details-actions > a, .details-actions > button { width: 100%; }
        .btn-white, .btn-outline { display: inline-flex; align-items: center; justify-content: center; padding: 14px 24px; border-radius: 999px; font-weight: 800; transition: all 0.24s ease; min-height: 50px; cursor: pointer; border: none; font-family: inherit; font-size: 1rem; }
        .btn-white { background: var(--accent-grad); color: #0a0a0d; box-shadow: 0 16px 36px rgba(255,91,46,.28); }
        .btn-white:hover { transform: translateY(-2px); box-shadow: 0 20px 44px rgba(255,91,46,.4); }
        .btn-outline { border: 1px solid var(--border-strong); background: transparent; color: var(--text); }
        .btn-outline:hover { border-color: var(--accent-2); color: var(--accent-2); }
        .details-hero-media { position: relative; border-radius: 26px; overflow: hidden; min-height: 300px; max-height: 380px; border: 1px solid var(--panel-border); background: var(--surface-strong); }
        .details-hero-media img { width: 100%; height: 100%; object-fit: contain; transition: transform 0.35s ease; }
        .details-hero-media:hover img { transform: scale(1.03); }
        .price-chip { position: absolute; bottom: 20px; left: 20px; background: var(--accent-grad); color: #0a0a0d; padding: 13px 24px; border-radius: 999px; font-weight: 800; letter-spacing: 0.02em; box-shadow: 0 18px 36px rgba(255,91,46,.3); }
        .stat-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; padding: 0 46px 40px; }
        .stat-card { background: var(--surface); border: 1px solid var(--panel-border); border-radius: 22px; padding: 26px; text-align: center; color: var(--text); transition: transform .2s ease, border-color .2s ease; }
        .stat-card:hover { transform: translateY(-3px); border-color: rgba(255,91,46,.4); }
        .stat-card span { display: block; margin-bottom: 10px; font-size: 0.75rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--accent-2); }
        .stat-card strong { font-size: 1.75rem; line-height: 1.05; }
        .overview-panel { display: grid; gap: 32px; padding: 0 46px 40px; }
        .overview-copy h2, .gallery-section h2, .booking-copy h2 { font-size: clamp(1.9rem, 3.5vw, 2.3rem); margin-bottom: 18px; color: var(--text); }
        .overview-copy p { color: var(--text-muted); font-size: 1rem; line-height: 1.9; margin-bottom: 12px; }
        .overview-highlights { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 24px; }
        .spec-panel, .feature-panel { background: var(--surface); border: 1px solid var(--panel-border); border-radius: 24px; padding: 32px; }
        .spec-panel h3, .feature-panel h3 { font-size: 1.1rem; margin-bottom: 18px; color: var(--text); }
        .spec-table { width: 100%; border-collapse: collapse; }
        .spec-table th, .spec-table td { padding: 0.9rem 0; font-size: 0.96rem; color: var(--text-muted); vertical-align: top; }
        .spec-table th { width: 42%; color: var(--accent-2); font-weight: 600; }
        .spec-table tr:not(:last-child) td, .spec-table tr:not(:last-child) th { border-bottom: 1px solid var(--panel-border); }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; }
        .feature-badge { padding: 14px 18px; border-radius: 999px; border: 1px solid var(--panel-border); background: var(--surface-strong); color: var(--text); font-size: 0.93rem; }
        .gallery-section { padding: 0 46px 40px; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .gallery-item { overflow: hidden; border-radius: 22px; border: 1px solid var(--panel-border); background: var(--surface-strong); }
        .gallery-item img { width: 100%; height: 220px; object-fit: contain; transition: transform 0.35s ease; }
        .gallery-item img:hover { transform: scale(1.06); }

        /* ── Appointment Booking Form ── */
        .booking-panel {
            background: var(--surface);
            border: 1px solid var(--panel-border);
            border-radius: 28px;
            padding: 42px 46px;
            display: grid;
            gap: 26px;
            width: min(100%, 820px);
            margin: 0 auto;
        }
        .booking-copy p { color: var(--text-muted); line-height: 1.8; max-width: 760px; }
        .form-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .booking-panel label {
            display: block; font-weight: 600; color: var(--text); font-size: 0.92rem; margin-bottom: 6px;
        }
        .booking-panel input,
        .booking-panel select,
        .booking-panel textarea {
            width: 100%; border-radius: 16px; border: 1px solid var(--border-strong);
            background: var(--surface-strong); color: var(--text); padding: 16px 18px;
            font-size: 15px; font-family: inherit;
        }
        .booking-panel input:focus,
        .booking-panel select:focus,
        .booking-panel textarea:focus { outline: none; border-color: var(--accent-2); }
        .booking-panel select { appearance: none; cursor: pointer; }
        .booking-panel textarea { min-height: 120px; resize: vertical; }
        .booking-panel button { width: fit-content; padding: 14px 40px; }
        .booking-success {
            background: rgba(74,222,128,.1); border: 1px solid rgba(74,222,128,.35);
            color: #4ade80; padding: 16px 20px; border-radius: 14px; font-weight: 600;
        }
        .booking-error {
            background: rgba(220,38,38,.1); border: 1px solid rgba(220,38,38,.35);
            color: #ff8a8a; padding: 16px 20px; border-radius: 14px; font-weight: 600;
        }

        @media (max-width: 1120px) { .details-hero { grid-template-columns: 1fr; } .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .overview-highlights { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .details-page { padding: 60px 4% 60px; } .details-panel { border-radius: 24px; } .details-hero-copy h1 { font-size: 2.2rem; } .details-hero-media { min-height: 260px; max-height: 320px; } .gallery-item img { height: 180px; } .details-actions { grid-template-columns: 1fr; gap: 12px; } .details-actions > a, .details-actions > button { width: 100%; } .booking-panel { width: 100%; padding: 30px 20px; } .details-hero { gap: 18px; } .details-hero-media { min-height: 240px; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo"><i class="fa-solid fa-bolt"></i> HONUS AUTOS</a>
        <ul class="nav-links">
            <li><a href="index.php#home">Home</a></li>
            <li><a href="inventory.php">Inventory</a></li>
            <li><a href="index.php#about">About</a></li>
            <li><a href="index.php#services">Services</a></li>
            <li><a href="index.php#reviews">Reviews</a></li>
            <li><a href="index.php#contact">Contact</a></li>
            <?php if (is_logged_in()): ?>
                <li><a href="my-appointments.php">My Appointments</a></li>
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

    <?php if (!$car): ?>
        <section class="details-page">
            <div class="details-panel not-found">
                <h1>Vehicle Not Found</h1>
                <p>The vehicle you requested is unavailable or does not exist. Please return to the inventory to choose another model.</p>
                <a class="btn-white" href="inventory.php">Back to Inventory</a>
            </div>
        </section>
    <?php else: ?>
        <section class="details-page">
            <div class="details-panel">
                <div class="details-hero">
                    <div class="details-hero-copy">
                        <p class="eyebrow"><?php echo htmlspecialchars($car['tagline']); ?></p>
                        <h1><?php echo htmlspecialchars($car['name']); ?></h1>
                        <p class="subtext"><?php echo htmlspecialchars($car['year']); ?> · <?php echo htmlspecialchars($car['mileage']); ?> · <?php echo htmlspecialchars($car['status']); ?></p>
                        <div class="details-badges">
                            <span class="badge"><?php echo htmlspecialchars($car['exterior_color']); ?></span>
                            <span class="badge"><?php echo htmlspecialchars($car['interior_color']); ?></span>
                        </div>

                        <div class="details-actions">
                            <a class="btn-white" href="#booking-form"><i class="fa fa-calendar-check"></i> Book Appointment</a>
                            <a class="btn-outline" href="inventory.php"><i class="fa fa-arrow-left"></i> Back to Inventory</a>
                        </div>
                    </div>
                    <div class="details-hero-media">
                        <?php $heroImage = first_car_image($car); ?>
                        <img src="<?php echo htmlspecialchars(car_image_url($heroImage)); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>">
                        <div class="price-chip"><?php echo htmlspecialchars($car['price']); ?></div>
                    </div>
                </div>

                <div class="stat-grid">
                    <div class="stat-card"><span>0-60 mph</span><strong><?php echo htmlspecialchars($car['zero_to_sixty']); ?></strong></div>
                    <div class="stat-card"><span>Horsepower</span><strong><?php echo htmlspecialchars($car['horsepower']); ?></strong></div>
                    <div class="stat-card"><span>Top Speed</span><strong><?php echo htmlspecialchars($car['top_speed']); ?></strong></div>
                    <div class="stat-card"><span>Drivetrain</span><strong><?php echo htmlspecialchars($car['drivetrain']); ?></strong></div>
                </div>

                <div class="overview-panel">
                    <div class="overview-copy">
                        <h2>Overview</h2>
                        <p><?php echo htmlspecialchars($car['description']); ?></p>
                        <p><?php echo htmlspecialchars($car['description_long']); ?></p>
                    </div>
                    <div class="overview-highlights">
                        <div class="spec-panel">
                            <h3>Key Specs</h3>
                            <table class="spec-table">
                                <tr><th>Engine</th><td><?php echo htmlspecialchars($car['engine']); ?></td></tr>
                                <tr><th>Transmission</th><td><?php echo htmlspecialchars($car['transmission']); ?></td></tr>
                                <tr><th>Exterior</th><td><?php echo htmlspecialchars($car['exterior_color']); ?></td></tr>
                                <tr><th>Interior</th><td><?php echo htmlspecialchars($car['interior_color']); ?></td></tr>
                                <tr><th>Torque</th><td><?php echo htmlspecialchars($car['torque']); ?></td></tr>
                            </table>
                        </div>
                        <div class="feature-panel">
                            <h3>Luxury Features</h3>
                            <div class="features-grid">
                                <?php foreach ($car['features'] as $feature): ?>
                                    <div class="feature-badge"><?php echo htmlspecialchars($feature); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (count($car['images']) > 1): ?>
                <div class="gallery-section">
                    <h2>Gallery</h2>
                    <div class="gallery-grid">
                        <?php foreach ($car['images'] as $img): ?>
                            <div class="gallery-item"><img src="<?php echo htmlspecialchars(car_image_url($img)); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>"></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── APPOINTMENT BOOKING FORM ── -->
                <div class="booking-panel" id="booking-form">
                    <div class="booking-copy">
                        <h2>Book an Appointment</h2>
                        <p>Schedule a test drive, vehicle viewing, or general inquiry for the <?php echo htmlspecialchars($car['name']); ?>. We'll review your request and confirm availability.</p>
                    </div>

                    <?php if ($apptSuccess): ?>
                        <div class="booking-success">
                            <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($apptSuccess); ?>
                            <br><a href="my-appointments.php" style="color:var(--accent-2);margin-top:8px;display:inline-block;">View My Appointments →</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($apptError): ?>
                        <div class="booking-error">
                            <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($apptError); ?>
                        </div>
                    <?php endif; ?>

                    <form action="car-details.php?id=<?php echo (int)$car['id']; ?>#booking-form" method="POST">
                        <input type="hidden" name="car_id" value="<?php echo (int)$car['id']; ?>">

                        <div class="form-row">
                            <div>
                                <label for="appt-name">Full Name *</label>
                                <input type="text" id="appt-name" name="name" placeholder="Your full name"
                                    value="<?php echo htmlspecialchars($_SESSION['username'] ?? $_POST['name'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="appt-email">Email Address *</label>
                                <input type="email" id="appt-email" name="email" placeholder="you@example.com"
                                    value="<?php echo htmlspecialchars($_SESSION['email'] ?? $_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="appt-phone">Phone Number *</label>
                                <input type="tel" id="appt-phone" name="phone" placeholder="+234 XXX XXX XXXX"
                                    value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="appt-type">Appointment Type *</label>
                                <select id="appt-type" name="appointment_type" required>
                                    <option value="Test Drive" <?php echo ($_POST['appointment_type'] ?? '') === 'Test Drive' ? 'selected' : ''; ?>>Test Drive</option>
                                    <option value="Vehicle Viewing" <?php echo ($_POST['appointment_type'] ?? '') === 'Vehicle Viewing' ? 'selected' : ''; ?>>Vehicle Viewing</option>
                                    <option value="General Inquiry" <?php echo ($_POST['appointment_type'] ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="appt-date">Preferred Date *</label>
                                <input type="date" id="appt-date" name="appointment_date"
                                    value="<?php echo htmlspecialchars($_POST['appointment_date'] ?? date('Y-m-d')); ?>"
                                    min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div>
                                <label for="appt-time">Preferred Time *</label>
                                <input type="time" id="appt-time" name="appointment_time"
                                    value="<?php echo htmlspecialchars($_POST['appointment_time'] ?? '10:00'); ?>" required>
                            </div>
                        </div>

                        <div>
                            <label for="appt-message">Additional Message</label>
                            <textarea id="appt-message" name="message" placeholder="Any special requests or questions about this vehicle..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn-white">
                            <i class="fa-solid fa-calendar-check"></i> Book Appointment
                        </button>
                    </form>
                </div>
            </div>
        </section>
    <?php endif; ?>

</body>
</html>
