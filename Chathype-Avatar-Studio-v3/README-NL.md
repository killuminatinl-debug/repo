# Chathype Avatar Studio v3

Volledig nieuwe implementatie in een aparte map; geen code uit de eerdere avatar-addon hergebruikt.

## Bestanden
- addon.php: spelersinterface voor CodyChat-addonvenster
- system/bootstrap.php: database-/sessie-aansluiting en beveiliging
- system/schema.sql: databaseschema
- system/api.php: JSON-acties met CSRF en server-side validatie
- system/install.php: tabellen installeren
- system/admin.php: beheerinterface
- assets/avatar-studio.css en assets/avatar-studio.js: eigen interface

## Installatie
1. Maak eerst een back-up van chat en database.
2. Plaats deze map in de projectmap.
3. Controleer in system/bootstrap.php de CodyChat-bootstrap-paden.
4. Voer system/install.php één keer uit als beheerder en blokkeer/verwijder daarna dat installatiebestand.
5. Voeg addon.php toe aan de modal/route van CodyChat.
6. Controleer schrijfrechten voor avatarbestanden als je later bestandsuitvoer toevoegt.

CodyChat-installaties verschillen door aangepaste paden en loaders. De code kan daarom niet eerlijk als live-getest op jouw chat worden aangemerkt zonder toegang tot jouw installatie en database.
