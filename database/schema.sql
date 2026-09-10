-- ============================================================
-- Honus Autos — Complete Database Schema
-- ============================================================
-- Run this once to set up the dealership database.
-- It creates the database, tables, migrates existing users
-- from the form_data database, and seeds starter inventory.
-- ============================================================

CREATE DATABASE IF NOT EXISTS cardealership
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE cardealership;

-- ------------------------------------------------------------
-- 1. USERS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_data (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    fullname  VARCHAR(100)  NOT NULL,
    username  VARCHAR(50)   NOT NULL UNIQUE,
    email     VARCHAR(100)  NOT NULL UNIQUE,
    password  VARCHAR(255)  NOT NULL,
    regdate   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. CARS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cars (
    id              INT           PRIMARY KEY,
    name            VARCHAR(150)  NOT NULL,
    tagline         VARCHAR(255)  DEFAULT NULL,
    year            VARCHAR(20)   DEFAULT NULL,
    price           VARCHAR(50)   DEFAULT NULL,
    mileage         VARCHAR(50)   DEFAULT NULL,
    exterior_color  VARCHAR(80)   DEFAULT NULL,
    interior_color  VARCHAR(80)   DEFAULT NULL,
    status          VARCHAR(50)   DEFAULT 'In Stock',
    engine          VARCHAR(120)  DEFAULT NULL,
    horsepower      VARCHAR(60)   DEFAULT NULL,
    torque          VARCHAR(60)   DEFAULT NULL,
    transmission    VARCHAR(80)   DEFAULT NULL,
    drivetrain      VARCHAR(80)   DEFAULT NULL,
    top_speed       VARCHAR(80)   DEFAULT NULL,
    zero_to_sixty   VARCHAR(80)   DEFAULT NULL,
    features        TEXT          DEFAULT NULL,
    description     TEXT          DEFAULT NULL,
    description_long TEXT         DEFAULT NULL,
    images          TEXT          DEFAULT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. ORDERS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT           NOT NULL,
    car_id          INT           NOT NULL,
    amount          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_method  VARCHAR(50)   DEFAULT NULL,
    payment_status  VARCHAR(30)   DEFAULT 'Pending',
    order_status    VARCHAR(40)   DEFAULT 'Processing',
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user  (user_id),
    INDEX idx_car   (car_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. APPOINTMENTS TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    car_id            INT NOT NULL,
    customer_name     VARCHAR(120) NOT NULL,
    customer_email    VARCHAR(160) NOT NULL,
    customer_phone    VARCHAR(40) NOT NULL,
    appointment_date  DATE NOT NULL,
    appointment_time  TIME NOT NULL,
    appointment_type  ENUM('Viewing', 'Test Drive') NOT NULL DEFAULT 'Viewing',
    message           TEXT DEFAULT NULL,
    status            ENUM('Pending', 'Approved', 'Confirmed', 'Completed', 'Rejected', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_appointments_user (user_id),
    INDEX idx_appointments_car (car_id),
    INDEX idx_appointments_date (appointment_date)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. MIGRATE EXISTING USERS from form_data.user_data
-- ------------------------------------------------------------
INSERT IGNORE INTO user_data (id, fullname, username, email, password, regdate)
SELECT
    fd.id,
    COALESCE(NULLIF(TRIM(fd.firstname), ''), fd.username) AS fullname,
    fd.username,
    fd.email,
    fd.password,
    fd.regdate
FROM form_data.user_data AS fd
WHERE fd.email NOT IN ('', 'emeil')
  AND fd.email LIKE '%@%';

-- AUTO_INCREMENT resets automatically after INSERT with explicit IDs

-- ------------------------------------------------------------
-- 5. SEED STARTER CARS (8 vehicles)
-- ------------------------------------------------------------
INSERT INTO cars (id, name, tagline, year, price, mileage, exterior_color, interior_color, status, engine, horsepower, torque, transmission, drivetrain, top_speed, zero_to_sixty, features, description, description_long, images, created_at) VALUES

(1, 'BMW M4 Competition',
 'A precision-engineered sports coupe with championship pace.',
 '2025', '$65,000', '8,200 miles', 'Alpine White', 'Black Leather', 'In Stock',
 '3.0L Twin-Turbo I6', '510 hp', '479 lb-ft', '8-Speed Automatic', 'RWD',
 '189 mph', '3.8 sec',
 '["Carbon Fiber Package","Adaptive Suspension","Premium Harman Kardon Audio","Heated and Ventilated Seats"]',
 'The BMW M4 Competition is crafted for drivers who demand both ruthless performance and everyday luxury. Its high-revving twin-turbo engine delivers storming acceleration, while the finely tuned chassis offers precise control for canyon runs and highways alike.',
 'Inside the cabin, premium materials and driver-focused ergonomics set a commanding tone. The advanced infotainment system and performance telemetry ensure every drive feels intelligent and connected.',
 '["images/bmw-m4-competition.webp"]',
 NOW()),

(2, 'Mercedes-Benz C63 AMG',
 'Luxury coupe engineering fused with raw AMG power.',
 '2025', '$83,000', '5,400 miles', 'Obsidian Black', 'Red/Black Nappa', 'In Stock',
 '4.0L Twin-Turbo V8', '503 hp', '516 lb-ft', '9-Speed Automatic', 'RWD',
 '180 mph', '3.9 sec',
 '["AMG Performance Exhaust","Nappa Leather Seats","Apple CarPlay / Android Auto","Multicontour Front Seats"]',
 'The C63 AMG combines upscale materials with an intoxicating V8 soundtrack. Its athletic design and aggressive stance are backed by precision engineering from Mercedes-AMG.',
 'This coupe delivers a confidence-inspiring drive whether navigating city streets or accelerating down the freeway. The intuitive cabin and premium tech reinforce its luxury performance identity.',
 '["images/mercedes-c63-amg.jpg"]',
 NOW()),

(3, 'Audi RS7',
 'Sleek, powerful, and refined for modern grand touring.',
 '2025', '$91,000', '6,100 miles', 'Matte Gray', 'Ivory Leather', 'In Stock',
 '4.0L Twin-Turbo V8', '591 hp', '590 lb-ft', '8-Speed Automatic', 'AWD',
 '190 mph', '3.5 sec',
 '["Bang & Olufsen Premium Sound","Quattro AWD","Digital Cockpit","Sport Differential"]',
 'The Audi RS7 offers immediate power delivery and sumptuous cabin refinement. Its athletic silhouette hides one of the most sophisticated performance sedans on the road.',
 'Advanced driver assists and adaptive cruise control make long journeys effortless, while its track-capable setup ensures performance remains the centerpiece.',
 '["images/audi-rs7.jpg"]',
 NOW()),

(4, 'Porsche 911 Turbo',
 'Timeless performance with surgical precision and luxury.',
 '2025', '$120,000', '3,900 miles', 'Jet Black', 'Graphite', 'In Stock',
 '3.8L Twin-Turbo Flat-6', '572 hp', '553 lb-ft', '8-Speed PDK', 'AWD',
 '198 mph', '2.7 sec',
 '["Adaptive Aerodynamics","Porsche Active Suspension","Premium Leather Interior","Sport Chrono Package"]',
 'The 911 Turbo marries Porsche heritage with blistering speed. Its trustworthy handling and luxury fit-and-finish make it a performance icon.',
 'With precise steering and immediate throttle response, this car feels both responsive and composed. Its cabin blends premium comfort with intuitive controls.',
 '["images/porsche-911-turbo.jpg"]',
 NOW()),

(5, 'Lamborghini Huracán',
 'A raging supercar with dramatic style and unrivaled excitement.',
 '2024', '$250,000', '4,200 miles', 'Arancio Borealis', 'Black Leather', 'In Stock',
 '5.2L V10', '631 hp', '417 lb-ft', '7-Speed Dual-Clutch', 'AWD',
 '202 mph', '2.9 sec',
 '["Launch Control","Sport Exhaust System","Carbon Fiber Interior","Lamborghini Infotainment System"]',
 'The Huracán is engineered to excite at every turn. Its V10 engine and angular design command attention and deliver a visceral driving experience.',
 'From its lightweight chassis to its dynamic handling, this supercar is built for drivers who value thrilling performance and bold style.',
 '["images/lamborghini-huracan.jpg"]',
 NOW()),

(6, 'Ferrari F8 Tributo',
 'Ferrari artistry fused with brutal speed and precision control.',
 '2024', '$280,000', '2,800 miles', 'Rosso Corsa', 'Black/Red', 'In Stock',
 '3.9L Twin-Turbo V8', '710 hp', '568 lb-ft', '7-Speed Dual-Clutch', 'RWD',
 '211 mph', '2.9 sec',
 '["Ferrari Drive Mode Selector","Premium Italian Leather","Adaptive Suspension","Advanced Aerodynamics Package"]',
 'This Ferrari F8 Tributo embodies exotic Italian design and race-derived power. Its focused driving dynamics deliver an unforgettable sensory experience.',
 'Precise chassis balance and edge-of-your-seat acceleration make the F8 a supercar masterpiece. The lavish interior ensures every journey feels dramatic and bespoke.',
 '["images/ferrari-f8-tributo.jpg"]',
 NOW()),

(7, 'Lexus LX 600',
 'Executive SUV comfort combined with robust luxury performance.',
 '2025', '$105,000', '7,100 miles', 'Graphite Black', 'Beige Semi-Aniline', 'In Stock',
 '3.5L Twin-Turbo V6', '409 hp', '479 lb-ft', '10-Speed Automatic', 'AWD',
 '143 mph', '6.1 sec',
 '["Executive Seating","Mark Levinson Audio","Panoramic View Monitor","Rear Seat Entertainment"]',
 'The LX 600 blends premium SUV capability with serene interior luxury. Its commanding presence is matched by a composed and quiet ride.',
 'Large, luxurious seating and advanced amenities make this SUV ideal for both family travel and executive transport.',
 '["images/lexus-lx-600.jpg"]',
 NOW()),

(8, 'Range Rover Sport',
 'A bold blend of British luxury and athletic road presence.',
 '2025', '$98,000', '5,800 miles', 'Silicon Silver', 'Ebony Leather', 'In Stock',
 '4.4L Twin-Turbo V8', '523 hp', '553 lb-ft', '8-Speed Automatic', 'AWD',
 '162 mph', '4.3 sec',
 '["Meridian Surround Sound","Air Suspension","Terrain Response 2","Configurable Ambient Lighting"]',
 'The Range Rover Sport balances rugged capability with unmistakable luxury. Its athletic stance and dynamic performance make every drive an occasion.',
 'Advanced off-road technology meets first-class comfort in a vehicle equally at home on city streets or mountain trails.',
 '["images/range-rover-sport.jpg"]',
 NOW());

ALTER TABLE cars AUTO_INCREMENT = 9;

-- ------------------------------------------------------------
-- 6. SEED SAMPLE ORDERS
--    Uses user_id 7 (admin: ytgg2909@gmail.com) and user_id 8
-- ------------------------------------------------------------
INSERT INTO orders (user_id, car_id, amount, payment_method, payment_status, order_status, created_at) VALUES
(7, 1, 65000.00,  'Bank Transfer', 'Paid',    'Completed',           '2026-07-15 10:30:00'),
(7, 4, 120000.00, 'Credit Card',   'Paid',    'Preparing Vehicle',   '2026-08-01 14:20:00'),
(8, 3, 91000.00,  'Cash',          'Pending', 'Processing',          '2026-08-10 09:15:00'),
(8, 6, 280000.00, 'Bank Transfer', 'Paid',    'Ready for Pickup',    '2026-08-12 16:45:00'),
(7, 5, 250000.00, 'Credit Card',   'Paid',    'Completed',           '2026-08-15 11:00:00');
