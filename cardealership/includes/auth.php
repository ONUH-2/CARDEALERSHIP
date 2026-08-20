<?php
session_start();

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['username']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: /cardealership/login/login.php');
        exit;
    }
}

function is_admin_user(): bool {
    return is_logged_in() && !empty($_SESSION['email']) && strtolower($_SESSION['email']) === 'ytgg2909@gmail.com';
}

function require_admin(): void {
    if (!is_admin_user()) {
        header('Location: /cardealership/index.php');
        exit;
    }
}

function esc($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
