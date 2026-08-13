<?php
declare(strict_types=1);
require __DIR__ . '/admin_auth.php';
require __DIR__ . '/admin_config.php';

if (adminIsLoggedIn()) {
    header('Location: admin_profile.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validToken = isset($_POST['csrf_token'])
        && hash_equals(adminCsrfToken(), (string) $_POST['csrf_token']);
    $validCredentials = $validToken
        && hash_equals($adminUser, trim((string) ($_POST['username'] ?? '')))
        && password_verify((string) ($_POST['password'] ?? ''), $adminPasswordHash);

    if ($validCredentials) {
        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        unset($_SESSION['admin_csrf']);
        $destination = $_SESSION['login_return_to'] ?? 'view_members.php';
        unset($_SESSION['login_return_to']);
        header('Location: ' . ($destination === 'view_members.php' ? $destination : 'admin_profile.php'));
        exit;
    }

    $error = 'The username or password is incorrect.';
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="csslab.css"><title>Moonlight Library | Admin Login</title></head>
<body>
<header><h1>Admin login</h1><nav aria-label="Account navigation"><a href="member_login.php">Member Login</a><a href="membership.html">Register</a></nav></header>
<main><section class="full-width auth-card"><h2>Member records</h2><p>Sign in to view private membership information.</p>
<?php if ($error !== ''): ?><p class="form-message error-message" role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="admin_login.php">
  <input type="hidden" name="csrf_token" value="<?= e(adminCsrfToken()) ?>">
  <p><label for="username">Username</label><br><input id="username" name="username" autocomplete="username" required autofocus></p>
  <p><label for="password">Password</label><br><input type="password" id="password" name="password" autocomplete="current-password" required></p>
  <button type="submit">Sign in</button>
</form></section></main>
<footer><p><small>&copy; 2026 Moonlight Library</small></p></footer>
</body></html>
