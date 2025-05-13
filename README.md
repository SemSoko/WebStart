# Todo-App mit PHP, JS und MySQL

Dies ist eine einfache PHP-basierte To-Do-Listen-Anwendung mit<br \>
JWT-Authentifizierung.

## Voraussetzungen<br \>
- PHP (über XAMPP enthalten): 8.2.12<br \>
- Composer: 2.8.8<br \>
- XAMPP: 8.2.12 (Apache + MySQL)

Abhängigkeiten (via Composer)<br \>

Nach dem Klonen/Download:<br \>
composer install

Damit werden folgende Abhängigkeiten installiert:<br \>
- firebase/php-jwt: 6.11<br \>
- vlucas/phpdotenv: 5.6<br \>
- phpunit/phpunit: 11.5<br \>

## Projektstruktur

todolist/<br \>
| --- bootstrap/<br \>
|<br \>
| --- js/<br \>
|<br \>
| --- public/<br \>
|<br \>
| --- src/<br \>
|		| --- Auth/<br \>
|		| --- Core/<br \>
|		| --- Todo/<br \>
|<br \>
| --- tests/<br \>
|		| --- Auth/<br \>
|		| --- Todo/<br \>
| --- .env<br \>
| --- .env-example<br \>
| --- .gitignore<br \>
| --- composer.json<br \>
| --- composer.lock<br \>
| --- README.md<br \>

## Einrichtung

1.	Projekt klonen<br \>
git clone https://github.com/SemSoko/todolist.git<br \>
cd todolist

2.	Abhängigkeiten installieren<br \>
composer install

3.	Umgebungsdatei konfigurieren<br \>
.env im Projektverzeichnis erstellen (oder kopieren aus .env.example, falls<br \>
vorhanden):

- DB_HOST=...<br \>
- DB_NAME=...<br \>
- DB_USER=...<br \>
- DB_PASS=...<br \>

- JWT_SECRET=...

4.	Datenbank importieren<br \>

Erstelle eine MySQL-Datenbank und importiere folgendes Schema:

--	Tabelle: users<br \>
create table users(<br \>
	id int AUTO_INCREMENT primary key,<br \>
	email varchar(255) unique not null,<br \>
	password varchar(255) not null,<br \>
	created_at timestamp default current_timestamp<br \>
);

--	Tabelle: todos<br \>
create table todos(<br \>
	id int auto_increment primary key,<br \>
	user_id int not null,<br \>
	title varchar(255) not null,<br \>
	is_done boolean default false,<br \>
	created_at timestamp default current_timestamp,<br \>
	foreign key (user_id) references users(id) on delete cascade<br \>
);

5.	Anwendung starten (XAMPP)

-	Stelle sicher, dass Apache und MySQL über das XAMPP Control Panel gestartet<br \>
	sind<br \>
-	Lege das Projektverzeichnis z. B. in '...\xampp\htdocs\todolist' ab.

Rufe anschließend im Browser auf:<br \>
http://localhost/todolist/public
