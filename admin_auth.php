<?php
// ADMIN AUTHENTICATION: starts a secure session, protects admin pages, and creates CSRF tokens.
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

function adminIsLoggedIn(): bool
{
    return isset($_SESSION['admin_authenticated'])
        && $_SESSION['admin_authenticated'] === true;
}

function requireAdmin(): void
{
    if (adminIsLoggedIn()) {
        return;
    }

    $_SESSION['login_return_to'] = basename($_SERVER['PHP_SELF'] ?? 'view_members.php');
    header('Location: admin_login.php');
    exit;
}

function adminCsrfToken(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf'];
}
