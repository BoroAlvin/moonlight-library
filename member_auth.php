<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

function memberIsLoggedIn(): bool
{
    return isset($_SESSION['member_id']) && is_int($_SESSION['member_id']);
}

function requireMember(): void
{
    if (memberIsLoggedIn()) return;
    header('Location: member_login.php');
    exit;
}

function memberCsrfToken(): string
{
    if (empty($_SESSION['member_csrf'])) {
        $_SESSION['member_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['member_csrf'];
}
