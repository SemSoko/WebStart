# WebStart - Modularer Einstieg in moderne Webentwicklung

**WebStart** ist ein leichtgewichtiges, modulares Grundgeruest zur Entwicklung\
von Webanwendungen. Das Ziel ist, eine solide und flexible Grundlage zu\
schaffen, auf der sich Webapplikationen effizient und wartbar entwickeln\
lassen.

## Inhaltsverzeichnis

1. [Technologie-Stack](#1-technologie-stack)
2. [Projektstruktur](#2-projektstruktur)
3. [Composer ─ Abhaengigkeiten](#3-Composer--abhaengigkeiten)
4. [Docker-Konfiguration](#4-docker-konfiguration)
	- [4.1 Projektkontext & Basisverzeichnis](#41-projektkontext--basisverzeichnis)
	- [4.2 Dockerfile](#42-dockerfile)
	- [4.3 docker-compose.yml ─ Container-Orchestrierung](#43-docker-composeyml--container-orchestrierung)
5. [Umgebungsvariablen (.env)](#5-umgebungsvariablen-env)
6. [Projektstart & Nutzung](#6-projektstart--nutzung)
	- [6.1 Wichtiger Hinweis zur Erstinitialisierung](#61-wichtiger-hinweis-zur-erstinitialisierung)
7. [Datenbankstruktur](#7-datenbankstruktur)
8. [Tests](#8-tests)
9. [Lizenzen der verwendeten Technologien](#9-lizenzen-der-verwendeten-technologien)
10. [Hinweise zur Darstellung](#hinweise-zur-darstellung)

## 1. Technologie-Stack

Aktuell basiert **WebStart** auf folgenden Komponenten:

- **Apache**: 8.2.12 ─ WebServer
- **PHP**: 8.2.12
- **Composer**: 2.8.8 ─ zur Verwaltung von PHP-Abhaengigkeiten
- **MariaDB**: 10.11 ─ relationale Datenbank
- **phpMyAdmin**: 5.2.1
- **Docker Engine**: 27.2.0 (Docker ueber Docker Desktop unter Windows)
- **Docker Compose**: 2.29.2-desktop.2 - zur Container-Orchestrierung

## 2. Projektstruktur

```
WebStart/
├── init-sql/
│    └─── 001-init.sql
│
├── projekt/
│    ├─── bootstrap/                                # Initialisierung (DB, Autoloader, ... )
│    │	└─── init.php
│    │
│    ├─── public/                                   # Oeffentlich zugaenglicher Bereich (Frontend + API)
│    │    ├─── api/                                 # API-Endpunkte
│    │    │    ├─── addUserTodo.php
│    │    │    ├─── deleteUserTodo.php
│    │    │    ├─── get_user_todos.php
│    │    │    ├─── login.php
│    │    │    ├─── register.php
│    │    │    ├─── toggleUserTodoStatus.php
│    │    │    └─── user_info.php
│    │    │
│    │    ├─── css/
│    │    │
│    │    ├─── html/
│    │    │    ├─── dashboard.html
│    │    │    ├─── login.html
│    │    │    └─── register.html
│    │    │
│    │    ├─── js/                                  # JavaScript-Frontend-Logik (modular aufgebaut)
│    │    │    │                                    # Feature-spezifische Module fuer das Dashboard
│    │    │    ├─── dashboard/                      # Feature-Modul fuer dashboard.html
│    │    │    │    ├─── index.js                   # Einstiegpunkt des Dashboards
│    │    │    │    ├─── api/                       # Fuer dashboard spezifische API-Funktionen
│    │    │    │    ├─── dom/                       # Fuer Selektoren und DOM-Erzeugung fuer Dashboard
│    │    │    │    │    ├─── create.js
│    │    │    │    │    └─── selectors.js
│    │    │    │    ├─── events/                    # Alle Eventlistener, Eventhandler fuer Dashboard
│    │    │    │    │    └─── dashboardEvents.js
│    │    │    │    │
│    │    │    │    └─── render/                    # Darstellung und DOM-Aktualisierungen
│    │    │    │         └─── todoRenderer.js
│    │    │    │
│    │    │    │                                    # Wiederverwendbare, globale Module
│    │    │    ├─── shared/                         # Feature unabhaengige Module (projektweit nutzbar)
│    │    │    │    ├─── api/                       # API-Funktionalitaet
│    │    │    │    │    ├─── fetchWrapper.js
│    │    │    │    │    ├─── todo.js
│    │    │    │    │    └─── user.js
│    │    │    │    ├─── dom/                       # Helferfunktionen fuer das DOM
│    │    │    │    │    └─── elements.js
│    │    │    │    └─── utils/                     # Sonstige Hilfsfunktionen (z.B. Tokenverwaltung)
│    │    │    │         └─── token.js
│    │    │    │
│    │    │    │                                    # Weitere Feature-Einstiegspunkte (noch nicht modularisiert)
│    │    │    ├─── login.js
│    │    │    ├─── logout.js
│    │    │    └─── register.js
│    │    │
│    │    └─── index.php
│    │
│    ├─── src/
│    │    ├─── auth/
│    │    │	└─── auth.php
│    │    │
│    │    ├─── core/
│    │    │ ├─── db.php
│    │    │ ├─── funktionen.php
│    │    │ └─── JwtHandler.php
│    │    │
│    │    └─── todo/
│    │         └─── todo.php
│    │
│    ├─── tests/                                    # Unit-Tests (PHPUnit)
│    │    ├─── auth/
│    │    │    ├─── CreateUserTest.php
│    │    │    ├─── IsEmailRegisteredTest.php
│    │    │    ├─── IsValidPasswordTest.php
│    │    │    ├─── LoginUserTest.php
│    │    │    └─── ProcessLoginFormTest.php
│    │    │
│    │    └─── todo/	
│    │         ├─── AddTodoTest.php
│    │         ├─── DeleteTodoTest.php
│    │         ├─── GetTodosByUserTest.php
│    │         └─── ToggleTodoTest.php
│    │
│    ├─── composer.json
│    └─── composer.lock
│
├─── sql-dumps/
├─── .env-example
├─── .env
├─── docker-compose.yml
├─── Dockerfile
├─── .gitignore
├─── JWT-basiertes-Authentifizierungssystem.txt
└─── README.md
```

## 3. Composer ─ Abhaengigkeiten

Das Projekt verwendet [Composer](https://getcomposer.org/), um PHP-Bibliotheken\
zu verwalten. Folgende Pakete sind in der `composer.json` definiert:

- **firebase/php-jwt**: 6.11 ─ zur Erstellung und Validierung von JSON Web Tokens (JWT)
- **vlucas/phpdotenv**: 5.6 ─ zum Einlesen von Umgebungsvariablen aus `.env`-Dateien
- **phpunit/phpunit**: 11.5 ─ fuer Unit-Tests

*Hinweis*
Wenn Docker verwendet wird, werden die Abhaengigkeiten automatisch beim\
Container-Build installiert.

Der nachfolgende Befehl ist nur fuer (lokale) Ausfuehrungen ausserhalb von Docker relevant:
`composer install`
	
## 4. Docker-Konfiguration

### 4.1 Projektkontext & Basisverzeichnis

Die Anwendung wird im Container unter dem Pfad `/var/www/app` ausgefuehrt.\
Als Basis-Image wird: `php:8.2-apache` eingesetzt. Dieses setzt auf einem Debian-System auf.\
Neben dem Debian-System bringt es PHP und Apache vorinstalliert mit.

Im PHP-Code wird der Projektstamm dynamisch ueber folgende Konstruktion ermittelt:
```php
//	Basis-Pfad zum Projektverzeichnis (/var/www/app)
define('BASE_PATH', dirname(__DIR__));
```

### 4.2 Dockerfile

Das `Dockerfile` basiert auf `php:8.2-apache` und enthaelt:

- Kopieren des Projektverzeichnisses nach `/var/www/app`
- Apache-Konfiguration (Setzen des Servernamens auf `localhost`)
- Installation systemweiter Tools:
	- `zip`, `unzip`, `git`, `libzip-dev`
-	Installation benoetigter PHP-Erweiterungen:
	-	`zip`, `pdo`, `pdo_mysql`
- Verlinkung von `/public` als Webroot (fuer Apache)
- Installation der Composer-Abhaengigkeiten beim Build

### 4.3 docker-compose.yml ─ Container-Orchestrierung

Die Datei `docker-compose.yml` definiert die beteiligten Container und deren Zusammenspiel.\
Ueber diese koennen alle Services einfach verwaltet werden.

### Enthaltene Services:

- **apache-php** (`mini-php-apache-composer`)
	- baut das PHP-Apache-Image ueber `Dockerfile`
	- Portweiterleitung: `8080:80` (Apache)
	- verwendet das Projektverzeichnis (`./projekt`) als Volume
	- fuehrt `composer install` bei jedem Start aus und startet anschliessend den Apache-Prozess
	- verwendet Umgebungsvariablen aus `.env`
	- ist abhaengig vom Datenbankdienst `db` (`depends_on`)
	
- **db** (`mariadb`)
	- Verwendet das offizielle `mariadb:10.11` Image
	- Portweiterleitung: `3306:3306`
	- initialisiert beim ersten Start die Tabellen mit dem Skript aus `init-sql/*`
	- persitiert Daten ueber das Volume `db_data`
	- enthaelt zusaetzlich das gemountete Verzeichnis `sql-dumps/` fuer Backups

- **db-admin-tool** (`php-my-admin`)
	- Basiert auf `phpmyadmin:5.2.1`
	- Portweiterleitung: `8082:80`
	- verbindet sich automatisch mit dem MariaDB-Container (`PMA_HOST=db`)
	- Konfiguration ueber `.env` (Benutzername, Passwort, ...)

- **Verwendete Volumes:**
	Diese Volumes sorgen dafuer, dass Datenbankinhalte und Dumps dauerhaft gespeichert werden.
	- `db_data`
	- `sql_dumps` (DB-Backups)

## 5. Umgebungsvariablen (.env)

Die Datei `.env` enthaelt alle konfigurierbaren Umgebungsvariablen fuer:

- Anwendung
- Datenbank
- phpMyAdmin

Zur Erstellung der `.env`-Datei kann die Vorlage: `.env-example` verwendet werden:\
Die .env-Datei **nicht versionieren** ─ sie sollte in der `.gitignore` eintragen sein.

## 6. Projektstart & Nutzung

### Projekt herunterladen

`git clone https://github.com/SemSoko/WebStart.git`\
`cd WebStart`

### Erstellen der .env-Datei

Die Datei `.env` wird aus der Vorlage `.env-example` erzeugt:\
`cp .env-example .env`

Anschliessend die `.env`-Datei mit den entsprechenden Werten befuellen.

### Anwendung starten mit Docker

In das Projektverzeichnis wechseln:\
`cd <Pfad zum Projekt>/WebStart`

Container bauen und starten:\
`docker-compose up --build`

Fuer spaetere Starts genuegt:\
`docker-compose up`

Zum Stoppen aller Container:\
`docker-compose down`

Zum Stoppen und entfernen aller Container:\
`docker-compose down -v`

### Zugriff ueber den Browser

Nachdem die Anwendung erfolgreich per Docker gestartet wurde, kann auf folgendes per\
Browser zugegriffen werden:

- Webanwendung (Frontend & API): `http://localhost:8080`
- phpMyAdmin (Datenbankverwaltung): `http://localhost:8082`

### Datenbankinitialisierung

Beim ersten Start fuehrt der mariadb-Container automatisch das Skript `WebStart/init-sql/001-init.sql` aus.\
Dabei werden die benoetigten Tabellen (users, todos) erstellt ─ sofern die Datenbank noch nicht existiert.

Die Initialisierung erfolgt ueber den Standardpfad `/docker-entrypoint-initdb.d` im Container

### 6.1 Wichtiger Hinweis zur Erstinitialisierung

Die Einstellungen aus der `.env`-Datei (z. B. Benutzername, Passwort, Datenbankname) werden vom
`mariadb`-Container **nur beim allerersten Start** übernommen.

Docker speichert die Datenbankdaten in einem persistenten Volume (`db_data`). Das bedeutet:

- Wurde der Container bereits einmal gestartet, greift MariaDB **nicht erneut** auf die `.env`-Werte oder `init-sql`-Skripte zu.
- Änderungen an `.env` oder SQL-Skripten werden **ignoriert**, solange das Volume existiert.
- Um eine saubere Neuinitialisierung zu erzwingen, muss das Volume **explizit gelöscht** werden.

#### Datenbank vollständig zurücksetzen:

`docker compose down -v        # Container und Volumes löschen`
`docker compose up --build     # Projekt frisch starten + Initialisierung ausführen`

Dadurch werden die Inhalte aus der `.env`-Datei sowie das `Skript init-sql/001-init.sql` zuverlässig verarbeitet.

## 7. Datenbankstruktur

**Aktuell in Arbeit**
Geplant ist unter anderem eine visuelle Darstellung (ERM), sowie tabellarische Beschreibung\
der Datenbankrelationen.

## 8. Tests

Das Projekt verwendet [PHPUnit](https://phpunit.de/), um Komponenten zu testen.\
Die Tests befinden sich im Verzeichnis: `WebStart/projekt/tests`.

**Hinweis**
Die bestehende Teststruktur wird aktuell ueberarbeitet, da die Anwendung auf eine\
API-basierte Architektur umgestellt wurde.\
Zukuenftig sollen auch JavaScript-Komponenten im Frontend von den Tests abgedeckt werden.

## 9. Lizenzen der verwendeten Technologien

- PHP
	- Version: 8.2.12
	- Lizenz: PHP License
	- Quelle: https://www.php.net/license/
	
- Apache HTTP Server 
	- Version: 2.4.x
	- Lizenz: Apache-2.0
	- Quelle: https://www.apache.org/licenses/LICENSE-2.0
	
- MariaDB
	- Version: 10.11
	- Lizenz: GPLv2
	- Quelle: https://mariadb.com/legal/
	
- Docker
	- Version: 27.2.0
	- Lizenz: Apache-2.0 (teilweise)
	- Quelle: https://docs.docker.com/subscription/desktop-license/
	
- Docker Compose
	- Version: 2.29.2
	- Lizenz: Apache-2.0
	- Quelle: https://github.com/docker/compose/blob/main/LICENSE
	
- phpMyAdmin
	- Version: 5.2.1
	- Lizenz: GPLv2
	- Quelle: https://www.phpmyadmin.net/license/
	
- firebase/php-jwt
	- Version: 6.11
	- Lizenz: BSD-3-Clause
	- Quelle: https://github.com/firebase/php-jwt/blob/main/LICENSE
	
- vlucas/phpdotenv
	- Version: 5.6
	- Lizenz: BSD-3-Clause
	- Quelle: https://github.com/vlucas/phpdotenv/blob/master/LICENSE
	
- phpunit/phpunit
	- Version: 11.5
	- Lizenz: BSD-3-Clause
	- Quelle: https://github.com/sebastianbergmann/phpunit/blob/main/LICENSE

### Hinweise zur Darstellung

In dieser README werden Unicode-Zeichen wie `├`, `─`, `│`, `└` verwendet.\
Diese lassen sich unter Windows ueber die Zeichentabelle `charmap` einfuegen:

- Schriftart: `Courier New`
- In Suche: `Box`

[Zueruck zum Anfang](#WebStart---Modularer-einstieg-in-moderne-webentwicklung)
