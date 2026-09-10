<?php
/**
 * Akaza's Motors shared MySQL connection.
 *
 * Keeps compatibility with the original project paths:
 *   /form/inc/connect.php
 *
 * Defaults are suitable for a standard XAMPP MySQL installation (root, no
 * password). You may override them with environment variables DB_HOST,
 * DB_USER, DB_PASS and DB_NAME.
 */

if (!class_exists('mysqli')) {
    $con = false;
    return;
}
mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS');
if ($pass === false) $pass = '';
$requestedDb = getenv('DB_NAME');
if ($requestedDb === false) $requestedDb = '';

$con = @new mysqli($host, $user, $pass);
if ($con->connect_errno) {
    $con = false;
    return;
}
$con->set_charset('utf8mb4');

// Prefer the project database name, then common alternatives. If none of
// those exist, find a database that contains the three tables this project
// actually needs.
$candidates = [];
if ($requestedDb !== '') $candidates[] = $requestedDb;
foreach (['cardealership', 'akaza_motors', 'akazas_motors'] as $candidate) {
    if (!in_array($candidate, $candidates, true)) $candidates[] = $candidate;
}

$chosen = null;
foreach ($candidates as $candidate) {
    $escaped = $con->real_escape_string($candidate);
    $result = @$con->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='" . $escaped . "' AND TABLE_NAME IN ('cars','user_data','orders')");
    $tables = [];
    if ($result) {
        while ($row = $result->fetch_row()) $tables[$row[0]] = true;
        $result->free();
    }
    if (isset($tables['cars'], $tables['user_data'], $tables['orders'])) {
        $chosen = $candidate;
        break;
    }
}

if ($chosen === null) {
    $dbs = @$con->query('SHOW DATABASES');
    if ($dbs) {
        while ($row = $dbs->fetch_row()) {
            $candidate = (string)$row[0];
            if (in_array($candidate, ['information_schema', 'mysql', 'performance_schema', 'phpmyadmin', 'sys'], true)) {
                continue;
            }
            $hasCars = false; $hasUsers = false; $hasOrders = false;
            $r = @$con->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='" . $con->real_escape_string($candidate) . "' AND TABLE_NAME IN ('cars','user_data','orders')");
            if ($r) {
                while ($t = $r->fetch_row()) {
                    if ($t[0] === 'cars') $hasCars = true;
                    elseif ($t[0] === 'user_data') $hasUsers = true;
                    elseif ($t[0] === 'orders') $hasOrders = true;
                }
                $r->free();
            }
            if ($hasCars && $hasUsers && $hasOrders) {
                $chosen = $candidate;
                break;
            }
        }
        $dbs->free();
    }
}

if ($chosen === null || !$con->select_db($chosen)) {
    $con->close();
    $con = false;
}
