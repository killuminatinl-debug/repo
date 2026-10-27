<?php
$load_addons = 'chathype_avatar_creator';
require_once __DIR__ . '/../../system/config_session.php';
require_once __DIR__ . '/system/settings.php';
$allowed = function_exists('canManageAddons') ? canManageAddons() : (function_exists('isAdmin') ? isAdmin() : (isset($data['user_rank']) && (int)$data['user_rank'] >= 8));
if (!$allowed) { echo '<div class="error_box">Je hebt geen rechten om deze instellingen te bekijken.</div>'; return; }
global $mysqli;
$enabled = chathypeAvatarStudioEnabled();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chas_save_settings'])) {
    $newValue = isset($_POST['chas_enabled']) && $_POST['chas_enabled'] === '1' ? '1' : '0';
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $stmt = $mysqli->prepare("UPDATE boom_addons SET custom1 = ? WHERE addons = 'chathype_avatar_creator'");
        if ($stmt) {
            $stmt->bind_param('s', $newValue);
            $ok = $stmt->execute();
            $stmt->close();
            $enabled = $newValue === '1';
            $msg = $ok ? 'Instellingen opgeslagen.' : 'Opslaan is mislukt.';
        } else $msg = 'Databasefout bij opslaan.';
    } else $msg = 'Databaseverbinding ontbreekt.';
}
?>
<div class="chas-admin">
<style>
.chas-admin{font-family:Inter,system-ui,sans-serif;max-width:920px;margin:18px auto;color:#26243d}.chas-admin *{box-sizing:border-box}.cha-hero{padding:30px;border-radius:22px;color:#fff;background:radial-gradient(ellipse at 90% 0%,#b7a6ff 0,transparent 34%),linear-gradient(120deg,#342b75,#6c57d9 65%,#8b72ed);position:relative;overflow:hidden}.cha-hero:after{content:'✦';position:absolute;right:42px;top:5px;font-size:130px;color:#ffffff13}.cha-kicker{font-size:9px;letter-spacing:2px;font-weight:850;color:#d9d2ff}.cha-hero h2{font-size:28px;letter-spacing:-1px;margin:8px 0}.cha-hero p{font-size:12px;color:#e3ddff;margin:0;max-width:460px;line-height:1.7}.cha-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:14px;margin-top:15px}.cha-card{background:#fff;border:1px solid #ebe9f5;border-radius:17px;padding:22px;box-shadow:0 9px 28px #30235d08}.cha-card h3{font-size:15px;margin:0 0 7px}.cha-card p{font-size:11px;color:#88869e;line-height:1.65;margin:0}.cha-toggle{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:17px 0;border-bottom:1px solid #f0eef7}.cha-toggle:last-of-type{border-bottom:0}.cha-toggle b{display:block;font-size:12px}.cha-toggle small{display:block;color:#8b899f;font-size:10px;margin-top:5px}.cha-switch{position:relative;width:48px;height:27px;flex:0 0 48px}.cha-switch input{opacity:0;width:1px;height:1px}.cha-switch span{position:absolute;inset:0;border-radius:30px;background:#d8d7e4;transition:.2s;cursor:pointer}.cha-switch span:before{content:'';position:absolute;width:19px;height:19px;left:4px;top:4px;border-radius:50%;background:#fff;box-shadow:0 2px 5px #0002;transition:.2s}.cha-switch input:checked+span{background:#6957df}.cha-switch input:checked+span:before{transform:translateX(21px)}.cha-save{margin-top:15px;border:0;border-radius:10px;padding:12px 17px;background:linear-gradient(110deg,#7965ef,#5948d1);color:#fff;font-weight:800;font-size:11px;cursor:pointer}.cha-status{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:800;padding:8px 11px;border-radius:9px;background:<?php echo $enabled?'#e6faf3':'#fff0f2'; ?>;color:<?php echo $enabled?'#168965':'#c43c5b'; ?>}.cha-status i{width:7px;height:7px;border-radius:50%;background:currentColor}.cha-stat{padding:13px 0;border-bottom:1px solid #f0eef7;display:flex;justify-content:space-between;gap:10px;font-size:11px}.cha-stat:last-child{border-bottom:0}.cha-stat span{color:#88869e}.cha-stat b{font-weight:800}.cha-msg{margin-top:12px;color:#168965;font-size:11px}.cha-admin a{color:#6252cf}@media(max-width:620px){.cha-grid{grid-template-columns:1fr}.cha-hero{padding:23px}.cha-hero h2{font-size:23px}}
</style>
<section class="cha-hero"><div class="cha-kicker">CHATHYPE CREATIVE LAB · ADMIN</div><h2>Avatar Studio beheer</h2><p>Beheer de avatarbouwer van je chat vanuit één overzichtelijk paneel. De instellingen worden direct opgeslagen in CodyChat.</p></section>
<form method="post">
<div class="cha-grid">
<section class="cha-card"><h3>Algemene instellingen</h3><p>Stel in of leden de Avatar Studio mogen gebruiken.</p>
<div class="cha-toggle"><div><b>Avatar Studio inschakelen</b><small>Toont de avatarbouwer aan ingelogde leden.</small></div><label class="cha-switch"><input type="checkbox" name="chas_enabled" value="1" <?php echo $enabled?'checked':''; ?>><span></span></label></div>
<button class="cha-save" type="submit" name="chas_save_settings" value="1">Instellingen opslaan ↗</button>
<?php if ($msg !== '') echo '<div class="cha-msg">'.htmlspecialchars($msg, ENT_QUOTES, 'UTF-8').'</div>'; ?>
</section>
<section class="cha-card"><h3>Addon status</h3><p>Huidige status en compatibiliteit.</p><div style="margin:15px 0"><span class="cha-status"><i></i><?php echo $enabled?'Actief voor leden':'Uitgeschakeld'; ?></span></div><div class="cha-stat"><span>Addon</span><b>Chathype Avatar Studio</b></div><div class="cha-stat"><span>Platform</span><b>CodyChat 11</b></div><div class="cha-stat"><span>PHP</span><b>7.4+</b></div><div class="cha-stat"><span>Opslag</span><b>MySQL / MariaDB</b></div></section>
</div></form></div>
