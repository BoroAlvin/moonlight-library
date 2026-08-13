<?php
// MEMBER LOGOUT: destroys the authenticated session and returns to the login page.
declare(strict_types=1);
require __DIR__ . '/member_auth.php';
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parameters['path'], $parameters['domain'], $parameters['secure'], $parameters['httponly']);
}
session_destroy();
header('Location: member_login.php?logged_out=1');
exit;
