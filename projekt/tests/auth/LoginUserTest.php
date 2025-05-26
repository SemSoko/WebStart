<?php
//	funktionen_tests.php
use PHPUnit\Framework\TestCase;
	
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';
	
//	Testklasse für loginUser()
class LoginUserTest extends TestCase
{
	private PDO $pdo;
	
	protected function setUp(): void{
		//	In-Memory-Datenbank
		$this->pdo = new PDO("sqlite::memory:");
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//	Nutzer-Tabelle erstellen
		$this->pdo->exec("
			create table users(
				id integer primary key autoincrement,
				email text unique not null,
				password text not null,
				created_at text default current_timestamp
			);
		");
		
		//	Database::getConnection() temporär überschreiben (Hack für Testing)
		Database::setTestConnection($this->pdo);
		
		//	Leeren, falls vorher belegt
		$_SESSION = [];
	}
	
	/*
		Test Nr. 1
		Eingabe gültiger Login-Daten.
		Test soll prüfen, ob sich ein Nutzer (bereits regestriert) anmelden kann.
	*/
	public function testSuccessfulLogin(){
		$email = 'test@test.de';
		$password = 'MeinPasswort1-';
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		
		//	User in DB einfügen
		$stmt = $this->pdo->prepare("insert into users (email, password) values (?, ?)");
		$stmt->execute([$email, $hashedPassword]);
		
		$result = loginUser($email, $password);
		$this->assertSame('Login erfolgreich', $result);
		$this->assertArrayHasKey('user_id', $_SESSION);
		$this->assertIsInt($_SESSION['user_id']);
	}
	
	/*
		Test Nr. 2
		Eingabe ungültiger Login-Daten
		Test soll prüfen, wie die Funktion auf:
		-	ungültige Passwörter: Falsch123! reagiert
		Es wird eine entsprechende Fehlermeldung erwartet.
	*/
	public function testLoginFailsWithWrongPassword(){
		$email = 'test@test.de';
		$correctPassword = 'Richtig123!';
		$wrongPassword = 'Falsch123!';
		$hashedPassword = password_hash($correctPassword, PASSWORD_DEFAULT);
		
		$stmt = $this->pdo->prepare("insert into users (email, password) values (?, ?)");
		$stmt->execute([$email, $hashedPassword]);
		
		$result = loginUser($email, 'EinPasswort123-');
		$this->assertSame(
			'Email oder Passwort ist ungültig.',
			$result
		);
		$this->assertArrayNotHasKey('user_id', $_SESSION);
	}
	
	/*
		Test Nr. 3
		Eingabe ungültiger Login-Daten
		Test soll prüfen, wie die Funktion auf:
		-	ungültige Emails: einewer@teset.de reagiert
		Es wird eine entsprechende Fehlermeldung erwartet.
	*/
	public function testLoginFailsWithUnknownEmail(){
		$email = 'eine@teset.de';
		$password = '123';
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		
		$stmt = $this->pdo->prepare("insert into users (email, password) values (?, ?)");
		$stmt->execute([$email, $hashedPassword]);
		
		$result = loginUser('einewer@teset.de', $hashedPassword);
		$this->assertSame(
			'Email oder Passwort ist ungültig.',
			$result
		);
		/*
			Wenn Anmeldung nicht erfolgreich, dann kann die user_id nicht
			im Session-Array gestzt sein
		*/
		$this->assertArrayNotHasKey('user_id', $_SESSION);
	}
	
	/*
		Test Nr. 4
		Eingabe ungültiger Login-Daten
		Test soll prüfen, wie die Funktion auf:
		-	ungültige Email: sadf@web.de und ungültiges Passwort: 123 reagiert
		Es wird eine entsprechende Fehlermeldung erwartet.
	*/
	public function testLoginFailsWithWrongEmailAndPassword(){
		$email = 'test@test.de';
		$password = 'Hallo1-m324';
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		
		$stmt = $this->pdo->prepare("insert into users (email, password) values (?, ?)");
		$stmt->execute([$email, $hashedPassword]);
		
		$result = loginUser('sadf@web.de', '123');
		$this->assertSame(
			'Email oder Passwort ist ungültig.',
			$result
		);
		/*
			Wenn Anmeldung nicht erfolgreich, dann kann die user_id nicht
			im Session-Array gesetzt sein
		*/
		$this->assertArrayNotHasKey('user_id', $_SESSION);
	}
}