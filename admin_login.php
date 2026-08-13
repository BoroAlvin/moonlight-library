<?php
// ADMIN LOGIN: verifies an administrator email/password stored in MySQL.
declare(strict_types=1);
require __DIR__ . '/admin_auth.php';
require __DIR__ . '/db.php';

if (adminIsLoggedIn()) {
    header('Location: admin_profile.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PHP FORM PROCESSING: validate the CSRF token and read submitted credentials.
    $validToken = isset($_POST['csrf_token'])
        && hash_equals(adminCsrfToken(), (string) $_POST['csrf_token']);
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $validCredentials = false;

    if ($validToken && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // MYSQLI RETRIEVAL: prepared statements keep user input separate from SQL instructions.
        $statement = $mysqli->prepare('SELECT id, email, password_hash FROM administrators WHERE email = ? LIMIT 1');
        $statement->bind_param('s', $email);
        $statement->execute();
        $admin = $statement->get_result()->fetch_assoc();
        $validCredentials = $admin !== null
            && password_verify((string) ($_POST['password'] ?? ''), $admin['password_hash']);
    }

    if ($validCredentials) {
        // AUTHENTICATION SUCCESS: regenerate the session ID to prevent session fixation.
        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        unset($_SESSION['admin_csrf']);
        $destination = $_SESSION['login_return_to'] ?? 'view_members.php';
        unset($_SESSION['login_return_to']);
        header('Location: ' . ($destination === 'view_members.php' ? $destination : 'admin_profile.php'));
        exit;
    }

    $error = 'The email address or password is incorrect.';
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
  <p><label for="email">Admin email</label><br><input type="email" id="email" name="email" autocomplete="username" required autofocus></p>
  <p><label for="password">Password</label><br><input type="password" id="password" name="password" autocomplete="current-password" required></p>
  <button type="submit">Sign in</button>
</form></section></main>
<footer><p><small>&copy; 2026 Moonlight Library</small></p></footer>
</body></html>
