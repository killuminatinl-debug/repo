<?php
require_once __DIR__ . '/bootstrap.php';
avatarV3RequireUser();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') avatarV3Json(array('ok'=>false,'error'=>'Gebruik POST.'),405);
avatarV3CheckCsrf();
try { $db=avatarV3Db(); } catch (Throwable $e) { avatarV3Json(array('ok'=>false,'error'=>'Databaseverbinding ontbreekt.'),500); }
if (avatarV3Setting('enabled','1') !== '1') avatarV3Json(array('ok'=>false,'error'=>'Avatar Studio is uitgeschakeld.'),403);
$user=avatarV3UserId(); $action=isset($_POST['action'])?(string)$_POST['action']:'';
function avatarV3Validate($raw) {
 $in=json_decode((string)$raw,true); if(!is_array($in)) return false;
 $sets=array(
 'skin'=>array('#f7d7bd','#e9b995','#c98d67','#8d5b43','#603d32'),
 'hair'=>array('#211a24','#49302b','#8b5436','#d7b36b','#d9d9df','#b44e5c','#5c72d6'),
 'background'=>array('#f1e8ff','#dff5f3','#fff0df','#e5edff','#ffe2ec','#24243a'),
 'shirt'=>array('#6956d8','#e55b93','#0e9eaa','#2d3548','#e7a33e','#e9e9ef'),
 'face'=>array('soft','round','angular'),'hairStyle'=>array('short','long','curly','fringe','bun','none'),
 'eyes'=>array('calm','happy','bold'),'outfit'=>array('tee','hoodie','jacket','formal'),
 'accessory'=>array('none','glasses','cap','earrings'));
 $out=array(); foreach($sets as $k=>$allowed){$v=isset($in[$k])?(string)$in[$k]:$allowed[0];if(!in_array($v,$allowed,true))return false;$out[$k]=$v;} return $out;
}
if($action==='load'){
 $s=$db->prepare('SELECT avatar_json FROM chathype_avatar_v3_profiles WHERE user_id=?');$s->bind_param('i',$user);$s->execute();$s->bind_result($json);$cfg=$s->fetch()?json_decode($json,true):null;$s->close();avatarV3Json(array('ok'=>true,'config'=>$cfg));
}
if($action==='save'){
 $cfg=avatarV3Validate(isset($_POST['config'])?$_POST['config']:'');if($cfg===false)avatarV3Json(array('ok'=>false,'error'=>'Ongeldige avatarinstellingen.'),422);
 $json=json_encode($cfg);$s=$db->prepare('INSERT INTO chathype_avatar_v3_profiles (user_id,avatar_json) VALUES (?,?) ON DUPLICATE KEY UPDATE avatar_json=VALUES(avatar_json),updated_at=CURRENT_TIMESTAMP');$s->bind_param('is',$user,$json);
 if(!$s->execute()){$s->close();avatarV3Json(array('ok'=>false,'error'=>'Opslaan is mislukt.'),500);}$s->close();avatarV3Json(array('ok'=>true,'message'=>'Avatarinstellingen opgeslagen.','config'=>$cfg));
}
if($action==='restore'){
 if(avatarV3Setting('allow_restore','1')!=='1')avatarV3Json(array('ok'=>false,'error'=>'Herstellen is uitgeschakeld.'),403);
 $s=$db->prepare('SELECT original_avatar,addon_avatar FROM chathype_avatar_v3_backups WHERE user_id=?');$s->bind_param('i',$user);$s->execute();$s->bind_result($original,$addon);
 if(!$s->fetch()){$s->close();avatarV3Json(array('ok'=>false,'error'=>'Geen herstelpunt beschikbaar.'),404);}$s->close();
 $s=$db->prepare('UPDATE boom_users SET user_tumb=? WHERE user_id=? AND user_tumb=?');$s->bind_param('sis',$original,$user,$addon);$s->execute();$changed=$s->affected_rows;$s->close();
 if(!$changed)avatarV3Json(array('ok'=>false,'error'=>'Herstellen niet uitgevoerd: avatar is gewijzigd of er bestaat geen match.'),409);
 avatarV3Json(array('ok'=>true,'message'=>'Oorspronkelijke avatar hersteld.'));
}
avatarV3Json(array('ok'=>false,'error'=>'Onbekende actie.'),400);
