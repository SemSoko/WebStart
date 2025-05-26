<?php
	//	Sicherstellen, dass die .env geladen ist
	require_once __DIR__ . '/../../bootstrap/init.php';

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
				$host = getenv('DB_HOST');
				//	$db = $_ENV['DB_NAME'];
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
					//	Direkte Ausgabe vermeiden, stattdesen Exception nutzen
					//	exit('Verbindungsfehler: '.$e->getMessage());
					throw new \RuntimeException('Verbindungsfehler: '.$e->getMessage(), 500);
				}
			}
			
			return self::$pdo;
		}
	}
?>