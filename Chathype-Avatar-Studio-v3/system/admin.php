<?php
require_once __DIR__ . '/bootstrap.php';
avatarV3RequireUser();
if(!avatarV3IsAdmin()){http_response_code(403);exit('Geen toegang.');}
$db=avatarV3Db();$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $sent=isset($_POST['csrf'])?(string)$_POST['csrf']:'';
 if(empty($_SESSION['chathype_avatar_v3_csrf'])||!hash_equals($_SESSION['chathype_avatar_v3_csrf'],$sent)){http_response_code(403);exit('Token verlopen.');}
 $values=array('enabled'=>isset($_POST['enabled'])?'1':'0','allow_random'=>isset($_POST['allow_random'])?'1':'0','allow_restore'=>isset($_POST['allow_restore'])?'1':'0','studio_title'=>substr(trim(isset($_POST['studio_title'])?(string)$_POST['studio_title']:''),0,80));
 $s=$db->prepare('INSERT INTO chathype_avatar_v3_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
 foreach($values as $k=>$v){$s->bind_param('ss',$k,$v);if(!$s->execute()){$message='Opslaan mislukt.';break;}}$s->close();if(!$message)$message='Instellingen opgeslagen.';
}
$cfg=array();$r=$db->query('SELECT setting_key,setting_value FROM chathype_avatar_v3_settings');if($r){while($x=$r->fetch_assoc())$cfg[$x['setting_key']]=$x['setting_value'];$r->free();}
$r=$db->query('SELECT COUNT(*) AS n FROM chathype_avatar_v3_profiles');$count=$r?(int)$r->fetch_assoc()['n']:0;$csrf=avatarV3Csrf();
?>
<link rel="stylesheet" href="../assets/avatar-studio.css">
<div class="chav3 chav3-admin"><header class="chav3-header"><div class="chav3-brandmark">✦</div><div><div class="chav3-eyebrow">CHATHYPE · CONTROL CENTER</div><h2>Avatar Studio beheer</h2><p>Instellingen en gebruik in één overzicht.</p></div></header>
<div class="chav3-admin-grid"><div class="chav3-stat"><span>Opgeslagen avatarprofielen</span><strong><?php echo $count; ?></strong><small>Profielen in de database</small></div><div class="chav3-stat"><span>Studio status</span><strong><?php echo isset($cfg['enabled'])&&$cfg['enabled']==='1'?'Actief':'Uitgeschakeld'; ?></strong><small>Na opslaan direct van toepassing</small></div></div>
<form method="post" class="chav3-admin-form"><input type="hidden" name="csrf" value="<?php echo avatarV3Escape($csrf); ?>"><?php if($message):?><p class="chav3-admin-message"><?php echo avatarV3Escape($message);?></p><?php endif;?>
<h3>Algemene instellingen</h3><label class="chav3-admin-label">Naam van de studio<input name="studio_title" maxlength="80" value="<?php echo avatarV3Escape(isset($cfg['studio_title'])?$cfg['studio_title']:'Chathype Avatar Studio');?>"></label>
<label class="chav3-toggle"><span><b>Avatar Studio inschakelen</b><small>Spelers mogen de studio openen.</small></span><input type="checkbox" name="enabled" <?php echo isset($cfg['enabled'])&&$cfg['enabled']==='1'?'checked':'';?>></label>
<label class="chav3-toggle"><span><b>Willekeurige avatar</b><small>Spelers mogen een willekeurige look maken.</small></span><input type="checkbox" name="allow_random" <?php echo isset($cfg['allow_random'])&&$cfg['allow_random']==='1'?'checked':'';?>></label>
<label class="chav3-toggle"><span><b>Avatar herstellen</b><small>Toon de hersteloptie in de studio.</small></span><input type="checkbox" name="allow_restore" <?php echo isset($cfg['allow_restore'])&&$cfg['allow_restore']==='1'?'checked':'';?>></label>
<button class="chav3-btn chav3-btn-dark" type="submit">Instellingen opslaan →</button></form></div>
