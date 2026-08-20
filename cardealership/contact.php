<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cars.php';

$carId = (int)($_POST['car_id'] ?? 0);
$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$car = $carId > 0 ? get_car_by_id($carId, true) : null;

if ($car && $name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $phone !== '') {
    header('Location: /cardealership/car-details.php?id=' . $carId . '&inquiry=sent#inquiry-form');
    exit;
}

if ($car) {
    header('Location: /cardealership/car-details.php?id=' . $carId . '#inquiry-form');
    exit;
}
header('Location: /cardealership/inventory.php');
exit;
