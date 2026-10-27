<?php
require_once __DIR__ . '/system/bootstrap.php';
chas_require_user();
if (!chas_is_admin()) { http_response_code(403); exit('Geen toegang.'); }
chas_install_schema();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(chas_csrf(), isset($_POST['csrf']) ? (string)$_POST['csrf'] : '')) { http_response_code(403); exit('Beveiligingstoken verlopen.'); }
    $enabled = isset($_POST['enabled']) && $_POST['enabled'] === '1' ? '1' : '0';
    $db = chas_db(); $stmt = $db->prepare("INSERT INTO chas_settings (setting_key,setting_value) VALUES ('enabled',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    if ($stmt) { $stmt->bind_param('s', $enabled); $message = $stmt->execute() ? 'Instellingen opgeslagen.' : 'Opslaan mislukt.'; $stmt->close(); }
}
$enabled = chas_enabled();
?><!doctype html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Avatar Studio beheer</title><link rel="stylesheet" href="assets/studio.css"></head><body><main class="studio admin"><header><div class="mark">✦</div><div><small>CHATHYPE · BEHEER</small><h1>Avatar Studio</h1><p>Beheer de beschikbaarheid van de avatarbouwer.</p></div></header><section class="controls admin-card"><?php if ($message !== '') echo '<p role="status">'.chas_e($message).'</p>'; ?><form method="post"><input type="hidden" name="csrf" value="<?php echo chas_e(chas_csrf()); ?>"><label class="switch"><input type="checkbox" name="enabled" value="1" <?php echo $enabled ? 'checked' : ''; ?>> Avatar Studio ingeschakeld</label><button type="submit">Instellingen opslaan</button></form></section></main></body></html>