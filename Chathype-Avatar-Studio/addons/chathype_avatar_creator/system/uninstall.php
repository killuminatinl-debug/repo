<?php
/*
 * Veilig verwijderen: herstel avatars die nog door deze addon worden gebruikt.
 * Laat back-upgegevens bestaan als herstellen niet volledig lukt.
 */
$load_addons = 'chathype_avatar_creator';
require_once __DIR__ . '/../../../system/config_addons.php';
require_once __DIR__ . '/settings.php';
if (!isset($mysqli) || !($mysqli instanceof mysqli)) return;
$table = $mysqli->query("SHOW TABLES LIKE 'chathype_avatar_creator_backup'");
if (!$table || $table->num_rows < 1) return;
$dir = realpath(__DIR__ . '/../../../avatar');
$rows = $mysqli->query('SELECT user_id, original_avatar, custom_avatar FROM chathype_avatar_creator_backup');
if (!$rows) return;
$failed = false;
while ($row = $rows->fetch_assoc()) {
    $uid = (int)$row['user_id'];
    $original = (string)$row['original_avatar'];
    $custom = (string)$row['custom_avatar'];
    $stmt = $mysqli->prepare('UPDATE boom_users SET user_tumb = ? WHERE user_id = ? AND user_tumb = ?');
    if (!$stmt) { $failed = true; continue; }
    $stmt->bind_param('sis', $original, $uid, $custom);
    if (!$stmt->execute()) $failed = true;
    $stmt->close();
    if ($dir && $custom !== '' && basename($custom) === $custom && strpos($custom, 'chathype_avatar_' . $uid . '_') === 0) {
        if (is_file($dir . DIRECTORY_SEPARATOR . $custom) && !@unlink($dir . DIRECTORY_SEPARATOR . $custom)) $failed = true;
    }
    if (function_exists('redisUpdateUser')) { @redisUpdateUser($uid); }
}
$rows->free();
if (!$failed) $mysqli->query('DROP TABLE chathype_avatar_creator_backup');
