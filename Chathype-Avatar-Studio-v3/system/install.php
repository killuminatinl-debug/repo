<?php
require_once __DIR__ . '/bootstrap.php';
if (!avatarV3IsAdmin()) { http_response_code(403); exit('Alleen beheerders.'); }
try { $db=avatarV3Db(); } catch(Throwable $e) { exit('Databaseverbinding ontbreekt; controleer CodyChat-bootstrap.'); }
$sql=array(
"CREATE TABLE IF NOT EXISTS chathype_avatar_v3_settings (setting_key VARCHAR(80) NOT NULL,setting_value TEXT NOT NULL,updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY(setting_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS chathype_avatar_v3_profiles (user_id INT UNSIGNED NOT NULL,avatar_json TEXT NOT NULL,generated_file VARCHAR(190) NOT NULL DEFAULT '',updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY(user_id),KEY updated_at(updated_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS chathype_avatar_v3_backups (user_id INT UNSIGNED NOT NULL,original_avatar VARCHAR(255) NOT NULL DEFAULT '',addon_avatar VARCHAR(255) NOT NULL DEFAULT '',saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
foreach($sql as $q) if(!$db->query($q)) exit('SQL-installatie mislukt: '.avatarV3Escape($db->error));
$defs=array('enabled'=>'1','allow_random'=>'1','allow_restore'=>'1','studio_title'=>'Chathype Avatar Studio');
$s=$db->prepare('INSERT IGNORE INTO chathype_avatar_v3_settings(setting_key,setting_value) VALUES(?,?)');
foreach($defs as $k=>$v){$s->bind_param('ss',$k,$v);if(!$s->execute())exit('Instellingen konden niet worden aangemaakt.');}$s->close();
echo '<meta charset="utf-8"><h1>Avatar Studio v3 geïnstalleerd</h1><p>Blokkeer of verwijder nu system/install.php en koppel addon.php aan je CodyChat-addonvenster.</p>';
