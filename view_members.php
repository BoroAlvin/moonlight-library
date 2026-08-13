<?php
// ADMIN MEMBER RECORDS: retrieves and displays registered members from MySQL.
declare(strict_types=1);
require __DIR__ . '/admin_auth.php';
requireAdmin();
require __DIR__ . '/db.php';
// MYSQLI RETRIEVAL: order the newest membership records first.
$result = $mysqli->query('SELECT id, full_name, email, membership_type, favourite_genre, newsletter, created_at FROM members ORDER BY created_at DESC');
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="stylesheet" href="csslab.css"><title>Moonlight Library | Member Records</title></head>
<body>
<header><h1>Library Member Records</h1><nav aria-label="Admin navigation"><a href="index.php">View site</a><a href="admin_profile.php">Events</a><a href="admin_books.php">Books</a><a href="view_members.php" aria-current="page">Members</a><a href="admin_logout.php">Sign out</a></nav></header>
<main><section class="full-width"><h2>Submitted memberships</h2><p>This table is retrieved live from the MySQL database using MySQLi.</p>
<?php if ($result->num_rows === 0): ?><p class="form-message error-message">No membership records have been submitted yet.</p>
<?php else: ?><div class="table-wrapper"><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Genre</th><th>Newsletter</th><th>Submitted</th></tr></thead><tbody>
<?php while ($member = $result->fetch_assoc()): ?><tr><td><?= (int) $member['id'] ?></td><td><?= e($member['full_name']) ?></td><td><?= e($member['email']) ?></td><td><?= e(ucfirst($member['membership_type'])) ?></td><td><?= e($member['favourite_genre']) ?></td><td><?= $member['newsletter'] ? 'Yes' : 'No' ?></td><td><?= e($member['created_at']) ?></td></tr><?php endwhile; ?>
</tbody></table></div><?php endif; ?></section></main><footer><p><small>&copy; 2026 Moonlight Library</small></p></footer>
</body></html>
