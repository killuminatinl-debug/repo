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
 $json=json_encode($cfg);
 $s=$db->prepare('SELECT user_tumb FROM boom_users WHERE user_id=? LIMIT 1');$s->bind_param('i',$user);$s->execute();$s->bind_result($currentAvatar);
 if(!$s->fetch()){$s->close();avatarV3Json(array('ok'=>false,'error'=>'Chatgebruiker niet gevonden.'),404);}$s->close();
 $dir=dirname(__DIR__).'/generated';
 if(!is_dir($dir) && !@mkdir($dir,0755,true))avatarV3Json(array('ok'=>false,'error'=>'Map generated ontbreekt en kan niet worden aangemaakt.'),500);
 if(!is_writable($dir))avatarV3Json(array('ok'=>false,'error'=>'Map generated is niet schrijfbaar.'),500);
 $file='avatar_'.$user.'.svg';$full=$dir.'/'.$file;
 $skin=$cfg['skin'];$hair=$cfg['hair'];$bg=$cfg['background'];$shirt=$cfg['shirt'];$rx=$cfg['face']==='angular'?58:($cfg['face']==='round'?66:61);
 $hairSvg='';if($cfg['hairStyle']==='long')$hairSvg='<path d="M75 126 Q56 56 111 46 Q174 18 222 75 Q240 111 226 196 L205 215 L202 112 Q157 85 105 103 L92 204 L74 190Z" fill="'.$hair.'"/>';elseif($cfg['hairStyle']==='curly')$hairSvg='<path d="M76 112 Q55 85 81 70 Q73 40 107 49 Q126 20 149 45 Q179 20 193 51 Q226 48 222 82 Q246 98 222 124 L204 103 Q160 78 104 100 L91 126Z" fill="'.$hair.'"/>';elseif($cfg['hairStyle']!=='none')$hairSvg='<path d="M76 119 Q62 48 131 43 Q201 38 219 102 L205 122 L193 84 Q149 67 104 94 L90 128Z" fill="'.$hair.'"/>';
 $eyeSvg=$cfg['eyes']==='happy'?'<path d="M124 144 q8 -8 16 0 M180 144 q8 -8 16 0" stroke="#292338" stroke-width="5" fill="none" stroke-linecap="round"/>':'<ellipse cx="132" cy="145" rx="5" ry="7" fill="#292338"/><ellipse cx="188" cy="145" rx="5" ry="7" fill="#292338"/>';
 $shirtSvg='<path d="M75 310 L83 246 Q95 220 124 216 L160 242 L196 216 Q225 222 237 246 L245 310Z" fill="'.$shirt.'"/>';
 $acc='';if($cfg['accessory']==='glasses')$acc='<g fill="none" stroke="#292338" stroke-width="5"><rect x="111" y="128" width="34" height="26" rx="8"/><rect x="175" y="128" width="34" height="26" rx="8"/><path d="M145 139 H175"/></g>';elseif($cfg['accessory']==='earrings')$acc='<circle cx="82" cy="160" r="6" fill="#f5c451"/><circle cx="238" cy="160" r="6" fill="#f5c451"/>';
 $svg='<svg xmlns="http://www.w3.org/2000/svg" width="320" height="320" viewBox="0 0 320 320"><rect width="320" height="320" rx="34" fill="'.$bg.'"/>'.$shirtSvg.'<path d="M139 190 L139 226 Q160 245 181 226 L181 190Z" fill="'.$skin.'"/><ellipse cx="160" cy="135" rx="'.$rx.'" ry="77" fill="'.$skin.'"/>'.$hairSvg.$eyeSvg.'<path d="M142 177 Q160 190 178 177" stroke="#8d4e51" stroke-width="4" fill="none" stroke-linecap="round"/>'.$acc.'</svg>';
 if(@file_put_contents($full,$svg,LOCK_EX)===false)avatarV3Json(array('ok'=>false,'error'=>'Avatarbestand kon niet worden aangemaakt.'),500);
 $relative='addons/Chathype-Avatar-Studio-v3/generated/'.$file;
 $s=$db->prepare('INSERT IGNORE INTO chathype_avatar_v3_backups(user_id,original_avatar,addon_avatar) VALUES(?,?,?)');$s->bind_param('iss',$user,$currentAvatar,$relative);$s->execute();$s->close();
 $s=$db->prepare('UPDATE boom_users SET user_tumb=? WHERE user_id=?');$s->bind_param('si',$relative,$user);$ok=$s->execute();$s->close();
 if(!$ok){@unlink($full);avatarV3Json(array('ok'=>false,'error'=>'Avatarinstellingen konden niet aan je chatprofiel worden gekoppeld.'),500);}
 $s=$db->prepare('INSERT INTO chathype_avatar_v3_profiles (user_id,avatar_json,generated_file) VALUES (?,?,?) ON DUPLICATE KEY UPDATE avatar_json=VALUES(avatar_json),generated_file=VALUES(generated_file),updated_at=CURRENT_TIMESTAMP');$s->bind_param('iss',$user,$json,$relative);
 if(!$s->execute()){$s->close();avatarV3Json(array('ok'=>false,'error'=>'Avatar is gekoppeld, maar instellingen konden niet worden opgeslagen.'),500);}$s->close();
 if(function_exists('redisUpdateUser')){try{redisUpdateUser($user);}catch(Throwable $e){}}
 avatarV3Json(array('ok'=>true,'message'=>'Avatar opgeslagen en gekoppeld aan je chatprofiel.','config'=>$cfg));
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
