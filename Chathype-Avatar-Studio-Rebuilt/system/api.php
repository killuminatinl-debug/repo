<?php
require_once __DIR__ . '/bootstrap.php';
chas_require_user();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') chas_json(array('ok'=>false,'error'=>'POST vereist.'),405);
if (!hash_equals(chas_csrf(), isset($_POST['csrf']) ? (string)$_POST['csrf'] : '')) chas_json(array('ok'=>false,'error'=>'Beveiligingstoken verlopen. Vernieuw de pagina.'),403);
try { chas_install_schema(); $db=chas_db(); } catch (Throwable $e) { chas_json(array('ok'=>false,'error'=>'Database-installatie of verbinding mislukt.'),500); }
if (!chas_enabled()) chas_json(array('ok'=>false,'error'=>'Avatar Studio is uitgeschakeld.'),403);
$uid=chas_user_id(); $action=isset($_POST['action'])?(string)$_POST['action']:'';
$allowed=array(
 'skin'=>array('#f7d7bd','#e9b995','#c98d67','#8d5b43','#603d32'),
 'hair'=>array('#211a24','#49302b','#8b5436','#d7b36b','#d9d9df','#b44e5c','#5c72d6'),
 'background'=>array('#f1e8ff','#dff5f3','#fff0df','#e5edff','#ffe2ec','#24243a'),
 'shirt'=>array('#6956d8','#e55b93','#0e9eaa','#2d3548','#e7a33e','#e9e9ef'),
 'face'=>array('soft','round','angular'),'hairStyle'=>array('short','long','curly','fringe','bun','none'),
 'eyes'=>array('calm','happy','bold'),'outfit'=>array('tee','hoodie','jacket','formal'),'accessory'=>array('none','glasses','cap','earrings')
);
if ($action==='load') {
    $stmt=$db->prepare('SELECT avatar_json FROM chas_profiles WHERE user_id=?'); $stmt->bind_param('i',$uid); $stmt->execute(); $stmt->bind_result($json); $cfg=$stmt->fetch()?json_decode($json,true):null; $stmt->close();
    chas_json(array('ok'=>true,'config'=>$cfg));
}
if ($action==='save') {
    $input=json_decode(isset($_POST['config'])?(string)$_POST['config']:'',true);
    if (!is_array($input)) chas_json(array('ok'=>false,'error'=>'Avatarinstellingen zijn ongeldig.'),422);
    $cfg=array();
    foreach ($allowed as $key=>$values) { $v=isset($input[$key])?(string)$input[$key]:$values[0]; if (!in_array($v,$values,true)) chas_json(array('ok'=>false,'error'=>'Ongeldige keuze: '.$key),422); $cfg[$key]=$v; }
    $stmt=$db->prepare('SELECT user_tumb FROM boom_users WHERE user_id=? LIMIT 1');
    if (!$stmt) chas_json(array('ok'=>false,'error'=>'Profiel niet beschikbaar.'),500);
    $stmt->bind_param('i',$uid); $stmt->execute(); $stmt->bind_result($old); if (!$stmt->fetch()) { $stmt->close(); chas_json(array('ok'=>false,'error'=>'Gebruiker niet gevonden.'),404); } $stmt->close();
    $dir=dirname(__DIR__).'/generated'; if (!is_dir($dir) && !@mkdir($dir,0755,true)) chas_json(array('ok'=>false,'error'=>'Kan generated-map niet aanmaken.'),500);
    if (!is_writable($dir)) chas_json(array('ok'=>false,'error'=>'generated-map is niet schrijfbaar.'),500);
    $file='avatar_'.$uid.'.svg'; $svg=chas_render_svg($cfg);
    if (@file_put_contents($dir.'/'.$file,$svg,LOCK_EX)===false) chas_json(array('ok'=>false,'error'=>'Avatarbestand opslaan mislukt.'),500);
    $relative='addons/Chathype-Avatar-Studio-Rebuilt/generated/'.$file;
    $stmt=$db->prepare('INSERT IGNORE INTO chas_backups(user_id,original_avatar,addon_avatar) VALUES(?,?,?)');
    if (!$stmt) { @unlink($dir.'/'.$file); chas_json(array('ok'=>false,'error'=>'Back-up niet beschikbaar.'),500); }
    $stmt->bind_param('iss',$uid,$old,$relative); $stmt->execute(); $stmt->close();
    $stmt=$db->prepare('UPDATE boom_users SET user_tumb=? WHERE user_id=?'); $stmt->bind_param('si',$relative,$uid);
    if (!$stmt->execute()) { $stmt->close(); chas_json(array('ok'=>false,'error'=>'Avatar koppelen aan profiel mislukt.'),500); } $stmt->close();
    $json=json_encode($cfg);
    $stmt=$db->prepare('INSERT INTO chas_profiles(user_id,avatar_json) VALUES(?,?) ON DUPLICATE KEY UPDATE avatar_json=VALUES(avatar_json)');
    $stmt->bind_param('is',$uid,$json); $ok=$stmt->execute(); $stmt->close();
    if (!$ok) chas_json(array('ok'=>false,'error'=>'Avatar gekoppeld, maar profielconfiguratie opslaan mislukt.'),500);
    if (function_exists('redisUpdateUser')) { try { redisUpdateUser($uid); } catch (Throwable $e) {} }
    chas_json(array('ok'=>true,'message'=>'Avatar opgeslagen en aan je profiel gekoppeld.','config'=>$cfg,'avatar_url'=>$relative.'?v='.time()));
}
if ($action==='restore') {
    $stmt=$db->prepare('SELECT original_avatar,addon_avatar FROM chas_backups WHERE user_id=?'); $stmt->bind_param('i',$uid); $stmt->execute(); $stmt->bind_result($original,$addon); if (!$stmt->fetch()) { $stmt->close(); chas_json(array('ok'=>false,'error'=>'Geen oorspronkelijke avatarback-up gevonden.'),404); } $stmt->close();
    $stmt=$db->prepare('UPDATE boom_users SET user_tumb=? WHERE user_id=? AND user_tumb=?'); $stmt->bind_param('sis',$original,$uid,$addon); $stmt->execute(); $changed=$stmt->affected_rows; $stmt->close();
    if (!$changed) chas_json(array('ok'=>false,'error'=>'Avatar niet hersteld: je profielavatar is inmiddels veranderd.'),409);
    $stmt=$db->prepare('DELETE FROM chas_backups WHERE user_id=?'); $stmt->bind_param('i',$uid); $stmt->execute(); $stmt->close();
    if (function_exists('redisUpdateUser')) { try { redisUpdateUser($uid); } catch (Throwable $e) {} }
    chas_json(array('ok'=>true,'message'=>'Oorspronkelijke avatar hersteld.'));
}
chas_json(array('ok'=>false,'error'=>'Onbekende actie.'),400);

function chas_render_svg($c) {
    $skin=$c['skin']; $hair=$c['hair']; $bg=$c['background']; $shirt=$c['shirt'];
    $rx=$c['face']==='angular'?57:($c['face']==='round'?66:61);
    $hairSvg='';
    if ($c['hairStyle']==='long') $hairSvg='<path d="M75 125 Q55 52 116 44 Q182 17 222 76 Q241 112 225 218 L205 218 L201 111 Q157 83 104 102 L91 212 L74 190Z" fill="'.$hair.'"/>';
    elseif ($c['hairStyle']==='curly') $hairSvg='<path d="M76 112 Q55 85 81 70 Q73 40 107 49 Q126 20 149 45 Q179 20 193 51 Q226 48 222 82 Q246 98 222 124 L204 103 Q160 78 104 100 L91 126Z" fill="'.$hair.'"/>';
    elseif ($c['hairStyle']==='fringe') $hairSvg='<path d="M76 119 Q62 48 131 43 Q201 38 219 102 L205 122 L193 84 Q149 67 104 94 L90 128Z" fill="'.$hair.'"/>';
    elseif ($c['hairStyle']==='bun') $hairSvg='<circle cx="190" cy="54" r="24" fill="'.$hair.'"/><path d="M76 119 Q62 48 131 43 Q201 38 219 102 L205 122 L193 84 Q149 67 104 94 L90 128Z" fill="'.$hair.'"/>';
    elseif ($c['hairStyle']!=='none') $hairSvg='<path d="M76 119 Q62 48 131 43 Q201 38 219 102 L205 122 L193 84 Q149 67 104 94 L90 128Z" fill="'.$hair.'"/>';
    $eyes=$c['eyes']==='happy'?'<path d="M124 144 q8 -8 16 0 M180 144 q8 -8 16 0" stroke="#292338" stroke-width="5" fill="none" stroke-linecap="round"/>':'<ellipse cx="132" cy="145" rx="5" ry="7" fill="#292338"/><ellipse cx="188" cy="145" rx="5" ry="7" fill="#292338"/>';
    $top='<path d="M75 310 L83 246 Q95 220 124 216 L160 242 L196 216 Q225 222 237 246 L245 310Z" fill="'.$shirt.'"/>';
    if ($c['outfit']==='formal') $top.='<path d="M121 216 L160 245 L146 310 L105 230Z M199 216 L160 245 L176 310 L214 230Z" fill="#fff" opacity=".85"/>';
    if ($c['outfit']==='hoodie') $top.='<path d="M119 220 Q116 190 160 191 Q204 190 201 220 L185 237 L135 237Z" fill="#fff" opacity=".18"/>';
    if ($c['outfit']==='jacket') $top.='<path d="M137 243 L151 310 M183 243 L169 310" stroke="#fff" stroke-width="5" opacity=".55"/>';
    $acc=''; if ($c['accessory']==='glasses') $acc='<g fill="none" stroke="#292338" stroke-width="5"><rect x="111" y="128" width="34" height="26" rx="8"/><rect x="175" y="128" width="34" height="26" rx="8"/><path d="M145 139 H175"/></g>';
    elseif ($c['accessory']==='cap') $acc='<path d="M83 93 Q102 39 163 48 Q204 52 217 91 L83 93Z" fill="#6956d8"/><path d="M78 94 Q154 84 225 100" stroke="#4c3cae" stroke-width="10" stroke-linecap="round"/>';
    elseif ($c['accessory']==='earrings') $acc='<circle cx="82" cy="160" r="6" fill="#f5c451"/><circle cx="238" cy="160" r="6" fill="#f5c451"/>';
    return '<svg xmlns="http://www.w3.org/2000/svg" width="320" height="320" viewBox="0 0 320 320"><rect width="320" height="320" rx="34" fill="'.$bg.'"/><circle cx="160" cy="150" r="118" fill="#fff" opacity=".18"/>'.$top.'<path d="M139 190 L139 226 Q160 245 181 226 L181 190Z" fill="'.$skin.'"/><ellipse cx="160" cy="135" rx="'.$rx.'" ry="77" fill="'.$skin.'"/>'.$hairSvg.$eyes.'<path d="M142 177 Q160 190 178 177" stroke="#8d4e51" stroke-width="4" fill="none" stroke-linecap="round"/>'.$acc.'</svg>';
}
