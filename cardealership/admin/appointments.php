<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/cars.php';
require_once __DIR__ . '/../../form/inc/connect.php';
require_admin();

// ─── Handle action (approve / reject / completed / cancelled) ───
if (isset($_GET['action']) && isset($_GET['appt_id'])) {
    $apptId = (int)$_GET['appt_id'];
    $action = $_GET['action'];
    $allowed = ['approved', 'rejected', 'completed', 'cancelled'];

    if ($apptId > 0 && in_array($action, $allowed, true) && $con && $con !== false) {
        $stmt = $con->prepare('UPDATE appointments SET status = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('si', $action, $apptId);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: /cardealership/admin/appointments.php');
    exit;
}

// ─── Fetch all appointments ─────────────────────────────────
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$appointments = [];
if ($con && $con !== false) {
    $where = [];
    $params = [];
    $types = '';

    if ($search !== '') {
        $where[] = '(a.customer_name LIKE ? OR a.customer_email LIKE ? OR c.name LIKE ?)';
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $types .= 'sss';
    }
    if ($statusFilter !== '') {
        $where[] = 'a.status = ?';
        $params[] = $statusFilter;
        $types .= 's';
    }

    $sql = 'SELECT a.*, c.name AS car_name, c.price AS car_price
            FROM appointments a
            LEFT JOIN cars c ON c.id = a.car_id';
    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY a.appointment_date ASC, a.appointment_time ASC';

    $stmt = $con->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        $stmt->close();
    }
}

// Status badge helper
function appt_badge(string $status): string {
    $styles = match(strtolower($status)) {
        'pending'   => 'background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.35);',
        'approved'  => 'background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.35);',
        'confirmed' => 'background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.35);',
        'rejected'  => 'background:rgba(220,38,38,.1);color:#ff8a8a;border:1px solid rgba(220,38,38,.35);',
        'cancelled' => 'background:rgba(220,38,38,.1);color:#ff8a8a;border:1px solid rgba(220,38,38,.35);',
        'completed' => 'background:rgba(25,201,255,.1);color:var(--accent-2);border:1px solid rgba(25,201,255,.35);',
        default     => 'background:var(--surface-strong);color:var(--muted);border:1px solid var(--border);',
    };
    return '<span class="pill" style="' . $styles . '">' . htmlspecialchars($status) . '</span>';
}

render_page_start('Manage Appointments | Admin');
render_navbar('Admin');

echo '<div class="page">';

// Header
echo '<div class="card">';
echo '<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">';
echo '<div><h2>Manage Appointments</h2><p class="muted">View and manage all customer appointment requests.</p></div>';
echo '<div class="actions">';
echo '<a class="btn-secondary" href="dashboard.php">← Dashboard</a>';
echo '</div></div>';

// Filter form
echo '<form method="get" style="display:grid;grid-template-columns:2fr 1fr auto;gap:12px;align-items:end;margin-top:20px;">';
echo '<div><label>Search</label><input type="text" name="search" value="' . esc($search) . '" placeholder="Search by name, email, or car..."></div>';
echo '<div><label>Status</label><select name="status"><option value="">All</option>';
foreach (['Pending', 'Approved', 'Rejected', 'Completed', 'Cancelled'] as $s) {
    echo '<option value="' . esc($s) . '"' . ($statusFilter === $s ? ' selected' : '') . '>' . esc($s) . '</option>';
}
echo '</select></div>';
echo '<button class="btn-primary" type="submit">Filter</button>';
echo '</form>';

// Table
echo '<div class="table-wrap" style="margin-top:20px;"><table><thead><tr>';
echo '<th>Customer</th><th>Vehicle</th><th>Date & Time</th><th>Type</th><th>Status</th><th>Message</th><th>Actions</th>';
echo '</tr></thead><tbody>';

if (empty($appointments)) {
    echo '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted);">No appointments found.</td></tr>';
} else {
    foreach ($appointments as $appt) {
        $carName = $appt['car_name'] ?: 'Deleted vehicle';
        $isPending = strtolower($appt['status']) === 'pending';

        echo '<tr>';
        // Customer
        echo '<td><strong>' . esc($appt['customer_name']) . '</strong><br><span class="muted">' . esc($appt['customer_email']) . '</span><br><span class="muted">' . esc($appt['customer_phone']) . '</span></td>';
        // Car
        echo '<td><strong>' . esc($carName) . '</strong>';
        if ($appt['car_price']) echo '<br><span class="muted">' . esc($appt['car_price']) . '</span>';
        echo '</td>';
        // Date & Time
        echo '<td><strong>' . esc(date('M d, Y', strtotime($appt['appointment_date']))) . '</strong><br><span class="muted">' . esc(date('h:i A', strtotime($appt['appointment_time']))) . '</span></td>';
        // Type
        echo '<td><span class="pill">' . esc($appt['appointment_type']) . '</span></td>';
        // Status
        echo '<td>' . appt_badge($appt['status']) . '</td>';
        // Message
        echo '<td>' . (trim($appt['message'] ?? '') !== '' ? '<span class="muted" style="max-width:180px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' . esc($appt['message']) . '</span>' : '<span class="muted">—</span>') . '</td>';
        // Actions
        echo '<td><div class="actions">';
        if ($isPending) {
            echo '<a class="btn-primary" href="?action=approved&appt_id=' . (int)$appt['id'] . '">Approve</a>';
            echo '<a class="btn-danger" href="?action=rejected&appt_id=' . (int)$appt['id'] . '" onclick="return confirm(\'Reject this appointment?\')">Reject</a>';
        } elseif (strtolower($appt['status']) === 'approved') {
            echo '<a class="btn-secondary" href="?action=completed&appt_id=' . (int)$appt['id'] . '">Mark Completed</a>';
            echo '<a class="btn-danger" href="?action=cancelled&appt_id=' . (int)$appt['id'] . '" onclick="return confirm(\'Cancel this appointment?\')">Cancel</a>';
        } else {
            echo '<span class="muted">No actions</span>';
        }
        echo '</div></td>';
        echo '</tr>';
    }
}

echo '</tbody></table></div>';
echo '</div></div>';

render_page_end();
