# AutoMeet — Appointment-Only Car Website

This is a beginner-friendly PHP/MySQLi car website. It does not sell cars. Visitors can browse vehicles and request either a vehicle viewing or a test drive.

## Local setup

1. Create the MySQL database by importing `database.sql` in phpMyAdmin, or run `mysql -u root -p < database.sql`.
2. Update the credentials in `config/database.php`.
3. Start the PHP server from this directory with `php -S localhost:8000`.
4. Open `http://localhost:8000` in your browser.

The booking endpoint uses a prepared statement and server-side validation. Before production, add authentication for staff, email notifications, CSRF protection, rate limiting, and a staff dashboard.

## Main files

| File | Purpose |
|---|---|
| `index.php` | Homepage and vehicle cards |
| `book.php` | Appointment booking form |
| `api/create-appointment.php` | Validates and saves bookings |
| `config/database.php` | MySQLi connection |
| `database.sql` | Tables and sample vehicles |
| `assets/style.css` | Responsive white, blue, and green UI |
| `assets/app.js` | Mobile menu and form feedback |

Author: Manus AI
