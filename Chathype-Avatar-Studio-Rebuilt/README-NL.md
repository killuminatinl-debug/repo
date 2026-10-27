# Chathype Avatar Studio — opnieuw opgebouwd

Addon voor CodyChat 11, bedoeld voor PHP 7.4+ en MySQL/MariaDB. Deze map is een zelfstandige nieuwe implementatie; de bestaande addon blijft onaangeraakt.

## Installatie
1. Plaats de map `Chathype-Avatar-Studio-Rebuilt` in `addons/` van je chat.
2. Controleer dat de chatdatabase en sessie door CodyChat worden geladen.
3. Open `index.php` vanuit een ingelogde CodyChat-sessie.
4. Open `admin.php` alleen via de beveiligde beheerder-sessie.

## Wat deze versie doet
- Live avatarpreview met kleur-, haar-, outfit- en accessoirekeuzes.
- Server-side validatie en CSRF-bescherming.
- Persoonlijke instellingen en veilige backup van de oorspronkelijke avatar.
- Herstel alleen als de actuele avatar nog de door deze addon ingestelde avatar is.
- Tabellen worden aangemaakt via `system/install.php`; de uninstall verwijdert geen gebruikersdata.
- PHP 7.4-syntaxis; geen Node.js of cronjob.

## Belangrijke compatibiliteitsnoot
CodyChat-installaties kunnen verschillen in sessievariabelen, rangcontrole, profiel-HTML en avatarbestandsmap. De addon gebruikt `system/config_session.php` en `$data['user_id']` waar beschikbaar, en controleert de gebruiker ook via de database. Test de beheerderstoegang en profielweergave in je exacte CodyChat-build voordat je dit op een live chat activeert. Deze omgeving kan jouw XAMPP-database niet uitvoeren; daarom wordt geen live-testclaim gedaan.
