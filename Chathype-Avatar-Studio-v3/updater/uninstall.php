<?php
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    $candidate = dirname(__DIR__, 2) . '/system/database.php';
    if (is_file($candidate)) require_once $candidate;
}
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->query('DROP TABLE IF EXISTS chathype_avatar_v3_profiles');
    $mysqli->query('DROP TABLE IF EXISTS chathype_avatar_v3_backups');
    $mysqli->query('DROP TABLE IF EXISTS chathype_avatar_v3_settings');
}
