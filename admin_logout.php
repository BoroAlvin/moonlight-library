<?php
// ADMIN LOGOUT: clears session data and removes the session cookie.
declare(strict_types=1);
require __DIR__ . '/admin_auth.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parameters['path'], $parameters['domain'], $parameters['secure'], $parameters['httponly']);
}
session_destroy();
header('Location: admin_login.php');
exit;
