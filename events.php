<?php
declare(strict_types=1);
require __DIR__ . '/member_auth.php';
requireMember();
require __DIR__ . '/db.php';
require __DIR__ . '/member_nav.php';
$memberId = memberIsLoggedIn() ? $_SESSION['member_id'] : 0;
$statement = $mysqli->prepare('SELECT e.id, e.title, e.event_date, e.event_time, e.audience, e.venue, e.description, EXISTS(SELECT 1 FROM event_reservations r WHERE r.event_id = e.id AND r.member_id = ?) AS reserved FROM events e WHERE e.event_date >= CURRENT_DATE ORDER BY e.event_date, e.event_time');
$statement->bind_param('i', $memberId);
$statement->execute();
$events = $statement->get_result();
$messages = ['reserved' => 'Your seat has been reserved.', 'cancelled' => 'Your reservation was cancelled.', 'already' => 'You already reserved this event.', 'invalid' => 'That request could not be completed.'];
$messageKey = (string) ($_GET['message'] ?? '');
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="description" content="Upcoming workshops, reading circles, and activities at Moonlight Library."><link rel="stylesheet" href="csslab.css"><script src="script.js" defer></script><title>Moonlight Library | Events</title></head>
<body><header id="top"><h1>🗓️ Events &amp; Activities</h1><nav aria-label="Main navigation"><a href="index.php">Home</a><a href="catalogue.php">Book Catalogue</a><a href="events.php" aria-current="page">Events</a><a href="gallery.php">Gallery</a><a href="contact.php">Contact Us</a><a href="member_logout.php">Log Out</a><?= memberProfileNavigation($mysqli) ?></nav></header>
<main><section class="full-width"><h2>Upcoming events</h2><p>Discover activities published by the library team.</p>
<?php if (isset($messages[$messageKey])): ?><p class="form-message <?= $messageKey === 'invalid' ? 'error-message' : 'success-message' ?>" role="<?= $messageKey === 'invalid' ? 'alert' : 'status' ?>"><?= e($messages[$messageKey]) ?></p><?php endif; ?>
<?php if ($events->num_rows === 0): ?><p class="form-message error-message">There are no upcoming events yet. Please check again soon.</p><?php else: ?><div class="event-grid">
<?php while ($event = $events->fetch_assoc()): ?><article class="event-card"><p class="event-date"><time datetime="<?= e($event['event_date'] . 'T' . $event['event_time']) ?>"><?= e(date('D, j M Y · g:i a', strtotime($event['event_date'] . ' ' . $event['event_time']))) ?></time></p><h3><?= e($event['title']) ?></h3><p><?= nl2br(e($event['description'])) ?></p><dl><dt>Audience</dt><dd><?= e($event['audience']) ?></dd><dt>Venue</dt><dd><?= e($event['venue']) ?></dd></dl><?php if (memberIsLoggedIn()): ?><form class="event-action" method="post" action="event_reservation.php"><input type="hidden" name="csrf_token" value="<?= e(memberCsrfToken()) ?>"><input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>"><input type="hidden" name="action" value="<?= $event['reserved'] ? 'cancel' : 'reserve' ?>"><button type="submit" class="<?= $event['reserved'] ? 'secondary-button' : '' ?>"><?= $event['reserved'] ? 'Cancel reservation' : 'Reserve a seat' ?></button></form><?php else: ?><p><a class="button" href="member_login.php">Sign in to reserve</a></p><?php endif; ?></article><?php endwhile; ?>
</div><?php endif; ?></section></main><footer><p><small>&copy; 2026 Moonlight Library</small></p><p><a href="#top">Back to top ↑</a></p></footer></body></html>
