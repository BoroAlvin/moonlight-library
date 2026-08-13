<?php
declare(strict_types=1);
require __DIR__ . '/member_auth.php';
requireMember();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}

$token = (string) ($_POST['csrf_token'] ?? '');
$eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? '');
if (!hash_equals(memberCsrfToken(), $token) || !$eventId || !in_array($action, ['reserve', 'cancel'], true)) {
    header('Location: events.php?message=invalid');
    exit;
}

if ($action === 'reserve') {
    $statement = $mysqli->prepare('INSERT IGNORE INTO event_reservations (member_id, event_id) SELECT ?, id FROM events WHERE id = ? AND event_date >= CURRENT_DATE');
    $statement->bind_param('ii', $_SESSION['member_id'], $eventId);
    $statement->execute();
    $message = $statement->affected_rows ? 'reserved' : 'already';
} else {
    $statement = $mysqli->prepare('DELETE FROM event_reservations WHERE member_id = ? AND event_id = ?');
    $statement->bind_param('ii', $_SESSION['member_id'], $eventId);
    $statement->execute();
    $message = 'cancelled';
}

$returnTo = (string) ($_POST['return_to'] ?? 'events');
header('Location: ' . ($returnTo === 'profile' ? 'member_profile.php' : 'events.php') . '?message=' . $message);
exit;
