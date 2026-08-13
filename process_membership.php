<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: membership.html');
    exit;
}

function clean(string $key): string
{
    return trim($_POST[$key] ?? '');
}

$fullName = clean('full_name');
$birthDate = clean('birth_date');
$email = clean('email');
$password = (string) ($_POST['password'] ?? '');
$phone = clean('phone');
$address = clean('address');
$type = clean('type');
$genre = clean('genre');
$newsletter = isset($_POST['newsletter']) ? 1 : 0;
$agreement = isset($_POST['agreement']);
$allowedTypes = ['student', 'adult', 'senior'];
$errors = [];

if ($fullName === '') $errors[] = 'Full name is required.';
if ($birthDate === '') $errors[] = 'Date of birth is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
if (strlen($password) < 8) $errors[] = 'Password must contain at least 8 characters.';
if ($address === '') $errors[] = 'Home address is required.';
if (!in_array($type, $allowedTypes, true)) $errors[] = 'Choose a valid membership type.';
if (!$agreement) $errors[] = 'You must agree to the library rules.';

if (!$errors) {
    require __DIR__ . '/db.php';
    $email = strtolower($email);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $statement = $mysqli->prepare(
        'INSERT INTO members (full_name, birth_date, email, password_hash, phone, home_address, membership_type, favourite_genre, newsletter) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->bind_param('ssssssssi', $fullName, $birthDate, $email, $passwordHash, $phone, $address, $type, $genre, $newsletter);
    try {
        $statement->execute();
        $memberId = $statement->insert_id;
        header('Location: member_login.php?registered=1');
        exit;
    } catch (mysqli_sql_exception $exception) {
        if ($exception->getCode() === 1062) $errors[] = 'An account already exists for this email address.';
        else throw $exception;
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="csslab.css">
  <title>Moonlight Library | Application Result</title>
</head>
<body>
  <header><h1>Membership application</h1><nav><a href="member_login.php">Log In</a><a href="membership.html">Register</a></nav></header>
  <main>
    <section class="full-width">
      <?php if ($errors): ?>
        <h2>Application not submitted</h2>
        <div class="form-message error-message"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
        <p><a class="button" href="membership.html">Return to the form</a></p>
      <?php else: ?>
        <h2>Application saved successfully</h2>
        <div class="form-message success-message">PHP received the POST data and MySQL created member record #<?= (int) $memberId ?>.</div>
        <dl class="submission-summary">
          <dt>Name</dt><dd><?= e($fullName) ?></dd>
          <dt>Email</dt><dd><?= e($email) ?></dd>
          <dt>Membership type</dt><dd><?= e(ucfirst($type)) ?></dd>
          <dt>Favourite genre</dt><dd><?= e($genre) ?></dd>
          <dt>Newsletter</dt><dd><?= $newsletter ? 'Yes' : 'No' ?></dd>
        </dl>
        <p><a class="button" href="member_login.php">Sign in to your profile</a></p>
      <?php endif; ?>
    </section>
  </main>
  <footer><p><small>&copy; 2026 Moonlight Library</small></p></footer>
</body>
</html>
