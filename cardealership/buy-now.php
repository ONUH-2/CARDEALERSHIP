<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/cars.php';
require_once __DIR__ . '/../form/inc/connect.php';

$carId = 0;
foreach (['car_id', 'id'] as $key) {
    if (isset($_GET[$key]) && $_GET[$key] !== '') { $carId = (int)$_GET[$key]; break; }
}
if ($carId === 0) {
    foreach (['car_id', 'id'] as $key) {
        if (isset($_POST[$key]) && $_POST[$key] !== '') { $carId = (int)$_POST[$key]; break; }
    }
}

$car = get_car_by_id($carId);
if (!$car) {
    header('Location: /cardealership/inventory.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $amount = (float) str_replace([',', '$'], '', $car['price']);

    if ($name === '') $errors[] = 'Name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($phone === '') $errors[] = 'Phone is required.';
    if ($address === '') $errors[] = 'Address is required.';
    if ($city === '') $errors[] = 'City is required.';
    if ($country === '') $errors[] = 'Country is required.';
    if (!in_array($paymentMethod, ['Credit Card', 'Bank Transfer', 'Cash'], true)) $errors[] = 'Choose a valid payment method.';

    if (empty($errors)) {
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
        if ($con && $con !== false) {
            $stmt = $con->prepare('INSERT INTO orders (user_id, car_id, amount, payment_method, payment_status, order_status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $paymentStatus = 'Pending';
            $orderStatus = 'Processing';
            $stmt->bind_param('iissss', $userId, $car['id'], $amount, $paymentMethod, $paymentStatus, $orderStatus);
            if ($stmt->execute()) {
                header('Location: /cardealership/payment-confirmation.php?order_id=' . (int)$con->insert_id);
                exit;
            } else {
                $errors[] = 'Could not place order. Please try again.';
            }
            $stmt->close();
        } else {
            $errors[] = 'The order system is currently unavailable. Please try again.';
        }
    }
}

render_page_start('Buy Now');
render_navbar('Inventory');
echo '<div class="page">';
if (!empty($_SESSION['flash_success'])) {
    echo '<p class="success">' . esc($_SESSION['flash_success']) . '</p>';
    unset($_SESSION['flash_success']);
}
if ($errors) {
    echo '<div class="card"><p class="error">' . esc(implode('<br>', $errors)) . '</p></div>';
}
echo '<div class="grid" style="grid-template-columns:1.1fr .9fr;gap:24px;">';
echo '<div class="card">';
if (!is_logged_in()) {
    echo '<p class="muted" style="margin-bottom:16px;">You are checking out as a guest. Sign in to see your orders later.</p>';
}
echo '<h2>Order Summary</h2>';
echo '<p><strong>Car:</strong> ' . esc($car['name']) . '</p>';
echo '<p><strong>Price:</strong> ' . esc($car['price']) . '</p>';
echo '<p><strong>Order Date:</strong> ' . esc(date('Y-m-d')) . '</p>';
echo '<p><strong>Estimated Delivery:</strong> 5–7 business days</p>';
if (!empty($car['images'])) {

    if (is_array($car['images'])) {
        $images = $car['images'];
    } else {
        $images = json_decode($car['images'], true);

        if (!is_array($images)) {
            $images = [$car['images']];
        }
    }

    $image = !empty($images[0]) ? $images[0] : 'images/placeholder.svg';

    echo '<img src="' . esc(car_image_url($image)) . '" alt="' . esc($car['name']) . '" style="margin-top:16px;border-radius:18px;height:260px;object-fit:cover;width:100%;">';
}
echo '</div>';
echo '<div class="card">';
echo '<h2>Purchase Details</h2>';
echo '<form method="post">';
echo '<input type="hidden" name="car_id" value="' . (int)$car['id'] . '">';
echo '<input type="text" name="name" value="' . esc($_SESSION['username'] ?? '') . '" placeholder="Your name" required>';
echo '<input type="email" name="email" value="' . esc($_SESSION['email'] ?? '') . '" placeholder="Email" required>';
echo '<input type="text" name="phone" placeholder="Phone Number" required>';
echo '<input type="text" name="address" placeholder="Address" required>';
echo '<input type="text" name="city" placeholder="City" required>';
echo '<input type="text" name="country" placeholder="Country" required>';
echo '<select name="payment_method" required><option value="">Select Payment Method</option><option value="Credit Card">Credit Card</option><option value="Bank Transfer">Bank Transfer</option><option value="Cash">Cash</option></select>';
echo '<button class="btn-primary" type="submit">Confirm Purchase</button>';
echo '</form>';
echo '</div>';
echo '</div>';
echo '</div>';
render_page_end();
