<?php
declare(strict_types=1);
require __DIR__ . '/admin_auth.php';
requireAdmin();
require __DIR__ . '/db.php';

$errors = [];
$success = isset($_GET['created']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['csrf_token'] ?? '');
    $title = trim((string) ($_POST['title'] ?? ''));
    $date = trim((string) ($_POST['event_date'] ?? ''));
    $time = trim((string) ($_POST['event_time'] ?? ''));
    $audience = trim((string) ($_POST['audience'] ?? ''));
    $venue = trim((string) ($_POST['venue'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));

    if (!hash_equals(adminCsrfToken(), $token)) $errors[] = 'Your session expired. Please try again.';
    if ($title === '' || mb_strlen($title) > 120) $errors[] = 'Enter an event title of up to 120 characters.';
    $dateValue = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$dateValue || $dateValue->format('Y-m-d') !== $date) $errors[] = 'Choose a valid event date.';
    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) $errors[] = 'Choose a valid event time.';
    if ($audience === '' || mb_strlen($audience) > 80) $errors[] = 'Enter the intended audience.';
    if ($venue === '' || mb_strlen($venue) > 120) $errors[] = 'Enter the event venue.';
    if ($description === '' || mb_strlen($description) > 2000) $errors[] = 'Enter a description of up to 2,000 characters.';

    if (!$errors) {
        $statement = $mysqli->prepare('INSERT INTO events (title, event_date, event_time, audience, venue, description) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->bind_param('ssssss', $title, $date, $time, $audience, $venue, $description);
        $statement->execute();
        header('Location: admin_profile.php?created=1');
        exit;
    }
}

$events = $mysqli->query('SELECT id, title, event_date, event_time, venue FROM events ORDER BY event_date, event_time');
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="csslab.css"><title>Moonlight Library | Admin Profile</title></head>
<body><header><h1>Admin profile</h1><p>Manage Moonlight Library content.</p><nav aria-label="Admin navigation"><a href="index.php">View site</a><a href="admin_profile.php" aria-current="page">Dashboard</a><a href="view_members.php">Members</a><a href="admin_logout.php">Sign out</a></nav></header>
<main>
<section><h2>Add an event</h2>
<?php if ($success): ?><p class="form-message success-message" role="status">The event was published successfully.</p><?php endif; ?>
<?php if ($errors): ?><div class="form-message error-message" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="admin_profile.php">
<input type="hidden" name="csrf_token" value="<?= e(adminCsrfToken()) ?>">
<p><label for="title">Event title</label><br><input id="title" name="title" maxlength="120" required value="<?= e($title ?? '') ?>"></p>
<p><label for="event-date">Date</label><br><input type="date" id="event-date" name="event_date" required value="<?= e($date ?? '') ?>"></p>
<p><label for="event-time">Time</label><br><input type="time" id="event-time" name="event_time" required value="<?= e($time ?? '') ?>"></p>
<p><label for="audience">Audience</label><br><input id="audience" name="audience" maxlength="80" placeholder="e.g. Everyone" required value="<?= e($audience ?? '') ?>"></p>
<p><label for="venue">Venue</label><br><input id="venue" name="venue" maxlength="120" required value="<?= e($venue ?? '') ?>"></p>
<p><label for="description">Description</label><br><textarea id="description" name="description" rows="5" maxlength="2000" required><?= e($description ?? '') ?></textarea></p>
<button type="submit">Publish event</button>
</form></section>
<aside><h2>Published events</h2><?php if ($events->num_rows === 0): ?><p>No admin events have been added.</p><?php else: ?><ul class="admin-event-list"><?php while ($event = $events->fetch_assoc()): ?><li><strong><?= e($event['title']) ?></strong><br><time datetime="<?= e($event['event_date'] . 'T' . $event['event_time']) ?>"><?= e(date('j M Y, g:i a', strtotime($event['event_date'] . ' ' . $event['event_time']))) ?></time><br><?= e($event['venue']) ?></li><?php endwhile; ?></ul><?php endif; ?><p><a class="button" href="events.php">View public events</a></p></aside>
</main><footer><p><small>&copy; 2026 Moonlight Library</small></p></footer></body></html>
