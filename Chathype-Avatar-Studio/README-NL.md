# Chathype Avatar Studio — CodyChat 11

Een vernieuwde avatarbouwer voor CodyChat 11 met een premium studio-interface, live SVG-preview, categorieën, willekeurige avatar, opslaan en beheerinstellingen.

## Installatie
1. Download de map `Chathype-Avatar-Studio` uit deze repository.
2. Kopieer de map `addons/chathype_avatar_creator` naar de map `addons` van je CodyChat-installatie.
3. Open in je chat de addon-installatie/manager en activeer **Chathype Avatar Studio**.
4. Open de addon via `addons/chathype_avatar_creator/index.php` of koppel `files/avatar_studio.php` aan je bestaande CodyChat-addon-menu.
5. Beheerinstellingen staan in `admin.php` (alleen zichtbaar voor beheerders).

## Vereisten
- CodyChat 11 met de gebruikelijke `system/config_session.php`, `system/config_addons.php`, `system/settings.php` en `system/database.php`.
- PHP 7.4 of hoger; MySQL/MariaDB.
- De avatarbestandsmap van CodyChat moet schrijfbaar zijn door PHP.

## Belangrijk
De addon gebruikt de CodyChat-sessie en `boom_users.user_tumb`. Bij de eerste wijziging bewaart hij de oorspronkelijke avatar in `chathype_avatar_creator_backup`; bij herstel kan die avatar worden teruggezet. Test eerst op een back-up van je chat. Het project is niet door een live CodyChat-host/database uitgevoerd in deze omgeving, dus controleer de integratie op jouw installatie.
