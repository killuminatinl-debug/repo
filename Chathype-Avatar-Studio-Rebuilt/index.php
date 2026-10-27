<?php
require_once __DIR__ . '/system/bootstrap.php';
chas_require_user();
chas_install_schema();
if (!chas_enabled()) { http_response_code(403); exit('Avatar Studio is uitgeschakeld.'); }
$csrf = chas_csrf();
?><!doctype html>
<html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Chathype Avatar Studio</title><link rel="stylesheet" href="assets/studio.css"></head>
<body><main class="studio" id="studio" data-csrf="<?php echo chas_e($csrf); ?>">
<header><div class="mark">✦</div><div><small>CHATHYPE · CREATOR LAB</small><h1>Avatar Studio</h1><p>Maak een avatar die bij jou past.</p></div></header>
<div class="layout"><section class="preview"><div class="label">LIVE PREVIEW</div><div id="avatar-preview" aria-label="Avatarvoorbeeld"></div><div class="actions"><button id="random" type="button" class="secondary">Verras me</button><button id="save" type="button">Avatar opslaan</button></div><button id="restore" type="button" class="link">Oorspronkelijke avatar herstellen</button><p id="status" role="status" aria-live="polite"></p></section>
<section class="controls"><nav><button type="button" data-tab="face" class="active">Gezicht</button><button type="button" data-tab="hair">Haar</button><button type="button" data-tab="clothes">Kleding</button><button type="button" data-tab="extras">Details</button></nav>
<div class="group" data-group="face"><h2>Huidskleur</h2><div class="swatches" data-key="skin"></div><h2>Gezichtsvorm</h2><div class="choices" data-key="face"></div><h2>Ogen</h2><div class="choices" data-key="eyes"></div></div>
<div class="group hidden" data-group="hair"><h2>Haarkleur</h2><div class="swatches" data-key="hair"></div><h2>Kapsel</h2><div class="choices" data-key="hairStyle"></div></div>
<div class="group hidden" data-group="clothes"><h2>Kledingkleur</h2><div class="swatches" data-key="shirt"></div><h2>Outfit</h2><div class="choices" data-key="outfit"></div></div>
<div class="group hidden" data-group="extras"><h2>Achtergrond</h2><div class="swatches" data-key="background"></div><h2>Accessoire</h2><div class="choices" data-key="accessory"></div></div>
</section></div></main><script src="assets/studio.js" defer></script></body></html>