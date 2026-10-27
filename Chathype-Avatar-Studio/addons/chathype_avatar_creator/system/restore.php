<?php
header('Content-Type: application/json; charset=utf-8');
$load_addons = 'chathype_avatar_creator';
require_once __DIR__ . '/../../../system/config_addons.php';
require_once __DIR__ . '/settings.php';
function chas_restore_reply($ok, $message) { echo json_encode(array('ok'=>$ok,'message'=>$message)); exit; }
if (!isset($data['user_id']) || (int)$data['user_id'] < 1) chas_restore_reply(false, 'Log eerst in.');
if (!isset($mysqli) || !($mysqli instanceof mysqli)) chas_restore_reply(false, 'Databaseverbinding ontbreekt.');
chathypeAvatarStudioTable();
$uid = (int)$data['user_id'];
$stmt = $mysqli->prepare('SELECT original_avatar, custom_avatar FROM chathype_avatar_creator_backup WHERE user_id = ? LIMIT 1');
if (!$stmt) chas_restore_reply(false, 'Databasefout.');
$stmt->bind_param('i', $uid);
$stmt->execute();
$stmt->bind_result($original, $custom);
$found = $stmt->fetch();
$stmt->close();
if (!$found) chas_restore_reply(false, 'Er is nog geen eerdere avatar opgeslagen.');
$up = $mysqli->prepare('UPDATE boom_users SET user_tumb = ? WHERE user_id = ? AND user_tumb = ?');
if (!$up) chas_restore_reply(false, 'Herstellen is niet gelukt.');
$up->bind_param('sis', $original, $uid, $custom);
$up->execute();
$changed = $up->affected_rows;
$up->close();
if ($changed < 1) chas_restore_reply(false, 'Je avatar is intussen veranderd. Om te voorkomen dat we die overschrijven, is niets aangepast.');
$dir = realpath(__DIR__ . '/../../../avatar');
if ($dir && $custom !== '' && basename($custom) === $custom && strpos($custom, 'chathype_avatar_' . $uid . '_') === 0) @unlink($dir . DIRECTORY_SEPARATOR . $custom);
$del = $mysqli->prepare('DELETE FROM chathype_avatar_creator_backup WHERE user_id = ?');
if ($del) { $del->bind_param('i', $uid); $del->execute(); $del->close(); }
if (function_exists('redisUpdateUser')) { @redisUpdateUser($uid); }
chas_restore_reply(true, 'Je oorspronkelijke avatar is teruggezet.');
