<?php
/* Chathype Avatar Studio — PHP 7.4+ */
if (!function_exists('chathypeAvatarStudioEnabled')) {
    function chathypeAvatarStudioEnabled()
    {
        global $mysqli;
        if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
            return true;
        }
        $stmt = $mysqli->prepare("SELECT custom1 FROM boom_addons WHERE addons = ? LIMIT 1");
        if (!$stmt) return true;
        $name = 'chathype_avatar_creator';
        $stmt->bind_param('s', $name);
        if (!$stmt->execute()) { $stmt->close(); return true; }
        $stmt->bind_result($enabled);
        $found = $stmt->fetch();
        $stmt->close();
        return !$found || (string)$enabled !== '0';
    }
}
if (!function_exists('chathypeAvatarStudioTable')) {
    function chathypeAvatarStudioTable()
    {
        global $mysqli;
        if (!isset($mysqli) || !($mysqli instanceof mysqli)) return false;
        return $mysqli->query("CREATE TABLE IF NOT EXISTS chathype_avatar_creator_backup (
            user_id INT NOT NULL PRIMARY KEY,
            original_avatar VARCHAR(255) NOT NULL DEFAULT '',
            custom_avatar VARCHAR(255) NOT NULL DEFAULT '',
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
