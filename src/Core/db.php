<?php
	//	Sicherstellen, dass die .env geladen ist
	require_once __DIR__ . '/bootstrap/init.php';

	class Database {
		private static ?PDO $pdo = null;
		private static ?PDO $testConnection = null;
		
		//	Für die PHPUnit-Tests
		public static function setTestConnection(?PDO $pdo): void{
			self::$testConnection = $pdo;
		}
		
		//	Verhindert direkte Instanziierung
		private function __construct() {}
		
		//	Holt die PDO-Verbindung, wenn sie noch nicht existiert
		public static function getConnection(): PDO {
			
			//	1.	Immer zuerst prüfen: Gibt es eine Testverbindung?
			if(self::$testConnection !== null){
				return self::$testConnection;
			}
			
			//	2.	Wenn echte Verbindung noch nicht aufgebaut wurde: jetzt erstellen
			if(self::$pdo === null){
				//	Hier die Verbindungsdaten definieren
				$host = $_ENV['DB_HOST'];
				$db = $_ENV['DB_NAME'];
				$user = $_ENV['DB_USER'];
				$pass = $_ENV['DB_PASS'];
				$charset = $_ENV['DB_CHARSET'];
				$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
				$options = [
					PDO::ATTR_ERRMODE				=>	PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE	=>	PDO::FETCH_ASSOC
				];
				
				try{
					self::$pdo = new PDO($dsn, $user, $pass, $options);
				}catch(\PDOException $e){
					exit('Verbindungsfehler: '.$e->getMessage());
				}
			}
			
			return self::$pdo;
		}
	}
?>

<?php
	/*
		function getPDO(): PDO {
		$host = 'localhost';
		$db = 'todoliste';
		$user = 'root';
		$pass = '';
		$charset = 'utf8mb4';
	
	
		$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
	
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		];
	
		try{
			return new PDO($dsn, $user, $pass, $options);
		}catch(\PDOException $e){
			exit('Verbindungsfehler: '.$e->getMessage());
		}
	}
	
		1.	Was ist der DSN in new PDO(...)?
		DSN steht für Data Source Name. Es ist ein String, der nur die
		Verbindungsdetails zur Datenbank enthält – aber ohne Benutzername und
		Passwort.
		
		Beispiel:
		$dsn = "mysql:host=localhost;dbname=todoliste;charset=utf8mb4";
		
		Dieser Teil erklärt:

mysql: → Wir verwenden den MySQL-Treiber

host=localhost → Die Datenbank ist auf dem gleichen Rechner

dbname=todoliste → Der Name der Datenbank

charset=utf8mb4 → Zeichenkodierung (auch für Emojis geeignet)

💡 Der DSN sagt PDO, wie und wohin es sich verbinden soll – aber nicht mit welchem Benutzer oder Passwort. Das kommt separat.

2. Was macht new PDO($dsn, $user, $pass, $options)?
Das ist die Kernzeile, die eine neue Datenbankverbindung erstellt:

php
Kopieren
Bearbeiten
return new PDO($dsn, $user, $pass, $options);
Das passiert dabei Schritt für Schritt:

new PDO(...) erstellt eine Verbindung zur Datenbank.

$dsn gibt an, welcher Datenbanktyp (MySQL), wo (host), welche DB (Name), welcher Zeichensatz.

$user → Der Benutzername (z. B. root)

$pass → Das Passwort für den Benutzer

$options → Zusätzliche Konfigurationen (z. B. Fehlerbehandlung)

▶️ Rückgabe: Ein PDO-Objekt, das du dann verwenden kannst, um Abfragen auszuführen (mit prepare, execute, fetch, usw.).

3. Was bedeutet „CLI-Skripten“?
CLI steht für Command Line Interface, also die Kommandozeile (z. B. Terminal, PowerShell, Bash).

CLI-Skripten in PHP heißt:

Du führst PHP-Skripte nicht über den Browser, sondern direkt über die Kommandozeile aus.

Beispiel:

bash
Kopieren
Bearbeiten
php mein_script.php
💡 CLI wird oft genutzt für:

Tests

Cronjobs (automatisierte Aufgaben)

Datenmigrationen

Admin-Tools

Wenn du z. B. ein CLI-Tool schreibst, das Daten in die Datenbank lädt, nutzt du dort auch getPDO(), aber ohne jegliches HTML.

1. DSN ist nur ein String zur Übersicht
Ja, der DSN ist ein reiner Verbindungs-String – du könntest ihn auch direkt in den Konstruktor schreiben, z. B.:

php
Kopieren
Bearbeiten
$pdo = new PDO("mysql:host=localhost;dbname=todoliste;charset=utf8mb4", "root", "", $options);
Aber wir lagern ihn vorher in eine Variable ($dsn) aus, um es übersichtlicher, flexibler und wartbarer zu machen. Wenn du z. B. später mehrere Datenbanken nutzt oder Umgebungen wechselst (Entwicklung vs. Produktion), brauchst du nur den DSN an einer Stelle zu ändern.

✅ 2. new PDO(...) stellt die Verbindung her und kapselt sie im Objekt
Ganz richtig:

php
Kopieren
Bearbeiten
$pdo = new PDO(...);
stellt sofort die Verbindung zur Datenbank her (oder wirft eine Exception bei Fehler).

erstellt dabei ein PDO-Objekt, das alle Funktionen für Datenbankoperationen enthält.

Das Objekt kapselt die Verbindung intern: Du kannst über Methoden wie prepare(), query(), fetch(), etc. auf die DB zugreifen.

➡️ Das PDO-Objekt hält die Verbindung aktiv, bis:

das Skript endet

du $pdo = null; explizit setzt (damit wird die Verbindung geschlossen)

oder es vom Garbage Collector bereinigt wird

Kurz gesagt:
✅ DSN = Konfigurations-String
✅ new PDO(...) = Verbindung + Objekt mit DB-Methoden
✅ Das Objekt ist die zentrale Schnittstelle zur Datenbank
	*/
?>

<?php
	/*
	$host = 'localhost';
	$db = 'todoliste';
	$user = 'root';
	$pass = '';
	$charset = 'utf8mb4';
	
	
		dsn = Data Source Name
		mysql: → Treiber (du verbindest dich mit MySQL)
		host=localhost → Datenbank läuft lokal
		dbname=todoliste → Name der Datenbank
		charset=utf8mb4 → Zeichensatz (UTF-8, auch für Emojis etc.)
	
	$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
	
	
		PDO = PHP Data Objects
		
		PDO::ATTR_ERRMODE
		Fehlerverhalten. EXCEPTION bedeutet: Bei Fehlern wirft PDO Exceptions
		
		PDO::ATTR_DEFAULT_FETCH_MODE
		FETCH_ASSOC liefert nur assoziative Arrays (statt numerischer + ass.)
	
	
	$options = [
		PDO::ATTR_ERRMODE				=>	PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE	=>	PDO::FETCH_ASSOC,
	];
	
	try{
		
			$pdo = new PDO($dsn, $user, $pass, $options);
			Das erstellt ein neues PDO-Objekt und stellt die Verbindung zur
			Datenbank her.
			
			$dsn: Infos zur DB
			$user: Benutzername (bei XAMPP: root)
			$pass: Passwort (bei XAMPP: leer)
			$options: Verhalten & Einstellungen
		
		$pdo = new PDO($dsn, $user, $pass, $options);
		
		
			catch (\PDOException $e)
			PDOException ist die Fehlerklasse von PDO
			
			Das \ am Anfang sagt PHP: „Nimm die PDOException aus dem globalen
			Namensraum“ (das ist wichtig, wenn du in Namespaces arbeitest – bei
			dir wäre es auch ohne \ okay)
		
	}catch(\PDOException $e){
		exit('Verbindungsfehler: '.$e->getMessage());
	}
	*/
?>