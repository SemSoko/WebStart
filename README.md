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
	- [4.4 JSDoc Docker-Setup](#44-jsdoc---dockerbasiertes-setup-fuer-die-javascript-dokumentation)
5. [Umgebungsvariablen (.env)](#5-umgebungsvariablen-env)
6. [Projektstart & Nutzung](#6-projektstart--nutzung)
	- [6.1 Wichtiger Hinweis zur Erstinitialisierung](#61-wichtiger-hinweis-zur-erstinitialisierung)
	- [6.2 JavaScript-Dokumentation (JSDoc) erzeugen und anzeigen](#62-javascript-dokumentation-jsdoc-erzeugen-und-anzeigen)
	- [6.3 PHP-Dokumentation (PHPDoc) erzeugen und anzeigen](#63-php-dokumentation-phpdoc-erzeugen-und-anzeigen)
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
- **Docker**: 27.5.1 (getestet unter Ubuntu 24.04)
- **Docker Compose**: 2.36.2 (CLI integriert, `docker compose`)

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
├── docker/
│    ├─── documentation/
│    │    ├─── jsdoc/
│    │    │    ├─── docker-compose.yml
│    │    │    ├─── docker-compose.tooling.yml
│    │    │    ├─── jsdoc-runner.dockerfile
│    │    │    ├─── jsdoc-web.dockerfile
│    │    │    ├─── out/
│    │    │    └─── .jsdoc-tooling/
│    │    │         └─── jsdoc.json
│    │    │
│    │    └─── phpdoc/
│    │         ├─── docker-compose.yml
│    │         ├─── phpdoc.dist.xml
│    │         └─── out/
│    │
│    └─── app/
│         ├─── docker-compose.yml
│         └─── Dockerfile
│
├─── sql-dumps/
├─── .env-example
├─── .env
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

### 4.4 JSDoc - Dockerbasiertes Setup fuer die JavaScript-Dokumentation

Neben dem produktiven Docker-Setup existiert eine separate, containerisierte Umgebung zur\
Erzeugung und Bereitstellung der JavaScript-Dokumentation (JSDoc).

Es werden zwei Docker-Compose-Dateien verwendet:

#### `docker-compose.tooling.yml`
Initialisiert ein Node.js-Projekt im Verzeichnis `.jsdoc-tooling`, das als Basis\
fuer das JSDoc-Setup dient.
`docker compose -f docker-compose.tooling.yml up --build`

- Fuehrt npm init -y und npm install --save-dev jsdoc aus
- Legt package.json und package-lock.json in .jsdoc-tooling/ ab
- Container wird nach der Initialisierung automatisch gestoppt
- Muss beim allerersten Setup ausgeführt werden
- Danach nur noch noetig, wenn sich die Tooling-Abhaengigkeiten aendern (z. B. Update von JSDoc)

#### `docker-compose.yml`
Erzeugt die Dokumentation und stellt sie im Browser bereit:\
`docker compose -f docker-compose.yml up --build`

- Startet `jsdoc-runner`, um Dokumentation aus `WebStart/projekt/public/js/` zu generieren
- Speichert die generierte Ausgabe im Verzeichnis `WebStart/docker/documentation/jsdoc/out/`
- Startet `jsdoc-docs-web`, einen Apache-Webserver zur Auslieferung der Doku

Zugriff im Browser:\
JSDoc-Viewer: [http://localhost:8081](http://localhost:8081)

- JSDoc-Konfiguration:
`WebStart/docker/documentation/jsdoc/.jsdoc-tooling/jsdoc.json`
- Generierte Dokumentation:
`WebStart/docker/documentation/jsdoc/out/`

*Hinweis*\
Das gesamte JSDoc-Setup ist vollständig vom produktiven Projekt entkoppelt und kann parallel\
oder separat ausgeführt werden. Änderungen am JS-Code in `WebStart/projekt/public/js/` werden\
bei jedem Build automatisch neu dokumentiert.

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

Das docker-compose.yml fuer den produktiven Anwendungscode befindet sich unter:\
`WebStart/docker/app/docker-compose.yml`

Im Projekt-Root (WebStart/) folgenden Befehl ausfuehren:\
`cd <Pfad zum Projekt>/WebStart`\
`docker compose -f docker/app/docker-compose.yml up --build`

Fuer spaetere Starts genuegt:\
`docker compose -f docker/app/docker-compose.yml up`

Container stoppen:\
`docker compose -f docker/app/docker-compose.yml down`

Zum Stoppen und entfernen aller Container (z.B. Datenbank-Volume-loeschen):\
`docker compose -f docker/app/docker-compose.yml down -v`

### Zugriff ueber den Browser

Nachdem die Anwendung erfolgreich per Docker Compose gestartet wurde, kann wie folgt\
per Browser auf das Projekt zugegriffen werden:

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

### 6.2 JavaScript-Dokumentation (JSDoc) erzeugen und anzeigen

Die JavaScript-Dokumentation wird in einem separaten, dockerbasierten Setup erzeugt.\
Dies ist **unabhängig von der Hauptanwendung**.

1. Tooling initialisieren (nur beim ersten Mal oder bei Änderungen an Abhängigkeiten)
`docker compose -f docker-compose.tooling.yml up --build`\
Dies erzeugt im Verzeichnis .jsdoc-tooling/ die Dateien package.json und package-lock.json.

2. JSDoc-Dokumentation generieren und anzeigen\
`docker compose -f docker-compose.yml up --build`

- Generiert automatisch die Dokumentation aus dem Verzeichnis `projekt/public/js/`
- Speichert die Ausgabe im Ordner `out/`
- Stellt die Dokumentation über einen Apache-Server im Browser zur Verfügung

Aufruf im Browser:\
[http://localhost:8081](http://localhost:8081)

### 6.3 PHP-Dokumentation (PHPDoc) erzeugen und anzeigen

Zur Dokumentation des PHP-Codes wird [phpDocumentor](https://www.phpdoc.org/) verwendet.\
Die Generierung erfolgt über ein separates Docker-Setup, das unabhängig von der Hauptanwendung\
betrieben wird.

#### Dokumentation generieren

In folgendes Verzeichnis wechseln:\
`WebStart/docker/documentation/phpdoc/`

Anschliessend zuerst den PHPDoc-Service der `docker-compose.yml` aufrufen:\
`docker compose run --rm phpdoc`

Dies analysiert den PHP-Code gemäß Konfiguration und erzeugt die HTML-Dokumentation im\
Unterordner out/.

#### Dokumentation im Browser anzeigen

In einem zweiten Schritt kann die nun vorhandene Dokumentation, per Apache-Container
gestartet werden:\
`docker compose up apache`

Aufruf im Browser:\
[http://localhost:8080](http://localhost:8080)

**Hinweis**\
Der Ordner `out/` wird per Volume in den Apache-Container eingebunden und dient dort als\
Webroot.

#### PHPDoc - Konfigurationsdatei

Die Konfigurationsdatei `phpdoc.dist.xml` befindet sich im Verzeichnis:\
`WebStart/docker/documentation/phpdoc/phpdoc.dist.xml`

Diese enthält u.a.:
- Ausgabeordner: `out/`
- Cache-Verzeichnis: `cache/`
- Eingelesene Quellverzeichnisse:
 1. `projekt/src/auth`
 2. `projekt/src/core`
 3. `projekt/src/todo`
 4. `projekt/public/api`

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
