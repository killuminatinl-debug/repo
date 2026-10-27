<?php
/* Chathype Avatar Studio rebuilt — PHP 7.4+ */
if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
$chasRoot = dirname(__DIR__, 2);
if (is_file($chasRoot . '/system/config_session.php')) require_once $chasRoot . '/system/config_session.php';
if (!isset($GLOBALS['mysqli']) && isset($mysqli)) $GLOBALS['mysqli'] = $mysqli;

function chas_e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function chas_db() {
    if (isset($GLOBALS['mysqli']) && $GLOBALS['mysqli'] instanceof mysqli) return $GLOBALS['mysqli'];
    throw new RuntimeException('CodyChat databaseverbinding ontbreekt.');
}
function chas_user_id() {
    foreach (array('data','user') as $g) {
        if (isset($GLOBALS[$g]) && is_array($GLOBALS[$g])) {
            foreach (array('user_id','id') as $k) if (isset($GLOBALS[$g][$k]) && is_numeric($GLOBALS[$g][$k])) return (int)$GLOBALS[$g][$k];
        }
    }
    foreach (array('user_id','uid') as $k) {
        if (isset($_SESSION[$k]) && is_numeric($_SESSION[$k])) return (int)$_SESSION[$k];
        if (isset($GLOBALS[$k]) && is_numeric($GLOBALS[$k])) return (int)$GLOBALS[$k];
    }
    return 0;
}
function chas_require_user() {
    $id = chas_user_id();
    if ($id < 1) { http_response_code(401); exit('Log eerst in bij CodyChat.'); }
    $db = chas_db();
    $stmt = $db->prepare('SELECT user_id FROM boom_users WHERE user_id=? LIMIT 1');
    if (!$stmt) { http_response_code(500); exit('Databasecontrole mislukt.'); }
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->store_result(); $valid = $stmt->num_rows === 1; $stmt->close();
    if (!$valid) { http_response_code(401); exit('CodyChat-gebruiker niet gevonden.'); }
}
function chas_is_admin() {
    if (function_exists('isGreater')) { try { return (bool)isGreater(2); } catch (Throwable $e) {} }
    foreach (array('data','user') as $g) if (isset($GLOBALS[$g]) && is_array($GLOBALS[$g])) {
        foreach (array('user_rank','user_access','user_role') as $k) if (isset($GLOBALS[$g][$k]) && is_numeric($GLOBALS[$g][$k]) && (int)$GLOBALS[$g][$k] >= 99) return true;
    }
    return false;
}
function chas_csrf() {
    if (empty($_SESSION['chas_avatar_csrf'])) $_SESSION['chas_avatar_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['chas_avatar_csrf'];
}
function chas_json($payload, $status=200) {
    if (!headers_sent()) { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store'); header('X-Content-Type-Options: nosniff'); }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit;
}
function chas_install_schema() {
    $db = chas_db();
    $queries = array(
        "CREATE TABLE IF NOT EXISTS chas_settings (setting_key VARCHAR(64) NOT NULL PRIMARY KEY, setting_value VARCHAR(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS chas_profiles (user_id INT NOT NULL PRIMARY KEY, avatar_json TEXT NOT NULL, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS chas_backups (user_id INT NOT NULL PRIMARY KEY, original_avatar VARCHAR(255) NOT NULL DEFAULT '', addon_avatar VARCHAR(255) NOT NULL DEFAULT '', updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    foreach ($queries as $q) if (!$db->query($q)) throw new RuntimeException('Installatie database mislukt.');
    $db->query("INSERT IGNORE INTO chas_settings (setting_key,setting_value) VALUES ('enabled','1')");
}
function chas_enabled() {
    try { $db=chas_db(); $r=$db->query("SELECT setting_value FROM chas_settings WHERE setting_key='enabled' LIMIT 1"); if ($r && ($row=$r->fetch_assoc())) { $r->free(); return $row['setting_value']==='1'; } if ($r) $r->free(); } catch (Throwable $e) {}
    return true;
}
