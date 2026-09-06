<?php
require __DIR__ . '/../config/database.php';

function fail(string $message, int $vehicleId): never {
    header('Location: ../book.php?vehicle_id=' . $vehicleId . '&error=' . urlencode($message));
    exit;
}

$vehicleId = filter_input(INPUT_POST, 'vehicle_id', FILTER_VALIDATE_INT);
$type = trim($_POST['appointment_type'] ?? '');
$name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$date = trim($_POST['appointment_date'] ?? '');
$time = trim($_POST['appointment_time'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$vehicleId || !in_array($type, ['Vehicle viewing', 'Test drive'], true) || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $date === '' || $time === '') {
    fail('Please complete every required field with valid information.', (int)$vehicleId);
}
if ($date < date('Y-m-d')) fail('Please choose today or a future date.', $vehicleId);

$vehicleCheck = $conn->prepare('SELECT id FROM vehicles WHERE id = ? AND is_available = 1');
$vehicleCheck->bind_param('i', $vehicleId);
$vehicleCheck->execute();
if (!$vehicleCheck->get_result()->fetch_assoc()) fail('That vehicle is no longer available.', $vehicleId);
$vehicleCheck->close();

$stmt = $conn->prepare('INSERT INTO appointments (vehicle_id, appointment_type, full_name, email, phone, appointment_date, appointment_time, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('isssssss', $vehicleId, $type, $name, $email, $phone, $date, $time, $message);
$success = $stmt->execute();
$stmt->close();
if (!$success) fail('We could not save your request. Please try again.', $vehicleId);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Appointment requested | AutoMeet</title><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="../assets/style.css"></head><body><main class="section"><div class="container" style="max-width:650px;text-align:center"><div style="width:72px;height:72px;border-radius:50%;display:grid;place-items:center;background:#e7faf0;color:#15935d;font-size:2rem;margin:0 auto 25px">✓</div><p class="eyebrow">Request received</p><h1 style="font-size:clamp(2.4rem,6vw,4.5rem)">We will see you soon.</h1><p class="hero-text" style="margin:0 auto 30px">Thanks, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>. Your <?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?> request has been received. Our team will contact you to confirm the exact appointment.</p><a class="button" href="../index.php">Return to vehicles <span>→</span></a></div></main></body></html>
