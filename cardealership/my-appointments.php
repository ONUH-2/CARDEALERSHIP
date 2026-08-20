<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/cars.php';
require_once __DIR__ . '/../form/inc/connect.php';

require_login();

$appointments = [];
if ($con && $con !== false) {
    $stmt = $con->prepare(
        'SELECT a.*, c.name AS car_name, c.price AS car_price, c.images AS car_images
         FROM appointments a
         LEFT JOIN cars c ON c.id = a.car_id
         WHERE a.user_id = ?
         ORDER BY a.appointment_date DESC, a.appointment_time DESC'
    );
    if ($stmt) {
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        $stmt->close();
    }
}

// Helper: status badge color
function appt_status_style(string $status): string {
    return match(strtolower($status)) {
        'pending'    => 'background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.35);',
        'approved'   => 'background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.35);',
        'confirmed'  => 'background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.35);',
        'rejected'   => 'background:rgba(220,38,38,.1);color:#ff8a8a;border:1px solid rgba(220,38,38,.35);',
        'cancelled'  => 'background:rgba(220,38,38,.1);color:#ff8a8a;border:1px solid rgba(220,38,38,.35);',
        'completed'  => 'background:rgba(25,201,255,.1);color:var(--accent-2);border:1px solid rgba(25,201,255,.35);',
        default      => 'background:var(--surface-strong);color:var(--text-muted);border:1px solid var(--border);',
    };
}

render_page_start('My Appointments');
render_navbar('My Appointments');
?>

<div class="page">
    <div class="card">
        <h2>My Appointments</h2>
        <p class="muted">View all your test drive and viewing appointments.</p>

        <?php if (empty($appointments)): ?>
            <div style="text-align:center;padding:60px 20px;color:var(--muted);border:1px dashed var(--border-strong);border-radius:20px;margin-top:20px;">
                <i class="fa-solid fa-calendar-xmark" style="font-size:2rem;color:var(--accent);margin-bottom:14px;display:block;"></i>
                <p>You haven't booked any appointments yet.</p>
                <a href="inventory.php" class="btn-primary" style="margin-top:16px;display:inline-flex;">Browse Inventory</a>
            </div>
        <?php else: ?>
            <div class="table-wrap" style="margin-top:20px;">
                <table>
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Booked On</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($appointments as $appt):
                        $images = [];
                        if (!empty($appt['car_images'])) {
                            $images = json_decode($appt['car_images'], true);
                            if (!is_array($images)) $images = [$appt['car_images']];
                        }
                        $image = !empty($images[0]) ? $images[0] : 'images/placeholder.svg';
                        $carName = $appt['car_name'] ?: 'Vehicle removed from inventory';
                    ?>
                        <tr>
                            <td>
                                <div style="display:flex;gap:12px;align-items:center;">
                                    <img src="<?php echo esc(car_image_url($image)); ?>" alt="" style="width:80px;height:60px;object-fit:cover;border-radius:10px;">
                                    <div>
                                        <strong><?php echo esc($carName); ?></strong>
                                        <?php if ($appt['car_price']): ?>
                                            <br><span class="muted"><?php echo esc($appt['car_price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo esc(date('M d, Y', strtotime($appt['appointment_date']))); ?></strong>
                                <br><span class="muted"><?php echo esc(date('h:i A', strtotime($appt['appointment_time']))); ?></span>
                            </td>
                            <td><span class="pill"><?php echo esc($appt['appointment_type']); ?></span></td>
                            <td>
                                <span class="pill" style="<?php echo appt_status_style($appt['status']); ?>">
                                    <?php echo esc($appt['status']); ?>
                                </span>
                            </td>
                            <td class="muted"><?php echo esc(date('M d, Y', strtotime($appt['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php render_page_end(); ?>
