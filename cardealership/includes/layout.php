<?php
require_once __DIR__ . '/auth.php';

function get_nav_appt_count(): int {
    global $con;
    if (!is_logged_in()) return 0;
    if (!isset($con)) {
        @require_once __DIR__ . '/../../form/inc/connect.php';
    }
    if (!isset($con) || $con === false) return 0;
    $stmt = $con->prepare('SELECT COUNT(*) AS total FROM appointments WHERE user_id = ?');
    if (!$stmt) return 0;
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['total'] ?? 0);
}

function render_navbar(string $active = ''): void {
    $isLoggedIn = is_logged_in();
    $isAdmin = is_admin_user();
    $apptCount = get_nav_appt_count();
    echo '<nav class="navbar">';
    echo '<a href="/cardealership/index.php" class="logo"><i class="fa-solid fa-bolt"></i> AKAZA\'S MOTORS</a>';
    echo '<ul class="nav-links">';
    $items = [
        ['href' => '/cardealership/index.php', 'label' => 'Home'],
        ['href' => '/cardealership/inventory.php', 'label' => 'Inventory'],
    ];
    if ($isLoggedIn) {
        $items[] = ['href' => '/cardealership/my-appointments.php', 'label' => 'My Appointments'];
    }
    foreach ($items as $item) {
        $class = $active === $item['label'] ? 'active' : '';
        $suffix = ($item['label'] === 'My Appointments' && $apptCount > 0) ? ' <span style="display:inline-flex;min-width:18px;height:18px;padding:0 5px;align-items:center;justify-content:center;border-radius:999px;background:var(--accent-grad);color:#0a0a0d;font-size:.7rem;margin-left:4px;vertical-align:middle;font-weight:800;">' . $apptCount . '</span>' : '';
        echo '<li><a class="' . esc($class) . '" href="' . esc($item['href']) . '">' . esc($item['label']) . $suffix . '</a></li>';
    }
    if ($isAdmin) {
        echo '<li><a class="' . esc($active === 'Admin' ? 'active' : '') . '" href="/cardealership/admin/dashboard.php">Admin</a></li>';
    }
    echo '</ul>';
    if ($isLoggedIn) {
        echo '<div class="user-area"><span class="user-name"><i class="fa fa-user-circle"></i> ' . esc($_SESSION['username']) . '</span><a class="user-logout" href="/cardealership/login/logout.php"><i class="fa fa-sign-out-alt"></i> Log out</a></div>';
    } else {
        echo '<a class="nav-cta" href="/cardealership/login/login.php?form=signup"><i class="fa fa-user-plus"></i> Sign Up</a>';
    }
    echo '</nav>';
}

function render_page_start(string $title): void {
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . esc($title) . '</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />';
    echo '<style>';
    echo ':root{color-scheme:dark;--bg:#06070c;--bg-soft:#0b0e17;--surface:#12151f;--surface-alt:#171c29;--surface-strong:#1d2333;--text:#f2f4fa;--muted:#8b93a8;--border:rgba(255,255,255,.09);--border-strong:rgba(255,255,255,.18);--accent:#ff5b2e;--accent-dark:#e2461b;--accent-2:#19c9ff;--accent-grad:linear-gradient(135deg,#ff5b2e,#ff8a3d);--shadow:0 25px 60px rgba(0,0,0,.55);--glow:0 0 0 1px rgba(255,91,46,.3),0 20px 45px rgba(255,91,46,.16)}';
    echo '*{box-sizing:border-box;margin:0;padding:0}';
    echo 'body{font-family:"Inter",Arial,sans-serif;background:radial-gradient(circle at 15% 0%,#151a28 0%,var(--bg) 45%);color:var(--text);line-height:1.6;min-height:100vh}';
    echo 'a{color:inherit;text-decoration:none}img{display:block;max-width:100%}';
    echo 'h1,h2,h3,h4{font-family:"Rajdhani",Arial,sans-serif;font-weight:700;letter-spacing:.01em}';
    echo '::-webkit-scrollbar{width:10px}::-webkit-scrollbar-track{background:var(--bg-soft)}::-webkit-scrollbar-thumb{background:var(--accent);border-radius:10px}';
    echo '.navbar{position:sticky;top:0;z-index:20;display:flex;justify-content:space-between;align-items:center;padding:18px 8%;background:rgba(8,9,15,.82);border-bottom:1px solid var(--border);backdrop-filter:blur(18px)}';
    echo '.logo{display:inline-flex;align-items:center;gap:8px;font-family:"Rajdhani",sans-serif;font-size:1.3rem;font-weight:700;letter-spacing:.22em;background:var(--accent-grad);-webkit-background-clip:text;background-clip:text;color:transparent}';
    echo '.nav-links{display:flex;gap:22px;list-style:none;flex-wrap:wrap}';
    echo '.nav-links a{color:var(--muted);font-weight:600;position:relative;padding-bottom:4px;transition:color .2s ease}';
    echo '.nav-links a::after{content:"";position:absolute;left:0;bottom:0;width:0;height:2px;background:var(--accent-grad);transition:width .25s ease}';
    echo '.nav-links a.active,.nav-links a:hover,.user-logout:hover{color:var(--text)}';
    echo '.nav-links a.active::after,.nav-links a:hover::after{width:100%}';
    echo '.user-area{display:flex;align-items:center;gap:12px;flex-wrap:wrap}';
    echo '.user-name{font-weight:600;color:var(--text)}';
    echo '.nav-cta,.user-logout,.btn,.btn-primary,.btn-secondary,.btn-danger{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 20px;border-radius:999px;font-weight:700;transition:all .22s ease;border:1px solid transparent;cursor:pointer;font-family:inherit;font-size:.95rem}';
    echo '.nav-cta,.btn-primary{background:var(--accent-grad);color:#0a0a0d;box-shadow:0 12px 30px rgba(255,91,46,.28)}';
    echo '.nav-cta:hover,.btn-primary:hover{transform:translateY(-2px);box-shadow:0 16px 38px rgba(255,91,46,.42)}';
    echo '.btn-secondary{background:var(--surface-alt);border:1px solid var(--border-strong);color:var(--text)}';
    echo '.btn-secondary:hover{border-color:var(--accent-2);color:var(--accent-2)}';
    echo '.btn-danger{background:rgba(220,38,38,.14);color:#ff8a8a;border:1px solid rgba(220,38,38,.4)}';
    echo '.btn-danger:hover{background:rgba(220,38,38,.28)}';
    echo '.page{padding:44px 8% 90px;max-width:1400px;margin:0 auto}';
    echo '.card{background:linear-gradient(180deg,var(--surface) 0%,var(--surface-alt) 100%);border:1px solid var(--border);border-radius:22px;box-shadow:var(--shadow);padding:26px}';
    echo '.grid{display:grid;gap:20px}.grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}.grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}';
    echo '.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}';
    echo '.stat{background:linear-gradient(180deg,var(--surface) 0%,var(--surface-alt) 100%);border:1px solid var(--border);border-radius:20px;padding:24px;transition:transform .25s ease,box-shadow .25s ease}';
    echo '.stat:hover{transform:translateY(-4px);box-shadow:var(--glow)}';
    echo '.stat h3{font-size:1.8rem;background:var(--accent-grad);-webkit-background-clip:text;background-clip:text;color:transparent}';
    echo 'table{width:100%;border-collapse:collapse;background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden}';
    echo 'th,td{padding:13px 14px;border-bottom:1px solid var(--border);text-align:left}th{background:var(--surface-alt);color:var(--muted);text-transform:uppercase;font-size:.78rem;letter-spacing:.08em}';
    echo 'tr:hover td{background:rgba(255,91,46,.04)}';
    echo '.thumb{width:64px;height:44px;object-fit:cover;border-radius:8px;border:1px solid var(--border)}';
    echo 'input,select,textarea{width:100%;padding:12px 14px;border:1px solid var(--border-strong);border-radius:14px;font:inherit;background:var(--surface-alt);color:var(--text)}';
    echo 'input:focus,select:focus,textarea:focus{outline:none;border-color:var(--accent-2)}';
    echo 'form{display:grid;gap:14px}';
    echo '.actions{display:flex;gap:10px;flex-wrap:wrap}';
    echo '.muted{color:var(--muted)}';
    echo '.success{color:#4ade80;font-weight:600}';
    echo '.error{color:#ff8a8a;font-weight:600}';
    echo '.pill{display:inline-block;padding:7px 12px;border-radius:999px;background:var(--surface-strong);border:1px solid var(--border);font-size:.85rem}';
    echo '.table-wrap{overflow-x:auto}';
    echo '@media (max-width:900px){.stats,.grid-2,.grid-3{grid-template-columns:1fr}.navbar{flex-direction:column;gap:12px}}';
    echo '</style>';
    echo '</head><body>';
}

function render_page_end(): void {
    echo '</body></html>';
}
