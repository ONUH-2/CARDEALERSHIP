<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../../form/inc/connect.php';

require_admin();

// ─── Fetch stats ───────────────────────────────────────────
$stats = [];
$recentAppointments = [];

if ($con && $con !== false) {
    $statsQuery = $con->query(
        'SELECT
            (SELECT COUNT(*) FROM user_data) AS users,
            (SELECT COUNT(*) FROM cars) AS cars,
            (SELECT COUNT(*) FROM appointments) AS total_appointments,
            (SELECT COUNT(*) FROM appointments WHERE status = "Pending") AS pending,
            (SELECT COUNT(*) FROM appointments WHERE status = "Approved") AS approved,
            (SELECT COUNT(*) FROM appointments WHERE status = "Completed") AS completed'
    );
    if ($statsQuery) {
        $stats = $statsQuery->fetch_assoc();
    }

    // Recent 10 appointments
    $result = $con->query(
        'SELECT a.*, c.name AS car_name
         FROM appointments a
         LEFT JOIN cars c ON c.id = a.car_id
         ORDER BY a.created_at DESC
         LIMIT 10'
    );
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentAppointments[] = $row;
        }
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

render_page_start('Admin Dashboard');
render_navbar('Admin');

echo '<div class="page">';

// ── Header ──
echo '<div class="card">';
echo '<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">';
echo '<div><h2>Admin Dashboard</h2><p class="muted">Manage appointments, inventory, and customers from one place.</p></div>';
echo '<div class="actions">';
echo '<a class="btn-primary" href="add-car.php">+ Add Car</a>';
echo '<a class="btn-secondary" href="cars.php">Manage Cars</a>';
echo '<a class="btn-secondary" href="appointments.php">All Appointments</a>';
echo '<a class="btn-secondary" href="users.php">Users</a>';
echo '</div></div></div>';

// ── Stats Cards ──
echo '<div class="stats" style="margin-top:20px;">';
$cards = [
    ['label' => 'Total Users',        'value' => $stats['users'] ?? 0],
    ['label' => 'Total Cars',         'value' => $stats['cars'] ?? 0],
    ['label' => 'Total Appointments', 'value' => $stats['total_appointments'] ?? 0],
    ['label' => 'Pending',            'value' => $stats['pending'] ?? 0],
    ['label' => 'Approved',           'value' => $stats['approved'] ?? 0],
    ['label' => 'Completed',          'value' => $stats['completed'] ?? 0],
];
foreach ($cards as $card) {
    echo '<div class="stat"><p class="muted">' . esc($card['label']) . '</p><h3>' . esc($card['value']) . '</h3></div>';
}
echo '</div>';

// ── Recent Appointments Table ──
echo '<div class="card" style="margin-top:24px;">';
echo '<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:16px;">';
echo '<h3>Recent Appointments</h3>';
echo '<a class="btn-secondary" href="appointments.php">View All →</a>';
echo '</div>';

echo '<div class="table-wrap"><table><thead><tr>';
echo '<th>Customer</th><th>Vehicle</th><th>Date & Time</th><th>Type</th><th>Status</th><th>Actions</th>';
echo '</tr></thead><tbody>';

if (empty($recentAppointments)) {
    echo '<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted);">No appointments yet.</td></tr>';
} else {
    foreach ($recentAppointments as $appt) {
        $isPending = strtolower($appt['status']) === 'pending';
        $isApproved = strtolower($appt['status']) === 'approved';
        $carName = $appt['car_name'] ?: 'Deleted vehicle';

        echo '<tr>';
        echo '<td><strong>' . esc($appt['customer_name']) . '</strong><br><span class="muted">' . esc($appt['customer_email']) . '</span></td>';
        echo '<td>' . esc($carName) . '</td>';
        echo '<td><strong>' . esc(date('M d, Y', strtotime($appt['appointment_date']))) . '</strong><br><span class="muted">' . esc(date('h:i A', strtotime($appt['appointment_time']))) . '</span></td>';
        echo '<td><span class="pill">' . esc($appt['appointment_type']) . '</span></td>';
        echo '<td>' . appt_badge($appt['status']) . '</td>';
        echo '<td><div class="actions">';
        if ($isPending) {
            echo '<a class="btn-primary" href="appointments.php?action=approved&appt_id=' . (int)$appt['id'] . '">Approve</a>';
            echo '<a class="btn-danger" href="appointments.php?action=rejected&appt_id=' . (int)$appt['id'] . '" onclick="return confirm(\'Reject this appointment?\')">Reject</a>';
        } elseif ($isApproved) {
            echo '<a class="btn-secondary" href="appointments.php?action=completed&appt_id=' . (int)$appt['id'] . '">Complete</a>';
        } else {
            echo '<span class="muted">—</span>';
        }
        echo '</div></td>';
        echo '</tr>';
    }
}

echo '</tbody></table></div></div>';

echo '</div>'; // end page

render_page_end();
