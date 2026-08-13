<?php
// EVENT PROCESSING: handles reservation and cancellation POST requests for logged-in members.
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
    // INSERT IGNORE plus the unique database key prevents duplicate reservations.
    $statement = $mysqli->prepare('INSERT IGNORE INTO event_reservations (member_id, event_id) SELECT ?, id FROM events WHERE id = ? AND event_date >= CURRENT_DATE');
    $statement->bind_param('ii', $_SESSION['member_id'], $eventId);
    $statement->execute();
    if ($statement->affected_rows) {
        $message = 'reserved';
    } else {
        $lookup = $mysqli->prepare('SELECT EXISTS(SELECT 1 FROM event_reservations WHERE member_id = ? AND event_id = ?)');
        $lookup->bind_param('ii', $_SESSION['member_id'], $eventId);
        $lookup->execute();
        $message = $lookup->get_result()->fetch_column() ? 'already' : 'invalid';
    }
} else {
    // CANCELLATION: remove only the reservation owned by the logged-in member.
    $statement = $mysqli->prepare('DELETE FROM event_reservations WHERE member_id = ? AND event_id = ?');
    $statement->bind_param('ii', $_SESSION['member_id'], $eventId);
    $statement->execute();
    $message = $statement->affected_rows ? 'cancelled' : 'invalid';
}

$returnTo = (string) ($_POST['return_to'] ?? 'events');
header('Location: ' . ($returnTo === 'profile' ? 'member_profile.php' : 'events.php') . '?message=' . $message);
exit;
