<?php
require_once __DIR__ . '/bootstrap.php';
if (!chas_is_admin()) { http_response_code(403); exit('Alleen beheerders kunnen installeren.'); }
try { chas_install_schema(); echo 'Chathype Avatar Studio database is aangemaakt.'; } catch (Throwable $e) { http_response_code(500); echo 'Installatie mislukt. Controleer databaseverbinding en rechten.'; }
