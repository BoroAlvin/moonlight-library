<?php
// MEMBER PROFILE: retrieves membership details, avatar, and upcoming event reservations.
declare(strict_types=1);
require __DIR__ . '/member_auth.php';
requireMember();
require __DIR__ . '/db.php';
require __DIR__ . '/member_nav.php';

// MYSQLI RETRIEVAL: load only the record belonging to the authenticated member.
$statement = $mysqli->prepare('SELECT full_name, birth_date, email, avatar_filename, phone, home_address, membership_type, favourite_genre, newsletter, created_at FROM members WHERE id = ?');
$statement->bind_param('i', $_SESSION['member_id']);
$statement->execute();
$member = $statement->get_result()->fetch_assoc();
if (!$member) {
    $_SESSION = [];
    session_destroy();
    header('Location: member_login.php');
    exit;
}
$reservationStatement = $mysqli->prepare('SELECT e.id, e.title, e.event_date, e.event_time, e.venue FROM event_reservations r JOIN events e ON e.id = r.event_id WHERE r.member_id = ? AND e.event_date >= CURRENT_DATE ORDER BY e.event_date, e.event_time');
$reservationStatement->bind_param('i', $_SESSION['member_id']);
$reservationStatement->execute();
$reservations = $reservationStatement->get_result();
$message = (string) ($_GET['message'] ?? '');
$avatarMessage = (string) ($_GET['avatar'] ?? '');
$avatarMessages = [
    'updated' => ['success-message', 'Your profile picture was updated.'],
    'removed' => ['success-message', 'Your profile picture was removed.'],
    'size' => ['error-message', 'Choose an image smaller than 2 MB.'],
    'type' => ['error-message', 'Choose a JPG, PNG, or WebP image.'],
    'failed' => ['error-message', 'The image could not be saved. Please try again.'],
    'invalid' => ['error-message', 'Your session expired. Please try again.'],
];
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="csslab.css"><title>Moonlight Library | My Profile</title></head>
<body><header><h1>My library profile</h1><p>Welcome, <?= e($member['full_name']) ?>.</p><nav aria-label="Member navigation"><a href="index.php">Home</a><a href="events.php">Events</a><a href="member_logout.php">Log Out</a><?= memberProfileNavigation($mysqli, true) ?></nav></header>
<main><section><h2>Membership details</h2><dl class="submission-summary"><dt>Name</dt><dd><?= e($member['full_name']) ?></dd><dt>Email</dt><dd><?= e($member['email']) ?></dd><dt>Phone</dt><dd><?= e($member['phone'] ?: 'Not provided') ?></dd><dt>Address</dt><dd><?= e($member['home_address']) ?></dd><dt>Membership</dt><dd><span class="status-badge available"><?= e(ucfirst($member['membership_type'])) ?></span></dd><dt>Favourite genre</dt><dd><?= e($member['favourite_genre']) ?></dd><dt>Newsletter</dt><dd><?= $member['newsletter'] ? 'Subscribed' : 'Not subscribed' ?></dd><dt>Member since</dt><dd><?= e(date('j F Y', strtotime($member['created_at']))) ?></dd></dl></section>
<aside><h2>My event reservations</h2><?php if ($message === 'cancelled'): ?><p class="form-message success-message" role="status">Your reservation was cancelled.</p><?php endif; ?><?php if ($reservations->num_rows === 0): ?><p>You have no upcoming reservations.</p><p><a class="button" href="events.php">Browse events</a></p><?php else: ?><ul class="admin-event-list"><?php while ($event = $reservations->fetch_assoc()): ?><li><strong><?= e($event['title']) ?></strong><br><time datetime="<?= e($event['event_date'] . 'T' . $event['event_time']) ?>"><?= e(date('j M Y, g:i a', strtotime($event['event_date'] . ' ' . $event['event_time']))) ?></time><br><?= e($event['venue']) ?><form class="event-action" method="post" action="event_reservation.php"><input type="hidden" name="csrf_token" value="<?= e(memberCsrfToken()) ?>"><input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>"><input type="hidden" name="action" value="cancel"><input type="hidden" name="return_to" value="profile"><button type="submit" class="secondary-button">Cancel</button></form></li><?php endwhile; ?></ul><p><a href="events.php">Browse more events</a></p><?php endif; ?></aside>
<section id="edit-profile" class="full-width profile-editor"><div class="profile-picture-preview"><?php if ($member['avatar_filename']): ?><img src="uploads/avatars/<?= e(basename($member['avatar_filename'])) ?>" alt="Current profile picture"><?php else: ?><span aria-hidden="true"><?= e(mb_strtoupper(mb_substr($member['full_name'], 0, 1))) ?></span><?php endif; ?></div><div><h2>Profile picture</h2><p>Upload a square JPG, PNG, or WebP image. Maximum size: 2 MB.</p><?php if (isset($avatarMessages[$avatarMessage])): ?><p class="form-message <?= e($avatarMessages[$avatarMessage][0]) ?>" role="status"><?= e($avatarMessages[$avatarMessage][1]) ?></p><?php endif; ?><form method="post" action="update_avatar.php" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(memberCsrfToken()) ?>"><p><label for="avatar">Choose a new picture</label><br><input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" required></p><button type="submit">Update picture</button><?php if ($member['avatar_filename']): ?> <button type="submit" name="remove_avatar" value="1" class="secondary-button" formnovalidate>Remove picture</button><?php endif; ?></form></div></section></main><footer><p><small>&copy; 2026 Moonlight Library</small></p></footer></body></html>
