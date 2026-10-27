<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
$load_addons = 'chathype_avatar_creator';
require_once __DIR__ . '/../../../system/config_addons.php';
require_once __DIR__ . '/settings.php';

function chas_reply($ok, $message, $extra = array()) {
    echo json_encode(array_merge(array('ok' => $ok, 'message' => $message), $extra));
    exit;
}
if (!isset($data['user_id']) || (int)$data['user_id'] < 1) chas_reply(false, 'Log eerst in.');
if (!chathypeAvatarStudioEnabled()) chas_reply(false, 'Avatar Studio is momenteel uitgeschakeld.');
if (strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '') !== 'POST') chas_reply(false, 'Ongeldige aanvraag.');
if (!isset($_POST['action']) || $_POST['action'] !== 'save_avatar') chas_reply(false, 'Onbekende actie.');

$raw = isset($_POST['image']) ? (string)$_POST['image'] : '';
if (!preg_match('~^data:image/png;base64,([A-Za-z0-9+/=]+)$~', $raw, $m)) chas_reply(false, 'De avatar-afbeelding is ongeldig.');
if (strlen($raw) > 1900000) chas_reply(false, 'De avatar is te groot.');
$binary = base64_decode($m[1], true);
if ($binary === false || strlen($binary) > 1400000) chas_reply(false, 'De afbeelding kon niet worden gelezen.');
$info = @getimagesizefromstring($binary);
if (!$info || $info[0] !== 360 || $info[1] !== 360 || $info['mime'] !== 'image/png') chas_reply(false, 'De avatar moet een PNG van 360 × 360 zijn.');
if (!isset($mysqli) || !($mysqli instanceof mysqli)) chas_reply(false, 'Databaseverbinding ontbreekt.');

chathypeAvatarStudioTable();
$uid = (int)$data['user_id'];
$stmt = $mysqli->prepare('SELECT user_tumb FROM boom_users WHERE user_id = ? LIMIT 1');
if (!$stmt) chas_reply(false, 'Databasefout bij het ophalen van je profiel.');
$stmt->bind_param('i', $uid);
$stmt->execute();
$stmt->bind_result($current);
$found = $stmt->fetch();
$stmt->close();
if (!$found) chas_reply(false, 'Gebruiker niet gevonden.');

$avatarDir = realpath(__DIR__ . '/../../../avatar');
if ($avatarDir === false || !is_dir($avatarDir) || !is_writable($avatarDir)) {
    chas_reply(false, 'De avatar-map bestaat niet of is niet schrijfbaar. Controleer de rechten van de CodyChat avatar-map.');
}
$old = (string)$current;
$backup = $mysqli->prepare("INSERT IGNORE INTO chathype_avatar_creator_backup (user_id, original_avatar, custom_avatar) VALUES (?, ?, '')");
if (!$backup) chas_reply(false, 'De avatar-back-up kon niet worden voorbereid.');
$backup->bind_param('is', $uid, $old);
if (!$backup->execute()) { $backup->close(); chas_reply(false, 'De avatar-back-up kon niet worden opgeslagen.'); }
$backup->close();

$filename = 'chathype_avatar_' . $uid . '_' . bin2hex(random_bytes(8)) . '.png';
$target = $avatarDir . DIRECTORY_SEPARATOR . $filename;
if (@file_put_contents($target, $binary, LOCK_EX) !== strlen($binary)) {
    @unlink($target);
    chas_reply(false, 'De avatar kon niet naar de server worden geschreven.');
}
$mysqli->begin_transaction();
try {
    $newAvatar = $filename;
    $up = $mysqli->prepare('UPDATE boom_users SET user_tumb = ? WHERE user_id = ?');
    if (!$up) throw new Exception('update');
    $up->bind_param('si', $newAvatar, $uid);
    if (!$up->execute() || $up->affected_rows < 0) { $up->close(); throw new Exception('update'); }
    $up->close();
    $bp = $mysqli->prepare('UPDATE chathype_avatar_creator_backup SET custom_avatar = ? WHERE user_id = ?');
    if (!$bp) throw new Exception('backup');
    $bp->bind_param('si', $newAvatar, $uid);
    if (!$bp->execute()) { $bp->close(); throw new Exception('backup'); }
    $bp->close();
    $mysqli->commit();
} catch (Exception $e) {
    $mysqli->rollback();
    @unlink($target);
    chas_reply(false, 'Opslaan is mislukt; je vorige avatar is behouden.');
}
if (function_exists('redisUpdateUser')) { @redisUpdateUser($uid); }
chas_reply(true, 'Je nieuwe avatar is opgeslagen!', array('avatar' => $filename));
