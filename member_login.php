<?php
// MEMBER LOGIN: retrieves the member by email and verifies the stored password hash.
declare(strict_types=1);
require __DIR__ . '/member_auth.php';
require __DIR__ . '/db.php';

if (memberIsLoggedIn()) {
    header('Location: member_profile.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST PROCESSING: validate the token and submitted email before querying MySQL.
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $validToken = isset($_POST['csrf_token']) && hash_equals(memberCsrfToken(), (string) $_POST['csrf_token']);
    $member = null;
    if ($validToken && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $statement = $mysqli->prepare('SELECT id, password_hash FROM members WHERE email = ? LIMIT 1');
        $statement->bind_param('s', $email);
        $statement->execute();
        $member = $statement->get_result()->fetch_assoc();
    }

    if ($validToken && $member && password_verify($password, $member['password_hash'])) {
        // LOGIN SUCCESS: store only the member ID in the authenticated session.
        session_regenerate_id(true);
        $_SESSION['member_id'] = (int) $member['id'];
        unset($_SESSION['member_csrf']);
        header('Location: index.php');
        exit;
    }
    $error = 'The email or password is incorrect.';
}
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="csslab.css"><title>Moonlight Library | Member Login</title></head>
<body><header><h1>Member login</h1><nav aria-label="Account navigation"><a href="membership.html">Register</a><a href="admin_login.php">Admin</a></nav></header>
<main><section class="full-width auth-card"><h2>Welcome back</h2><p>Sign in to view your library profile.</p><?php if (isset($_GET['registered'])): ?><p class="form-message success-message" role="status">Registration successful. Log in with your new account.</p><?php endif; ?><?php if (isset($_GET['logged_out'])): ?><p class="form-message success-message" role="status">You have logged out successfully.</p><?php endif; ?><?php if ($error): ?><p class="form-message error-message" role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(memberCsrfToken()) ?>"><p><label for="member-email">Email</label><br><input type="email" id="member-email" name="email" autocomplete="email" required autofocus></p><p><label for="member-password">Password</label><br><input type="password" id="member-password" name="password" autocomplete="current-password" required></p><button type="submit">Log In</button></form><p>Not a member? <a href="membership.html">Register now</a>.</p></section></main><footer><p><small>&copy; 2026 Moonlight Library</small></p></footer></body></html>
