<?php
if (!defined('CHATHYPE_AVATAR_STUDIO')) define('CHATHYPE_AVATAR_STUDIO', true);
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
/* Find CodyChat root without assuming whether this addon is nested in addons/. */
$root = dirname(__DIR__);
for ($i = 0; $i < 6; $i++) {
    if (is_file($root . '/system/config_session.php') || is_file($root . '/system/database.php')) break;
    $parent = dirname($root);
    if ($parent === $root) break;
    $root = $parent;
}
if (!defined('CHATHYPE_AVATAR_V3_CHAT_ROOT')) define('CHATHYPE_AVATAR_V3_CHAT_ROOT', $root);
foreach (array($root . '/system/config_session.php', $root . '/system/database.php') as $file) {
    if (is_file($file)) require_once $file;
}
if (!isset($mysqli) && isset($GLOBALS['mysqli'])) $mysqli = $GLOBALS['mysqli'];
if (isset($mysqli) && $mysqli instanceof mysqli) $mysqli->set_charset('utf8mb4');
function avatarV3Db() {
    if (!isset($GLOBALS['mysqli']) || !($GLOBALS['mysqli'] instanceof mysqli)) {
        if (isset($GLOBALS['mysqli'])) return $GLOBALS['mysqli'];
        throw new RuntimeException('CodyChat databaseverbinding ontbreekt.');
    }
    return $GLOBALS['mysqli'];
}
function avatarV3Escape($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function avatarV3Csrf() {
    if (empty($_SESSION['chathype_avatar_v3_csrf'])) $_SESSION['chathype_avatar_v3_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['chathype_avatar_v3_csrf'];
}
function avatarV3CheckCsrf() {
    $sent = isset($_POST['csrf']) ? (string)$_POST['csrf'] : '';
    $known = isset($_SESSION['chathype_avatar_v3_csrf']) ? (string)$_SESSION['chathype_avatar_v3_csrf'] : '';
    if (!$sent || !$known || !hash_equals($known, $sent)) avatarV3Json(array('ok'=>false,'error'=>'Beveiligingstoken verlopen. Vernieuw de pagina.'),403);
}
function avatarV3Json($data, $status=200) {
    if (!headers_sent()) { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store'); header('X-Content-Type-Options: nosniff'); }
    echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
}
function avatarV3UserId() {
    if (isset($GLOBALS['data']['user_id']) && is_numeric($GLOBALS['data']['user_id'])) return (int)$GLOBALS['data']['user_id'];
    foreach (array('user_id','uid') as $key) if (isset($_SESSION[$key]) && is_numeric($_SESSION[$key])) return (int)$_SESSION[$key];
    return 0;
}
function avatarV3IsAdmin() {
    if (function_exists('isGreater')) { try { return (bool)isGreater(2); } catch (Throwable $e) {} }
    if (isset($GLOBALS['data']['user_rank']) && is_numeric($GLOBALS['data']['user_rank'])) return (int)$GLOBALS['data']['user_rank'] >= 99;
    if (isset($GLOBALS['data']['user_access']) && is_numeric($GLOBALS['data']['user_access'])) return (int)$GLOBALS['data']['user_access'] >= 99;
    return !empty($_SESSION['admin']);
}
function avatarV3RequireUser() { if (avatarV3UserId() < 1) { http_response_code(401); exit('Log eerst in op Chathype.'); } }
function avatarV3Setting($key,$default='') {
    static $cache = null;
    if ($cache === null) {
        $cache = array();
        try { $r=avatarV3Db()->query('SELECT setting_key, setting_value FROM chathype_avatar_v3_settings'); if ($r) { while($row=$r->fetch_assoc()) $cache[$row['setting_key']]=$row['setting_value']; $r->free(); } } catch (Throwable $e) {}
    }
    return isset($cache[$key]) ? $cache[$key] : $default;
}
