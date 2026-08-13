<?php
declare(strict_types=1);

function memberProfileNavigation(mysqli $mysqli, bool $onProfile = false): string
{
    $statement = $mysqli->prepare('SELECT full_name, avatar_filename FROM members WHERE id = ?');
    $statement->bind_param('i', $_SESSION['member_id']);
    $statement->execute();
    $identity = $statement->get_result()->fetch_assoc();
    if (!$identity) return '';

    $name = htmlspecialchars($identity['full_name'], ENT_QUOTES, 'UTF-8');
    $destination = $onProfile ? '#edit-profile' : 'member_profile.php#edit-profile';
    if ($identity['avatar_filename']) {
        $filename = htmlspecialchars(basename($identity['avatar_filename']), ENT_QUOTES, 'UTF-8');
        $visual = '<img src="uploads/avatars/' . $filename . '" alt="' . $name . ' profile picture">';
    } else {
        $initial = htmlspecialchars(strtoupper(substr($identity['full_name'], 0, 1)), ENT_QUOTES, 'UTF-8');
        $visual = '<span aria-hidden="true">' . $initial . '</span>';
    }

    return '<a class="profile-nav-link" href="' . $destination . '" aria-label="Open and edit ' . $name . ' profile">'
        . $visual . '<span class="profile-nav-name">My Profile</span></a>';
}
