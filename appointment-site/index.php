<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/helpers.php';

$vehicles = [];
$result = $conn->query("SELECT id, name, year, category, price_label, image_url, description FROM vehicles WHERE is_available = 1 ORDER BY created_at DESC LIMIT 6");
if ($result) {
    while ($row = $result->fetch_assoc()) $vehicles[] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoMeet | See it. Drive it. Decide with confidence.</title>
    <meta name="description" content="Book a relaxed vehicle viewing or test drive with AutoMeet.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="index.php"><span class="brand-mark">A</span><span>Auto<span class="text-blue">Meet</span></span></a>
        <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">☰</button>
        <nav class="nav-links">
            <a href="#vehicles">Vehicles</a><a href="#how-it-works">How it works</a><a href="#why-us">Why AutoMeet</a>
            <a class="button button-small" href="#vehicles">Book an appointment</a>
        </nav>
    </div>
</header>

<main>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <p class="eyebrow"><span></span> A better way to meet your next car</p>
            <h1>See it. <span class="text-blue">Drive it.</span> Decide with confidence.</h1>
            <p class="hero-text">Browse a curated selection of vehicles, then book a private viewing or a test drive at a time that works for you. No pressure. No checkout buttons. Just a better first step.</p>
            <div class="hero-actions"><a class="button" href="#vehicles">Explore vehicles <span>→</span></a><a class="text-link" href="#how-it-works">How it works <span>↗</span></a></div>
            <div class="hero-proof"><div class="proof-avatars"><span>JM</span><span>AK</span><span>TS</span></div><p><strong>4.9/5</strong> from recent visitors<br><small>Friendly, no-pressure appointments</small></p></div>
        </div>
        <div class="hero-visual"><div class="hero-glow"></div><img src="https://images.unsplash.com/photo-1553440569-bcc63803a83d?auto=format&fit=crop&w=1400&q=85" alt="Blue luxury car parked outdoors"><div class="floating-card"><span class="status-dot"></span><div><strong>Appointments made simple</strong><small>Choose a car, time and experience.</small></div></div></div>
    </div>
</section>

<section class="trusted"><div class="container trusted-inner"><span>Designed for a calmer car search</span><div><b>VIEW</b><b>TEST DRIVE</b><b>NO PRESSURE</b><b>YOUR TIME</b></div></div></section>

<section class="section" id="vehicles"><div class="container"><div class="section-heading"><div><p class="eyebrow">Find your next drive</p><h2>Vehicles worth seeing in person.</h2></div><a class="text-link" href="#vehicles">View all vehicles <span>→</span></a></div>
    <?php if (!$vehicles): ?><div class="empty-state">No vehicles are available yet. Add sample vehicles by importing <code>database.sql</code>.</div><?php else: ?>
    <div class="vehicle-grid">
        <?php foreach ($vehicles as $vehicle): ?>
        <article class="vehicle-card"><div class="vehicle-image"><img src="<?= e(vehicle_image($vehicle['image_url'])) ?>" alt="<?= e($vehicle['name']) ?>"><span class="vehicle-badge">Available to view</span></div><div class="vehicle-body"><div class="vehicle-meta"><span><?= e($vehicle['year']) ?> · <?= e($vehicle['category']) ?></span><span class="green-dot">●</span></div><h3><?= e($vehicle['name']) ?></h3><p><?= e($vehicle['description']) ?></p><div class="vehicle-footer"><strong><?= e($vehicle['price_label']) ?></strong><a href="book.php?vehicle_id=<?= (int)$vehicle['id'] ?>">Book now <span>→</span></a></div></div></article>
        <?php endforeach; ?>
    </div><?php endif; ?>
</div></section>

<section class="section soft-section" id="how-it-works"><div class="container"><div class="section-heading centered"><div><p class="eyebrow">A simple three-step experience</p><h2>Your time. Your choice. Your drive.</h2></div></div><div class="steps"><div class="step"><span>01</span><h3>Choose a vehicle</h3><p>Find a car that catches your eye and explore the details before you visit.</p></div><div class="step"><span>02</span><h3>Pick your experience</h3><p>Book a relaxed viewing or get behind the wheel for a test drive.</p></div><div class="step"><span>03</span><h3>Meet us in person</h3><p>We will have the vehicle ready at your selected date and time.</p></div></div></div></section>

<section class="section split-section" id="why-us"><div class="container split-grid"><div class="split-image"><img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85" alt="Car on a scenic road"></div><div><p class="eyebrow">The AutoMeet difference</p><h2>Car shopping should feel like a conversation.</h2><p class="body-copy">We built AutoMeet for the part of the car journey that matters most: seeing how a vehicle looks, feels and fits your life before you make a decision.</p><div class="benefits"><div><span>✓</span><p><strong>No online sales</strong><small>There is no pressure to buy from this website.</small></p></div><div><span>✓</span><p><strong>Real appointments</strong><small>Choose a time that works for your schedule.</small></p></div><div><span>✓</span><p><strong>Helpful people</strong><small>Ask questions and take your time with the car.</small></p></div></div><a class="button" href="#vehicles">Start exploring <span>→</span></a></div></div></section>
</main>
<footer><div class="container footer-inner"><div><a class="brand" href="index.php"><span class="brand-mark">A</span><span>Auto<span class="text-blue">Meet</span></span></a><p>Make your next car decision in person.</p></div><div class="footer-note">© <?= date('Y') ?> AutoMeet · Appointment-only vehicle experiences</div></div></footer>
<script src="assets/app.js"></script>
</body></html>
