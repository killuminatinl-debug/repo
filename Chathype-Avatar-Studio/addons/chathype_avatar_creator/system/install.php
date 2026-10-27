<?php
$load_addons = 'chathype_avatar_creator';
require_once __DIR__ . '/../../../system/config_addons.php';
require_once __DIR__ . '/settings.php';
if (isset($mysqli) && $mysqli instanceof mysqli) {
    chathypeAvatarStudioTable();
    $mysqli->query("UPDATE boom_addons SET custom1 = '1' WHERE addons = 'chathype_avatar_creator' AND (custom1 IS NULL OR custom1 = '')");
}
