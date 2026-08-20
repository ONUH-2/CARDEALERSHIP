<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../form/inc/connect.php';

require_admin();

if (!isset($con) || $con === false) {
    header('Location: /cardealership/admin/dashboard.php');
    exit;
}

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$action = $_GET['action'] ?? '';

if ($orderId > 0) {
    $updates = [];
    if ($action === 'mark_paid') {
        $updates['payment_status'] = 'Paid';
    } elseif ($action === 'mark_pending') {
        $updates['payment_status'] = 'Pending';
    } elseif ($action === 'processing') {
        $updates['order_status'] = 'Processing';
    } elseif ($action === 'preparing') {
        $updates['order_status'] = 'Preparing Vehicle';
    } elseif ($action === 'ready') {
        $updates['order_status'] = 'Ready for Pickup';
    } elseif ($action === 'completed') {
        $updates['order_status'] = 'Completed';
    } elseif ($action === 'cancelled') {
        $updates['order_status'] = 'Cancelled';
    }

    if (!empty($updates)) {
        $fields = [];
        $values = [];
        $types = '';
        foreach ($updates as $field => $value) {
            $fields[] = $field . ' = ?';
            $values[] = $value;
            $types .= 's';
        }
        $values[] = $orderId;
        $types .= 'i';
        $stmt = $con->prepare('UPDATE orders SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $stmt->close();
    }
}

header('Location: /cardealership/admin/dashboard.php');
exit;
