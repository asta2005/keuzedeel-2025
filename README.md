Keuzedeel - Slim + Twig demo
============================

Dit project is opgezet als een werkende demo met:
- Slim 4 (routes), Twig (templates)
- SQLite DB in data/database.sqlite
- Eenvoudige admin interface om projecten/vacatures toe te voegen (foto upload)
- Vooraf toegevoegde voorbeeldprojecten met gebouw-foto's (SVGs) in public/images

Belangrijke setup stappen (lokaal)
----------------------------------
1) Pak deze map uit in je keuzedeel map of kopieer de bestanden.
2) Voer in de projectroot uit:
   composer install
   (zorg dat composer is geïnstalleerd op jouw systeem)
3) Start de ontwikkelserver (optioneel):
   php -S localhost:8080 -t public
4) Open in je browser: http://localhost:8080/
   Admin login: ga naar /admin/login en gebruik wachtwoord: adminpass
   Na login kan je nieuwe projecten toevoegen (upload van foto's wordt opgeslagen in public/uploads).

Security
--------
- Dit is een demo. De admin-auth is een simpele sessie met een hardcoded wachtwoord 'adminpass'.
  Verander dit en voeg echte gebruikersauthenticatie toe voor productie.
- Controleer uploads en file-permissies bij deployment.
