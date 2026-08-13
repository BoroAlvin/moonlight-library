<?php
declare(strict_types=1);
require __DIR__ . '/member_auth.php';
requireMember();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_POST['csrf_token'])
    || !hash_equals(memberCsrfToken(), (string) $_POST['csrf_token'])) {
    header('Location: member_profile.php?avatar=invalid#edit-profile');
    exit;
}

$memberId = $_SESSION['member_id'];
$lookup = $mysqli->prepare('SELECT avatar_filename FROM members WHERE id = ?');
$lookup->bind_param('i', $memberId);
$lookup->execute();
$currentAvatar = $lookup->get_result()->fetch_column();
$avatarDirectory = __DIR__ . '/uploads/avatars';

if (isset($_POST['remove_avatar'])) {
    $update = $mysqli->prepare('UPDATE members SET avatar_filename = NULL WHERE id = ?');
    $update->bind_param('i', $memberId);
    $update->execute();
    if ($currentAvatar) {
        $oldAvatar = $avatarDirectory . '/' . basename((string) $currentAvatar);
        if (is_file($oldAvatar)) unlink($oldAvatar);
    }
    header('Location: member_profile.php?avatar=removed#edit-profile');
    exit;
}

$upload = $_FILES['avatar'] ?? null;
if (!$upload || $upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > 2 * 1024 * 1024) {
    header('Location: member_profile.php?avatar=size#edit-profile');
    exit;
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
$extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
if (!isset($extensions[$mime]) || @getimagesize($upload['tmp_name']) === false) {
    header('Location: member_profile.php?avatar=type#edit-profile');
    exit;
}

if (!is_dir($avatarDirectory) && !mkdir($avatarDirectory, 0755, true) && !is_dir($avatarDirectory)) {
    header('Location: member_profile.php?avatar=failed#edit-profile');
    exit;
}
$filename = 'member-' . $memberId . '-' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime];
if (!move_uploaded_file($upload['tmp_name'], $avatarDirectory . '/' . $filename)) {
    header('Location: member_profile.php?avatar=failed#edit-profile');
    exit;
}

$update = $mysqli->prepare('UPDATE members SET avatar_filename = ? WHERE id = ?');
$update->bind_param('si', $filename, $memberId);
$update->execute();
if ($currentAvatar) {
    $oldAvatar = $avatarDirectory . '/' . basename((string) $currentAvatar);
    if (is_file($oldAvatar)) unlink($oldAvatar);
}
header('Location: member_profile.php?avatar=updated#edit-profile');
exit;
