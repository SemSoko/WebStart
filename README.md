# Todo-App mit PHP, JS und MySQL

Dies ist eine einfache PHP-basierte To-Do-Listen-Anwendung mit\
JWT-Authentifizierung.

## Voraussetzungen
- PHP (über XAMPP enthalten): 8.2.12
- Composer: 2.8.8
- XAMPP: 8.2.12 (Apache + MySQL)

Abhängigkeiten (via Composer)

Nach dem Klonen/Download:\
composer install

Damit werden folgende Abhängigkeiten installiert:
- firebase/php-jwt: 6.11
- vlucas/phpdotenv: 5.6
- phpunit/phpunit: 11.5

## Projektstruktur

```
todolist/
| --- bootstrap/		# Initialisierung (DB, Autoloader, ... )
|		| --- init.php
|
| --- public/
|		| --- api/
|				| --- addUserTodo.php
|				| --- deleteUserTodo.php
|				| --- get_user_todos.php
|				| --- login.php
|				| --- register.php
|				| --- toggleUserTodoStatus.php
|				| --- user_info.php
|		| --- css/
|		| --- html/
|				| --- dashboard.php
|				| --- login.php
|				| --- register.php
|		| --- js/
|				| --- dashboard.php
|				| --- login.php
|				| --- logout.php
|				| --- register.php
|
| --- src/
|		| --- auth/
|			| --- auth.php
|		| --- core/
|			| --- db.php
|			| --- funktionen.php
|			| --- JwtHandler.php
|		| --- todo/
|			| --- todo.php
|
| --- tests/
|		| --- auth/
|				| --- CreateUserTest.php
|				| --- IsEmailRegisteredTest.php
|				| --- IsValidPasswordTest.php
|				| --- LoginUserTest.php
|				| --- ProcessLoginFormTest.php
|		| --- todo/
|				| --- AddTodoTest.php
|				| --- DeleteTodoTest.php
|				| --- GetTodosByUserTest.php
|				| --- ToggleTodoTest.php
|
| --- .env
| --- .env-example
| --- .gitignore
| --- composer.json
| --- composer.lock
| --- JWT-basiertes-Authentifizierungssystem.txt
| --- README.md
```

## Einrichtung

1.	Projekt klonen\
git clone https://github.com/SemSoko/todolist.git\
cd todolist

2.	Abhängigkeiten installieren\
composer install

3.	Umgebungsdatei konfigurieren\
.env im Projektverzeichnis erstellen (oder kopieren aus .env.example, falls\
vorhanden):

- DB_HOST=...
- DB_NAME=...
- DB_USER=...
- DB_PASS=...

- JWT_SECRET=...

4.	Datenbank importieren

Erstelle eine MySQL-Datenbank und importiere folgendes Schema:

```
--	Tabelle: users
create table users(
	id int AUTO_INCREMENT primary key,
	email varchar(255) unique not null,
	password varchar(255) not null,
	surname varchar(255) not null,
	first_name varchar(255) not null,
	created_at timestamp default current_timestamp
);

--	Tabelle: todos
create table todos(
	id int auto_increment primary key,
	user_id int not null,
	title varchar(255) not null,
	is_done boolean default false,
	created_at timestamp default current_timestamp,
	foreign key (user_id) references users(id) on delete cascade
);
```

5.	Anwendung starten (XAMPP)

-	Stelle sicher, dass Apache und MySQL über das XAMPP Control Panel gestartet\
	sind
-	Lege das Projektverzeichnis z. B. in '...\xampp\htdocs\todolist' ab.

Rufe anschließend im Browser auf:\
http://localhost/todolist/public
