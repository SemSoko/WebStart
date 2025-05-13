<?php
//	funktionen_tests.php
use PHPUnit\Framework\TestCase;
	
require_once __DIR__ . '/../funktionen.php';
	
//	Testklasse für createUser()
class CreateUserTest extends TestCase
{
	private PDO $pdo;
	
	protected function setUp(): void{
		$this->pdo = new PDO('sqlite::memory:');
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec("
			create table users(
				id int AUTO_INCREMENT primary key,
				email varchar(255) unique not null,
				password varchar(255) not null,
				created_at timestamp default current_timestamp
			)
		");
	}
	
	/*
		Test Nr. 1
		Erstellen eines Nutzers mit gültigen Daten
		Email: test@test.de
		Passwort: MeinPasswort-1
		Der Test muss erfolgreich durchlaufen und einen Benutzer in der DB
		anlegen
	*/
	public function testValidUserIsCreated(){
		$email = 'test@test.de';
		$password = 'MeinPasswort-1';
		
		$result = createUser($this->pdo, $email, $password);
		$this->assertNull($result);
		
		$stmt = $this->pdo->query("select * from users where email = '$email'");
		$user = $stmt->fetch();
		
		$this->assertNotFalse($user);
		$this->assertSame($email, $user['email']);
		//	Passwort muss gehashed sein
		$this->assertNotSame($password, $user['password']);
		$this->assertTrue(password_verify($password, $user['password']));
	}
	
	/*
		Test Nr. 2
		Erstellen eines Nutzers mit ungültigem Passwort
		Email: test@test.de
		Passwort: wer
		Der Account darf nicht angelegt werden.
		
		Wichtig
		Das assert je nach Passwort anpassen. Denn Fehlermeldungen im
		Error-Array können abweichen je nach getesten Passwort.
		Entweder entfallen / kommen Fälle hinzu.
	*/
	public function testUserNotCreatedWithInvalidPassword(){
		$email = 'test@test.de';
		$password = ' ';
		
		$result = createUser($this->pdo, $email, $password);
		$this->assertStringContainsString(
			'Das Passwort muss mindestens 8 Zeichen lang sein.',
			$result
		);
		$this->assertStringContainsString(
			'Das Passwort muss mindestens einen Großbuchstaben enthalten.',
			$result
		);
		$this->assertStringContainsString(
			'Das Passwort muss mindestens einen Kleinbuchstaben enthalten.',
			$result
		);
		$this->assertStringContainsString(
			'Das Passwort muss mindestens eine Zahl enthalten.',
			$result
		);
		$this->assertStringContainsString(
			'Das Passwort muss mindestens ein Sonderzeichen enthalten.',
			$result
		);
		$this->assertStringContainsString(
			'Das Passwort darf keine Leerzeichen enthalten.',
			$result
		);
		
		$stmt = $this->pdo->query("select count(*) from users");
		$count = $stmt->fetchColumn();
		$this->assertSame(0, (int)$count);
	}
}