<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/cars.php';
require_once __DIR__ . '/../form/inc/connect.php';

require_login();

$orders = [];
if ($con && $con !== false) {
    $stmt = $con->prepare('SELECT o.*, c.name AS car_name, c.price AS car_price, c.images AS car_images FROM orders o LEFT JOIN cars c ON c.id = o.car_id WHERE o.user_id = ? ORDER BY o.created_at DESC');
    if ($stmt) {
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) $orders[] = $row;
        $stmt->close();
    }
}

render_page_start('My Orders');
render_navbar('My Orders');
echo '<div class="page">';
echo '<div class="card">';
echo '<h2>My Orders</h2>';
if (empty($orders)) {
    echo '<p class="muted">You have not placed any orders yet.</p>';
} else {
    echo '<div class="table-wrap"><table><thead><tr><th>Car</th><th>Amount</th><th>Payment</th><th>Payment Status</th><th>Order Status</th><th>Order Date</th></tr></thead><tbody>';
    foreach ($orders as $order) {
        $images = [];
        if (!empty($order['car_images'])) {
            $images = json_decode($order['car_images'], true);
            if (!is_array($images)) $images = [$order['car_images']];
        }
        $image = !empty($images[0]) ? $images[0] : 'images/placeholder.svg';
        $carName = $order['car_name'] ?: 'Vehicle removed from inventory';
        $carPrice = $order['car_price'] ?: $order['amount'];
        echo '<tr>';
        echo '<td><div style="display:flex;gap:12px;align-items:center;"><img src="' . esc(car_image_url($image)) . '" alt="' . esc($carName) . '" style="width:80px;height:60px;object-fit:cover;border-radius:10px;"> <strong>' . esc($carName) . '</strong></div></td>';
        echo '<td>' . esc($carPrice) . '</td>';
        echo '<td>' . esc($order['payment_method']) . '</td>';
        echo '<td><span class="pill">' . esc($order['payment_status']) . '</span></td>';
        echo '<td><span class="pill">' . esc($order['order_status']) . '</span></td>';
        echo '<td>' . esc($order['created_at']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
echo '</div></div>';
render_page_end();
