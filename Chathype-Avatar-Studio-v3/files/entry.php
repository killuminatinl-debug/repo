<?php
/*
 * Include this file from CodyChat's addon/modal loader.
 * If your build uses a different loader, map the addon window to this entry.
 */
require_once dirname(__DIR__) . '/system/bootstrap.php';
avatarV3RequireUser();
$addonBase = 'addons/Chathype-Avatar-Studio-v3';
?>
<link rel="stylesheet" href="<?php echo avatarV3Escape($addonBase); ?>/assets/avatar-studio.css">
<div class="chav3" id="chav3" data-api="<?php echo avatarV3Escape($addonBase); ?>/system/api.php" data-csrf="<?php echo avatarV3Escape(avatarV3Csrf()); ?>">
<header class="chav3-header"><div class="chav3-brandmark">✦</div><div><div class="chav3-eyebrow">CHATHYPE · CREATOR LAB</div><h2>Avatar Studio</h2><p>Ontwerp een look die helemaal bij jou past.</p></div><div class="chav3-status">● Live preview</div></header>
<div class="chav3-layout"><section class="chav3-preview-panel"><div class="chav3-preview-top"><span>JOUW AVATAR</span><span>● LIVE</span></div><div class="chav3-canvas-wrap"><div class="chav3-orbit"></div><div id="chav3-preview" class="chav3-preview"></div></div><div class="chav3-preview-caption"><strong>Jouw nieuwe look</strong><span>Uniek. Helemaal jij.</span></div><div class="chav3-preview-actions"><button type="button" class="chav3-btn chav3-btn-soft" id="chav3-random">↻ Verrass me</button><button type="button" class="chav3-btn chav3-btn-dark" id="chav3-save">Avatar opslaan →</button></div><p id="chav3-status" class="chav3-feedback" role="status" aria-live="polite"></p></section>
<section class="chav3-editor"><nav class="chav3-tabs"><button class="active" data-tab="face" type="button">☺<span>Gezicht</span></button><button data-tab="hair" type="button">✦<span>Haar</span></button><button data-tab="outfit" type="button">♧<span>Kleding</span></button><button data-tab="details" type="button">✧<span>Details</span></button></nav>
<div class="chav3-section active" data-section="face"><div class="chav3-field"><h3>Huidskleur</h3><p>Kies jouw tint</p><div class="chav3-swatches" data-field="skin"></div></div><div class="chav3-field"><h3>Gezichtsvorm</h3><p>De basis van je look</p><div class="chav3-options" data-field="face"></div></div><div class="chav3-field"><h3>Ogen</h3><p>Geef je avatar karakter</p><div class="chav3-options" data-field="eyes"></div></div></div>
<div class="chav3-section" data-section="hair"><div class="chav3-field"><h3>Haarkleur</h3><p>Van natuurlijk tot opvallend</p><div class="chav3-swatches" data-field="hair"></div></div><div class="chav3-field"><h3>Kapsel</h3><p>Kies je stijl</p><div class="chav3-options" data-field="hairStyle"></div></div></div>
<div class="chav3-section" data-section="outfit"><div class="chav3-field"><h3>Kledingkleur</h3><p>Maak het eigen</p><div class="chav3-swatches" data-field="shirt"></div></div><div class="chav3-field"><h3>Outfit</h3><p>Van casual tot chic</p><div class="chav3-options" data-field="outfit"></div></div></div>
<div class="chav3-section" data-section="details"><div class="chav3-field"><h3>Achtergrond</h3><p>Kies een sfeer</p><div class="chav3-swatches" data-field="background"></div></div><div class="chav3-field"><h3>Accessoire</h3><p>De finishing touch</p><div class="chav3-options" data-field="accessory"></div></div><button type="button" id="chav3-restore" class="chav3-btn chav3-btn-soft chav3-full">Herstel oorspronkelijke avatar</button></div>
</section></div></div>
<script src="<?php echo avatarV3Escape($addonBase); ?>/assets/avatar-studio.js" defer></script>
