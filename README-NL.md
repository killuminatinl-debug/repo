# Chathype Avatar Studio v3 — CodyChat 11

Een zelfstandige avatarbouwer met live preview, huidskleur, kapsels, kleding, accessoires, opslaan, herstellen en een beheerpaneel. Gericht op PHP 7.4+ en MySQL/MariaDB.

## Installatie in CodyChat

1. Maak een back-up van je chatbestanden en database.
2. Download deze map en plaats **de map `Chathype-Avatar-Studio-v3` ongewijzigd** in de hoofdmap van je CodyChat-installatie onder `addons/Chathype-Avatar-Studio-v3/`.
3. Activeer de addon via de CodyChat-addonmanager als jouw installatie een handmatige addonregistratie ondersteunt. De updater `updater/data.php` maakt de tabellen automatisch aan wanneer CodyChat deze updater uitvoert.
4. Koppel `files/entry.php` aan de addon/modal-route van CodyChat. Open `addon.php` alleen als losse test-/weergavepagina vanuit een ingelogde chat-sessie.
5. Controleer dat PHP schrijfrechten heeft op `addons/Chathype-Avatar-Studio-v3/generated/` (de map wordt bij het eerste opslaan aangemaakt).
6. Open de studio als speler, sla een avatar op en controleer dat `boom_users.user_tumb` wordt bijgewerkt. De addon schrijft het avatarpad naar het bestaande CodyChat-profiel en probeert daarna `redisUpdateUser()` aan te roepen als die functie bestaat.
7. Open `system/admin.php` via een ingelogde beheerderssessie voor instellingen.

## Belangrijk over profielweergave

De avatar wordt gekoppeld via `boom_users.user_tumb`, de avatarveldnaam die in CodyChat 11 wordt gebruikt. De uiteindelijke zichtbaarheid kan ook afhangen van jouw aangepaste profieltemplate, CDN/cache of de gebruikte afbeeldingsproxy. Deze repository is niet verbonden met jouw live database/server; daarom kan ik niet beweren dat jouw specifieke host al live is getest.

## Technische details

- PHP 7.4-syntaxis; MySQL/MariaDB en mysqli.
- CSRF-controle op bewerkingen, allowlist-validatie van avataropties en prepared statements.
- Uninstall is niet-destructief: spelerinstellingen en back-ups worden niet automatisch verwijderd.
- Als je de map hernoemt, moeten de asset/API-paden en de mapnaam in `system/api.php` gelijk worden aangepast.
