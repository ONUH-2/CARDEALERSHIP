<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/cars.php';

$allCars = get_all_cars();
$featuredCars = array_slice($allCars, 0, 6);
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Honus Autos | Luxury Car Dealership</title>
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
            --shadow: 0 25px 65px rgba(0,0,0,.55);
            --glow: 0 0 0 1px rgba(255,91,46,.3), 0 20px 45px rgba(255,91,46,.16);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }
        h1, h2, h3, h4 { font-family: 'Rajdhani', sans-serif; font-weight: 700; }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 8%;
            background: rgba(8, 9, 15, 0.82);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(18px);
        }
        .logo { display: inline-flex; align-items: center; gap: 8px; font-size: 1.35rem; font-weight: 700; letter-spacing: 0.2em; background: var(--accent-grad); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .nav-links { display: flex; gap: 22px; list-style: none; flex-wrap: wrap; }
        .nav-links a { color: var(--muted); font-weight: 600; position: relative; padding-bottom: 4px; }
        .nav-links a::after { content: ""; position: absolute; left: 0; bottom: 0; width: 0; height: 2px; background: var(--accent-grad); transition: width .25s ease; }
        .nav-links a:hover, .user-logout:hover { color: var(--text); }
        .nav-links a:hover::after { width: 100%; }
        .nav-cta, .btn-white, button.hero-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 13px 24px; border-radius: 999px; font-weight: 800; transition: all 0.24s ease;
            background: var(--accent-grad); color: #0a0a0d; border: none; cursor: pointer;
            box-shadow: 0 12px 30px rgba(255, 91, 46, .28);
        }
        .nav-cta:hover, .btn-white:hover, button.hero-btn:hover { transform: translateY(-2px); box-shadow: 0 16px 38px rgba(255,91,46,.42); }
        .btn-outline { border: 1px solid var(--border-strong); background: transparent; color: var(--text); padding: 13px 24px; border-radius: 999px; font-weight: 700; display:inline-flex; align-items:center; gap:8px; transition: all .22s ease; }
        .btn-outline:hover { border-color: var(--accent-2); color: var(--accent-2); }

        /* HERO */
        .hero {
            min-height: 92vh;
            display: grid;
            align-items: center;
            padding: 70px 8% 60px;
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(100deg, rgba(6,7,12,.92) 15%, rgba(6,7,12,.55) 65%, rgba(6,7,12,.85) 100%),
                url('images/begin.jpg') center/cover no-repeat;
        }
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(115deg, rgba(255,91,46,.07) 0 2px, transparent 2px 90px),
                repeating-linear-gradient(65deg, rgba(25,201,255,.05) 0 2px, transparent 2px 120px);
            animation: driftLines 24s linear infinite;
            pointer-events: none;
        }
        @keyframes driftLines { from { background-position: 0 0, 0 0; } to { background-position: 420px 0, -420px 0; } }
        .hero-content { max-width: 640px; position: relative; z-index: 2; }
        .eyebrow-tag {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; border-radius: 999px;
            border: 1px solid var(--border-strong); background: rgba(255,91,46,.08); color: var(--accent-2);
            font-weight: 700; letter-spacing: .16em; text-transform: uppercase; font-size: .75rem; margin-bottom: 22px;
        }
        .hero-content h1 { font-size: clamp(2.8rem, 5.4vw, 4.8rem); line-height: 1.02; margin-bottom: 18px; }
        .hero-content h1 span { display: block; background: var(--accent-grad); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .hero-content p { font-size: 1.08rem; color: var(--muted); margin-bottom: 28px; max-width: 520px; }
        .hero-buttons { display: flex; gap: 14px; flex-wrap: wrap; }

        /* SECTIONS */
        .stats, .inventory, .about, .why-us, .services, .brands, .testimonials, .contact, footer { padding: 80px 8%; position: relative; z-index: 2; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; background: var(--surface); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding-top:56px; padding-bottom:56px; }
        .stat-box { text-align: center; padding: 10px; }
        .stat-box h2 { background: var(--accent-grad); -webkit-background-clip: text; background-clip: text; color: transparent; font-size: 2.1rem; margin-bottom: 8px; }
        .stat-box p { color: var(--muted); font-weight: 600; letter-spacing: .03em; }

        .title { text-align: center; max-width: 700px; margin: 0 auto 40px; }
        .title .eyebrow-tag { margin-bottom: 16px; }
        .title h2 { font-size: clamp(1.9rem, 3vw, 2.6rem); margin-bottom: 10px; }
        .title p { color: var(--muted); }

        .car-grid, .why-grid, .service-grid, .testimonial-grid { display: grid; gap: 24px; }
        .car-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .why-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .service-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .testimonial-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }

        .car-card, .why-card, .service-card, .testimonial-card {
            background: linear-gradient(180deg, var(--surface) 0%, var(--surface-alt) 100%);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 26px;
            opacity: 0; transform: translateY(24px);
            transition: opacity 0.7s ease, transform 0.7s ease, border-color .3s ease, box-shadow .3s ease;
        }
        .car-card { padding: 0; overflow: hidden; display: flex; flex-direction: column; }
        .car-card.reveal, .why-card.reveal, .service-card.reveal, .testimonial-card.reveal { opacity: 1; transform: translateY(0); }
        .car-card:hover, .why-card:hover, .service-card:hover { transform: translateY(-6px); border-color: rgba(255,91,46,.4); box-shadow: var(--glow); }

        .car-image { width: 100%; aspect-ratio: 16/10; overflow: hidden; background: var(--surface-strong); position: relative; }
        .car-image img { width: 100%; height: 100%; object-fit: contain; transition: transform .4s ease; }
        .car-card:hover .car-image img { transform: scale(1.05); }
        .car-info { padding: 26px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
        .car-info h3 { font-size: 1.3rem; }
        .car-info p.model-year { color: var(--muted); font-size: .92rem; }
        .car-info h4 { color: var(--accent-2); margin: 4px 0 12px; font-size: 1.2rem; }
        .car-info .btn-outline { margin-top: auto; width: 100%; justify-content: center; }

        .why-card, .service-card { text-align: left; }
        .why-card i, .service-card i { color: var(--accent-2); font-size: 1.5rem; margin-bottom: 14px; display: block; }
        .why-card h3, .service-card h3 { margin-bottom: 8px; font-size: 1.2rem; }
        .why-card p, .service-card p { color: var(--muted); }

        .about { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center; }
        .about-visual { border-radius: 26px; box-shadow: var(--shadow); overflow: hidden; border: 1px solid var(--border); position: relative; }
        .about-visual::after { content:""; position:absolute; inset:0; box-shadow: inset 0 0 0 1px rgba(255,91,46,.15); border-radius:26px; }
        .about-visual img { width: 100%; height: 100%; max-height: 460px; object-fit: cover; }
        .about-content .eyebrow-tag { margin-bottom: 16px; }
        .about-content h2 { font-size: clamp(1.9rem, 3vw, 2.5rem); margin-bottom: 16px; }
        .about-content p { color: var(--muted); margin-bottom: 20px; }
        .about-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 4px 0 26px; }
        .about-card { background: var(--surface); border: 1px solid var(--border); border-radius: 18px; padding: 18px; }
        .about-card h3 { font-size: 1rem; margin-bottom: 6px; color: var(--accent-2); }
        .about-card p { color: var(--muted); font-size: .9rem; margin: 0; }

        /* BRANDS MARQUEE */
        .brands { padding-top: 60px; padding-bottom: 60px; }
        .marquee { overflow: hidden; position: relative; margin-top: 30px; -webkit-mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent); mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent); }
        .marquee-track { display: flex; gap: 60px; width: max-content; animation: marquee 26s linear infinite; }
        .marquee-track span { font-family: 'Rajdhani', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--muted); letter-spacing: .08em; white-space: nowrap; }
        @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        .testimonial-card { position: relative; }
        .testimonial-card i.fa-quote-left { color: rgba(255,91,46,.35); font-size: 1.6rem; margin-bottom: 14px; display: block; }
        .testimonial-card p { color: var(--muted); margin-bottom: 16px; font-style: italic; }
        .testimonial-card .stars { color: var(--accent); margin-bottom: 10px; letter-spacing: 2px; }
        .testimonial-card h3 { font-size: 1rem; color: var(--text); }

        .contact { max-width: 900px; margin: 0 auto; }
        .contact form { display: grid; gap: 14px; }
        input, textarea { width: 100%; padding: 15px 18px; border: 1px solid var(--border-strong); border-radius: 14px; background: var(--surface-alt); color: var(--text); font: inherit; }
        input:focus, textarea:focus { outline: none; border-color: var(--accent-2); }
        textarea { min-height: 140px; resize: vertical; }

        footer { background: var(--bg); border-top: 1px solid var(--border); padding-bottom: 40px; }
        .footer-container { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 30px; }
        .footer-box h3 { margin-bottom: 14px; }
        .footer-box p, .footer-box a { color: var(--muted); display:block; margin-bottom:8px; }
        .footer-box a:hover { color: var(--accent-2); }
        hr { border: none; border-top: 1px solid var(--border); margin: 24px 0; }
        .copyright { text-align: center; color: var(--muted); font-size: .9rem; }

        @media (max-width: 1024px) { .stats, .why-grid, .service-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .car-grid, .testimonial-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .about { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .navbar { flex-direction: column; gap: 12px; padding: 16px 6%; } .stats, .car-grid, .why-grid, .service-grid, .testimonial-grid, .footer-container, .about-grid { grid-template-columns: 1fr; } .hero { min-height: auto; padding-top: 50px; } }
    </style>

</head>

<body>

    <nav class="navbar">
        <a href="#home" class="logo"><i class="fa-solid fa-bolt"></i> HONUS AUTOS</a>
        <ul class="nav-links">
            <li><a href="#home">Home</a></li>
            <li><a href="inventory.php">Inventory</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#reviews">Reviews</a></li>
            <li><a href="#contact">Contact</a></li>
            <?php if (is_logged_in()): ?>
                <li><a href="my-appointments.php">My Appointments<?php $navCount = get_nav_appt_count(); if ($navCount > 0): ?> <span style="display:inline-flex;min-width:18px;height:18px;padding:0 5px;align-items:center;justify-content:center;border-radius:999px;background:var(--accent-grad);color:#0a0a0d;font-size:0.7rem;margin-left:4px;vertical-align:middle;font-weight:800;"><?php echo $navCount; ?></span><?php endif; ?></a></li>
            <?php endif; ?>
            <?php if (is_admin_user()): ?>
                <li><a href="admin/dashboard.php">Admin</a></li>
            <?php endif; ?>
        </ul>
        <?php if (!empty($_SESSION['username'])): ?>
            <div class="user-area" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span style="font-weight:600;display:inline-flex;align-items:center;gap:8px;"><i class="fa fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a class="user-logout" style="color:var(--accent-2);font-weight:600;display:inline-flex;align-items:center;gap:8px;" href="login/logout.php"><i class="fa fa-sign-out-alt"></i> Log out</a>
            </div>
        <?php else: ?>
            <a id="signupBtn" class="nav-cta" href="login/login.php?form=signup"><i class="fa fa-user-plus"></i> Sign Up</a>
        <?php endif; ?>
    </nav>

    <section class="hero" id="home">
        <div class="hero-content">
            <span class="eyebrow-tag"><i class="fa-solid fa-gauge-high"></i> Premium Since Day One</span>
            <h1>DRIVE<span>WITHOUT LIMITS</span></h1>
            <p>Premium luxury vehicles. Professional service. An exceptional driving experience, every time.</p>
            <div class="hero-buttons">
                <a href="inventory.php" class="hero-btn btn-white">View Inventory</a>
                <a href="#contact" class="btn-outline">Book Appointment</a>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="stat-box"><h2>500+</h2><p>Luxury Cars Sold</p></div>
        <div class="stat-box"><h2>15+</h2><p>Years Experience</p></div>
        <div class="stat-box"><h2>100%</h2><p>Customer Satisfaction</p></div>
        <div class="stat-box"><h2>24/7</h2><p>Customer Support</p></div>
    </section>

    <section class="inventory" id="inventory">
        <div class="title">
            <span class="eyebrow-tag"><i class="fa-solid fa-car"></i> Featured Collection</span>
            <h2>Handpicked For You</h2>
            <p>Discover a carefully selected collection of luxury vehicles.</p>
        </div>

        <div class="car-grid">
            <?php if (!$featuredCars): ?>
                <div style="grid-column:1/-1;text-align:center;color:var(--muted);padding:40px;border:1px dashed var(--border-strong);border-radius:20px;">
                    No vehicles in inventory yet. Check back soon.
                </div>
            <?php else: foreach ($featuredCars as $car): $image = first_car_image($car); ?>
                <div class="car-card">
                    <div class="car-image">
                        <img src="<?php echo htmlspecialchars(car_image_url($image)); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>">
                    </div>
                    <div class="car-info">
                        <h3><?php echo htmlspecialchars($car['name']); ?></h3>
                        <p class="model-year"><?php echo htmlspecialchars($car['year']); ?> Model</p>
                        <h4><?php echo htmlspecialchars($car['price']); ?></h4>
                        <a href="car-details.php?id=<?php echo (int)$car['id']; ?>" class="btn-outline">Explore Now</a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </section>

    <section class="about" id="about">
        <div class="about-visual">
            <img src="images/aboutuspicture.jpg" alt="Akaza's Motors showroom">
        </div>
        <div class="about-content">
            <span class="eyebrow-tag"><i class="fa-solid fa-award"></i> Our Promise</span>
            <h2>Where Passion Meets Performance</h2>
            <p>At Honus Autos, we curate elite vehicles for drivers who demand precision, luxury, and iconic style. Every model is chosen for performance, craftsmanship, and unforgettable presence on the road.</p>
            <div class="about-grid">
                <div class="about-card">
                    <h3>Premium Selection</h3>
                    <p>Hand-picked luxury vehicles from world-class brands.</p>
                </div>
                <div class="about-card">
                    <h3>White-glove Service</h3>
                    <p>Personalized support from browsing to delivery.</p>
                </div>
            </div>
            <a href="inventory.php" class="btn-white">Explore Collection</a>
        </div>
    </section>

    <section class="why-us">
        <div class="title">
            <span class="eyebrow-tag"><i class="fa-solid fa-shield-halved"></i> Why Us</span>
            <h2>Why Choose Honus Autos?</h2>
            <p>Experience professionalism, trust, and luxury.</p>
        </div>
        <div class="why-grid">
            <div class="why-card"><i class="fa-solid fa-star"></i><h3>Luxury Inventory</h3><p>Hand-picked premium vehicles from trusted manufacturers.</p></div>
            <div class="why-card"><i class="fa-solid fa-hand-holding-dollar"></i><h3>Affordable Financing</h3><p>Flexible financing plans designed for every customer.</p></div>
            <div class="why-card"><i class="fa-solid fa-clipboard-check"></i><h3>Certified Vehicles</h3><p>Every vehicle undergoes professional inspection before sale.</p></div>
            <div class="why-card"><i class="fa-solid fa-user-tie"></i><h3>Trusted Experts</h3><p>Experienced professionals ready to assist you anytime.</p></div>
        </div>
    </section>

    <section class="services" id="services">
        <div class="title">
            <span class="eyebrow-tag"><i class="fa-solid fa-toolbox"></i> Services</span>
            <h2>Our Services</h2>
        </div>
        <div class="service-grid">
            <div class="service-card"><i class="fa-solid fa-car-side"></i><h3>Luxury Car Sales</h3><p>Discover the latest premium vehicles available today.</p></div>
            <div class="service-card"><i class="fa-solid fa-file-invoice-dollar"></i><h3>Vehicle Financing</h3><p>Convenient payment options with competitive rates.</p></div>
            <div class="service-card"><i class="fa-solid fa-right-left"></i><h3>Trade-In Service</h3><p>Upgrade your vehicle with our trade-in program.</p></div>
            <div class="service-card"><i class="fa-solid fa-screwdriver-wrench"></i><h3>Maintenance</h3><p>Professional servicing performed by certified technicians.</p></div>
        </div>
    </section>

    <section class="brands">
        <div class="title">
            <span class="eyebrow-tag"><i class="fa-solid fa-crown"></i> Trusted Brands</span>
            <h2>Luxury Brands We Carry</h2>
        </div>
        <div class="marquee">
            <div class="marquee-track">
                <span>TOYOTA</span><span>MERCEDES-BENZ</span><span>LEXUS</span><span>PORSCHE</span><span>LEXUS</span>
                <span>TOYOTA</span><span>MERCEDES-BENZ</span><span>LEXUS</span><span>PORSCHE</span><span>LEXUS</span>
            </div>
        </div>
    </section>

    <section class="testimonials" id="reviews">
        <div class="title">
            <span class="eyebrow-tag"><i class="fa-solid fa-comment"></i> Testimonials</span>
            <h2>What Our Customers Say</h2>
            <p>Trusted by luxury car enthusiasts.</p>
        </div>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <i class="fa-solid fa-quote-left"></i>
                <div class="stars">★★★★★</div>
                <p>"Outstanding customer service. Buying my dream car was simple and stress-free."</p>
                <h3>— Michael Johnson</h3>
            </div>
            <div class="testimonial-card">
                <i class="fa-solid fa-quote-left"></i>
                <div class="stars">★★★★★</div>
                <p>"Professional staff and premium vehicles. Highly recommended."</p>
                <h3>— Sarah Williams</h3>
            </div>
            <div class="testimonial-card">
                <i class="fa-solid fa-quote-left"></i>
                <div class="stars">★★★★★</div>
                <p>"One of the best luxury dealerships I've visited. Amazing experience."</p>
                <h3>— David Brown</h3>
            </div>
        </div>
    </section>

    <section class="contact" id="contact">
        <div class="title">
            <span class="eyebrow-tag"><i class="fa-solid fa-calendar-check"></i> Get In Touch</span>
            <h2>Book An Appointment</h2>
            <p>We'd love to help you find your perfect vehicle.</p>
        </div>
        <form>
            <input type="text" placeholder="Full Name">
            <input type="email" placeholder="Email Address">
            <input type="text" placeholder="Phone Number">
            <textarea placeholder="Tell us which vehicle you're interested in..."></textarea>
            <button type="submit" class="hero-btn btn-white">Submit</button>
        </form>
    </section>

    <script>
        const revealItems = document.querySelectorAll('.car-card, .why-card, .service-card, .testimonial-card');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    entry.target.style.transitionDelay = (i % 4) * 0.08 + 's';
                    entry.target.classList.add('reveal');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        revealItems.forEach((item) => revealObserver.observe(item));
    </script>

    <footer>
        <div class="footer-container">
            <div class="footer-box">
                <h3>HONUS AUTOS</h3>
                <p>Luxury. Performance. Excellence.</p>
            </div>
            <div class="footer-box">
                <h3>Quick Links</h3>
                <a href="#home">Home</a>
                <a href="inventory.php">Inventory</a>
                <a href="#about">About</a>
                <a href="#services">Services</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="footer-box">
                <h3>Contact</h3>
                <p>Lagos, Nigeria</p>
                <p>+234 000 000 0000</p>
                <p>info@akazasmotors.com</p>
            </div>
        </div>
        <hr>
        <p class="copyright">© 2026 Honus Autos. All Rights Reserved.</p>
    </footer>

</body>

</html>
