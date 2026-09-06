<?php
// Update these values for your local MySQL/MariaDB installation.
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'cardealership_appointments';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Database connection failed. Check config/database.php.');
}
$conn->set_charset('utf8mb4');
?>

---FILE: /home/ubuntu/CARDEALERSHIP/appointment-site/includes/helpers.php---
<?php
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function old(string $key): string {
    return e($_POST[$key] ?? '');
}

function vehicle_image(?string $image): string {
    return $image ?: 'https://images.unsplash.com/photo-1553440569-bcc63803a83d?auto=format&fit=crop&w=1200&q=85';
}
?>

---FILE: /home/ubuntu/CARDEALERSHIP/appointment-site/database.sql---
CREATE DATABASE IF NOT EXISTS cardealership_appointments;
USE cardealership_appointments;

CREATE TABLE IF NOT EXISTS vehicles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    year YEAR NOT NULL,
    category VARCHAR(40) NOT NULL,
    price_label VARCHAR(40) NOT NULL,
    image_url TEXT NOT NULL,
    description TEXT NOT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT UNSIGNED NOT NULL,
    appointment_type ENUM('Vehicle viewing', 'Test drive') NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    message TEXT NULL,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

INSERT INTO vehicles (name, year, category, price_label, image_url, description) VALUES
('BMW 3 Series', 2024, 'Sedan', 'Available to view', 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85', 'A refined daily sedan with confident handling, premium comfort, and smart technology.'),
('Mercedes-Benz GLC', 2024, 'SUV', 'Available to view', 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85', 'A practical luxury SUV with a calm cabin, flexible space, and a smooth driving feel.'),
('Audi A5 Coupe', 2023, 'Coupe', 'Available to view', 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85', 'A sleek coupe that blends everyday usability with a more focused, engaging drive.'),
('Range Rover Evoque', 2024, 'SUV', 'Available to view', 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85', 'A compact luxury SUV with a confident stance, premium finish, and city-friendly dimensions.');

---FILE: /home/ubuntu/CARDEALERSHIP/appointment-site/README.md---
# AutoMeet — Appointment-Only Car Website

This is a beginner-friendly PHP/MySQLi car website. It does not sell cars. Visitors can browse vehicles and request either a vehicle viewing or a test drive.

## Local setup

1. Create a MySQL database by importing `database.sql` in phpMyAdmin or by running `mysql -u root -p < database.sql`.
2. Update the credentials in `config/database.php`.
3. Start the PHP server from this directory with `php -S localhost:8000`.
4. Open `http://localhost:8000` in your browser.

The appointment endpoint uses a prepared statement and server-side validation. For production, add authentication for staff, email notifications, CSRF protection, rate limiting, and a staff dashboard.

## Main files

| File | Purpose |
|---|---|
| `index.php` | Homepage and vehicle cards |
| `book.php` | Appointment booking form |
| `api/create-appointment.php` | Validates and saves bookings |
| `config/database.php` | MySQLi connection |
| `database.sql` | Tables and sample vehicles |
| `assets/style.css` | White, blue, and green responsive UI |
| `assets/app.js` | Mobile menu and form feedback |
---

Автор: Manus AI
