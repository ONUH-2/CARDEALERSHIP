<?php
session_start();

require_once __DIR__ . '/../../form/inc/connect.php';

$message = '';
$signupErrors = [];
$activeForm = (($_GET['form'] ?? '') === 'signup') ? 'signup' : 'login';

if (!isset($con) || $con === false) {
    $message = 'Database unavailable. Please try again later.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($con) || $con === false) {
        $message = 'Database unavailable. Please check your MySQL setup and try again.';
    } else {
    $formType = $_POST['form_type'] ?? 'login';
    $activeForm = $formType === 'signup' ? 'signup' : 'login';

    if ($formType === 'signup') {
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if ($fullname === '') $signupErrors[] = 'Please enter your full name.';
        if ($username === '') $signupErrors[] = 'Please enter a username.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $signupErrors[] = 'Please enter a valid email address.';
        if (strlen($password) < 6) $signupErrors[] = 'Password must be at least 6 characters long.';
        if ($password !== $confirmPassword) $signupErrors[] = 'Passwords do not match.';

        if (!$signupErrors) {
            $check = $con->prepare('SELECT id FROM user_data WHERE email = ? OR username = ? LIMIT 1');
            if (!$check) {
                $signupErrors[] = 'Could not validate your account details. Please try again.';
            } else {
                $check->bind_param('ss', $email, $username);
                $check->execute();
                $existing = $check->get_result()->fetch_assoc();
                $check->close();

                if ($existing) {
                    $signupErrors[] = 'That email or username is already registered.';
                }
            }
        }

        if (!$signupErrors) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $con->prepare('INSERT INTO user_data (fullname, username, email, password) VALUES (?, ?, ?, ?)');
            if (!$stmt) {
                $signupErrors[] = 'Could not create your account. Please check your database setup.';
            } else {
                $stmt->bind_param('ssss', $fullname, $username, $email, $hashedPassword);
                if ($stmt->execute()) {
                    $message = 'Account created successfully. You can now log in.';
                    $activeForm = 'login';
                    $_POST = [];
                } else {
                    $signupErrors[] = 'Could not create your account. Please try again.';
                }
                $stmt->close();
            }
        }
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if ($email !== '' && $password !== '') {
            $stmt = $con->prepare('SELECT id, username, email, password FROM user_data WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $storedPassword = (string)$user['password'];

                $passwordIsValid = password_verify($password, $storedPassword);
                $isLegacyPlaintext = !$passwordIsValid && $storedPassword !== '' && hash_equals($storedPassword, $password);

                if ($passwordIsValid || $isLegacyPlaintext) {
                    if ($isLegacyPlaintext || password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $upgrade = $con->prepare('UPDATE user_data SET password = ? WHERE id = ?');
                        if ($upgrade) {
                            $upgrade->bind_param('si', $newHash, $user['id']);
                            $upgrade->execute();
                            $upgrade->close();
                        }
                    }
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    header('Location: ../../cardealership/index.php');
                    exit;
                }

                $message = 'Wrong password. Please try again.';
            } else {
                $message = 'No account found with that email.';
            }
            $stmt->close();
        } else {
            $message = 'Please fill in both fields.';
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Honus Autos | Login & Sign Up</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    :root {
      color-scheme: dark;
      --bg: #06070c;
      --panel: #12151f;
      --panel-soft: #171c29;
      --text: #f2f4fa;
      --muted: #8b93a8;
      --border: rgba(255,255,255,.09);
      --border-strong: rgba(255,255,255,.18);
      --accent: #ff5b2e;
      --accent-2: #19c9ff;
      --accent-grad: linear-gradient(135deg,#ff5b2e,#ff8a3d);
      --shadow: 0 30px 80px rgba(0,0,0,.6);
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      font-family: 'Inter', Arial, sans-serif;
      background: radial-gradient(circle at 15% 10%, #151a28 0%, var(--bg) 45%);
      color: var(--text);
    }
    h1, h2, h3 { font-family: 'Rajdhani', sans-serif; font-weight: 700; }
    .auth-shell {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px;
      position: relative;
      overflow: hidden;
    }
    .auth-shell::before {
      content: "";
      position: absolute; inset: 0;
      background-image:
        repeating-linear-gradient(115deg, rgba(255,91,46,.05) 0 2px, transparent 2px 90px),
        repeating-linear-gradient(65deg, rgba(25,201,255,.04) 0 2px, transparent 2px 120px);
      animation: driftLines 24s linear infinite;
      pointer-events: none;
    }
    @keyframes driftLines { from { background-position: 0 0, 0 0; } to { background-position: 420px 0, -420px 0; } }
    .auth-card {
      position: relative; z-index: 2;
      width: min(100%, 1000px);
      display: grid;
      grid-template-columns: 1.02fr 0.98fr;
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 28px;
      overflow: hidden;
      box-shadow: var(--shadow);
    }
    .brand-panel {
      padding: 46px;
      background:
        linear-gradient(160deg, rgba(6,7,12,.72) 0%, rgba(6,7,12,.92) 100%),
        url('../images/begin.jpg') center/cover;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 16px;
      color: var(--text);
    }
    .eyebrow {
      display: inline-flex; align-items: center; gap: 8px; width: fit-content;
      color: var(--accent-2); text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.78rem; font-weight: 700;
      padding: 7px 16px; border-radius: 999px; border: 1px solid var(--border-strong); background: rgba(255,91,46,.08);
    }
    .brand-panel h1 { font-size: clamp(1.9rem, 3vw, 2.6rem); line-height: 1.1; }
    .brand-panel p { color: var(--muted); line-height: 1.7; }
    .info-list { display: grid; gap: 10px; margin-top: 10px; }
    .info-list span { color: var(--text); font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .info-list span::before { content: "\2713"; color: var(--accent-2); font-weight: 800; }
    .form-panel { padding: 42px; display: flex; flex-direction: column; justify-content: center; }
    .tabs { display: flex; gap: 12px; margin-bottom: 24px; }
    .tab-btn { flex: 1; padding: 12px 14px; border: 1px solid var(--border-strong); border-radius: 999px; background: var(--panel-soft); color: var(--text); font-weight: 700; cursor: pointer; transition: all .2s ease; }
    .tab-btn.active { background: var(--accent-grad); color: #0a0a0d; border-color: transparent; box-shadow: 0 12px 28px rgba(255,91,46,.28); }
    .auth-form { display: none; gap: 14px; }
    .auth-form.active { display: flex; flex-direction: column; }
    label { font-weight: 600; color: var(--text); font-size: .92rem; }
    input { width: 100%; padding: 13px 16px; border: 1px solid var(--border-strong); border-radius: 14px; font: inherit; color: var(--text); background: var(--panel-soft); }
    input:focus { outline: none; border-color: var(--accent-2); }
    .submit-btn { margin-top: 10px; padding: 15px; border: none; border-radius: 999px; background: var(--accent-grad); color: #0a0a0d; font-weight: 800; cursor: pointer; font-size: 1rem; transition: all .22s ease; box-shadow: 0 12px 30px rgba(255,91,46,.28); }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 16px 38px rgba(255,91,46,.42); }
    .switch-text { margin-top: 10px; color: var(--muted); font-size: .92rem; }
    .switch-link { color: var(--accent-2); font-weight: 700; }
    .notice { border-radius: 12px; padding: 11px 13px; margin-bottom: 14px; }
    .notice-success { color: #4ade80; background: rgba(74,222,128,.1); border: 1px solid rgba(74,222,128,.35); }
    .notice-error { color: #ff8a8a; background: rgba(220,38,38,.1); border: 1px solid rgba(220,38,38,.35); }
    @media (max-width: 840px) { .auth-card { grid-template-columns: 1fr; } .brand-panel { padding-bottom: 24px; } .form-panel { padding-top: 16px; } }
  </style>

</head>
<body>
  <div class="auth-shell">
    <div class="auth-card">
      <div class="brand-panel">
        <p class="eyebrow">Luxury Performance</p>
        <h1>Welcome to<br>Honus Autos</h1>
        <p>Join the exclusive club for premium service, private offers, and priority access to the latest luxury vehicles.</p>

        <div class="info-list">
          <span>✓ Premium inventory access</span>
          <span>✓ Priority appointments</span>
          <span>✓ VIP offers and events</span>
        </div>
      </div>

      <div class="form-panel">
        <div class="tabs">
          <button type="button" class="tab-btn <?php echo $activeForm === 'login' ? 'active' : ''; ?>" data-form="login">Login</button>
          <button type="button" class="tab-btn <?php echo $activeForm === 'signup' ? 'active' : ''; ?>" data-form="signup">Sign up</button>
        </div>

        <?php if (!empty($message)) {
          $messageIsSuccess = $activeForm === 'login' && $message === 'Account created successfully. You can now log in.';
        ?>
          <div class="notice <?= $messageIsSuccess ? 'notice-success' : 'notice-error'; ?>" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
          </div>
        <?php } ?>

        <?php if (!empty($signupErrors)) { ?>
          <div style="color:#ff8a8a;background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.35);border-radius:12px;padding:11px 13px;margin-bottom:14px;">
            <?php foreach ($signupErrors as $signupError) { ?>
              <div><?php echo htmlspecialchars($signupError); ?></div>
            <?php } ?>
          </div>
        <?php } ?>

        <form class="auth-form <?php echo $activeForm === 'login' ? 'active' : ''; ?>" id="login-form" method="POST" action="">
          <input type="hidden" name="form_type" value="login">
          <label for="login-email">Email</label>
          <input type="email" id="login-email" name="email" placeholder="you@example.com" required>

          <label for="login-password">Password</label>
          <input type="password" id="login-password" name="password" placeholder="••••••••" required>

          <button type="submit" class="submit-btn" name="submit" value="1">Sign In</button>
          <p class="switch-text">New here? <a href="#" class="switch-link" data-form="signup">Create account</a></p>
        </form>

        <form class="auth-form <?php echo $activeForm === 'signup' ? 'active' : ''; ?>" id="signup-form" method="POST" action="">
          <input type="hidden" name="form_type" value="signup">
          <label for="signup-name">Full Name</label>
          <input type="text" id="signup-name" name="fullname" placeholder="John Doe" required>

          <label for="signup-username">Username</label>
          <input type="text" id="signup-username" name="username" placeholder="johndoe" required>

          <label for="signup-email">Email</label>
          <input type="email" id="signup-email" name="email" placeholder="you@example.com" required>

          <label for="signup-password">Password</label>
          <input type="password" id="signup-password" name="password" placeholder="Create a password" required>

          <label for="signup-confirm">Confirm Password</label>
          <input type="password" id="signup-confirm" name="confirm_password" placeholder="Repeat password" required>

          <button type="submit" class="submit-btn" name="submit" value="1">Create Account</button>
          <p class="switch-text">Already have an account? <a href="#" class="switch-link" data-form="login">Log in</a></p>
        </form>
      </div>
    </div>
  </div>

  <script>
    const tabs = document.querySelectorAll('.tab-btn');
    const forms = document.querySelectorAll('.auth-form');
    const switches = document.querySelectorAll('.switch-link');

    function showForm(target) {
      forms.forEach(form => form.classList.toggle('active', form.id === `${target}-form`));
      tabs.forEach(tab => tab.classList.toggle('active', tab.dataset.form === target));
    }

    tabs.forEach(tab => {
      tab.addEventListener('click', (event) => {
        event.preventDefault();
        showForm(tab.dataset.form);
      });
    });

    switches.forEach(link => {
      link.addEventListener('click', (event) => {
        event.preventDefault();
        showForm(link.dataset.form);
      });
    });
  </script>
</body>
</html>
