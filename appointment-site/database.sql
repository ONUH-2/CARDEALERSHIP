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
('Range Rover Evoque', 2024, 'SUV', 'Available to view', 'https://images.unsplash.com/photo-1605559424843-9e6c1f5d3e1b?auto=format&fit=crop&w=1200&q=85', 'A compact luxury SUV with a confident stance, premium finish, and city-friendly dimensions.');
