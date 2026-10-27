<?php
/* Veilig standaardgedrag: laat profiel- en backupdata staan. Verwijder nooit tabellen automatisch. */
require_once __DIR__ . '/bootstrap.php';
if (!chas_is_admin()) { http_response_code(403); exit('Geen toegang.'); }
echo 'De addon is uitgeschakeld via beheer. Back-ups en gebruikersgegevens zijn bewust behouden.';
