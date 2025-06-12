<?php
	require_once __DIR__ . '/../../bootstrap/init.php';
	
	/**
	 * Singelton-aehnliche Klasse zum Verarbeiten der PDO-Datenbankverbindung.
	 *
	 * Stellt eine Verbindung zu MariaDB her,
	 * liest die Zugangsdaten aus den Umgebungsvariablen
	 * und unterstuetzt separate Verbindungen fuer Tests.
	 */
	class Database {
		/**
		 * Die persistente PDO-Verbindung fuer die Anwendung.
		 * Wird einmalig beim ersten Zugriff erzeugt.
		 *
		 * @var PDO|null
		 */
		private static ?PDO $pdo = null;
		
		/**
		 * Alternative PDO-Verbindung fuer Tests (z.B. mit In-Memory-Datenbank).
		 *
		 * @var PDO|null
		 */
		private static ?PDO $testConnection = null;
		
		/**
		 * Setzt eine alternative PDO-Verbindung fuer Tests.
		 * Diese Verbindung wird von getConnection() bevorzugt verwendet.
		 * Wenn eine Testverbindung gesetzt wurde, wird diese bevorzugt zurueckgegeben.
		 * Unabhaengig davon, ob bereits eine Standardverbindung besteht.
		 *
		 * @param PDO|null $pdo Die zu verwendende Testverbindung.
		 * @return void
		 */
		public static function setTestConnection(?PDO $pdo): void{
			self::$testConnection = $pdo;
		}
		
		/**
		 * Privater Konstruktor verhindert direkte Instanziierung dieser Klasse.
		 *
		 * @remarks
		 * Dadurch kann die Klasse nur ueber ihre statischen Methoden genutzt werden.
		 * So wird sichergestellt, dass nur eine zentrale Verbindung verwaltet wird.
		 */
		private function __construct() {}
		
		/**
		 * Gibt die aktive PDO-Verbindung zurueck.
		 * 
		 * @remarks
		 * Falls zuvor eine Testverbindung gesetzt wurde, wird diese bevorzugt verwendet.
		 *
		 * @throws \RuntimeException Wenn die Verbindung nicht aufgebaut werden kann.
		 * @return PDO Die aktive Datenbankverbindung.
		 */
		public static function getConnection(): PDO {
			/*
			 * Prueft, ob es bereits eine Testverbindung gibt.
			 */
			if(self::$testConnection !== null){
				return self::$testConnection;
			}
			
			/*
			 * Prueft, ob eine Datenbankverbindung besteht, wenn nicht, wird eine
			 * Datenbankverbindung hergestellt.
			 */
			if(self::$pdo === null){
				/*
				 * Vorbereitende Massnahmen fuer den Verbindungsaufbau mit Hilfe von Umgebungsvariablen.
				 */
				$host = getenv('DB_HOST');
				$db = getenv('DB_NAME');
				$user = getenv('DB_USER');
				$pass = getenv('DB_PASS');
				$charset = getenv('DB_CHARSET');
				$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
				$options = [
					PDO::ATTR_ERRMODE				=>	PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE	=>	PDO::FETCH_ASSOC
				];
				
				try{
					self::$pdo = new PDO($dsn, $user, $pass, $options);
				}catch(\PDOException $e){
					throw new \RuntimeException('Verbindungsfehler: '.$e->getMessage(), 500);
				}
			}
			
			return self::$pdo;
		}
	}
?>