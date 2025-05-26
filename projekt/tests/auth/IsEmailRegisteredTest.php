<?php
//	funktionen_tests.php
use PHPUnit\Framework\TestCase;
	
require_once __DIR__ . '/../funktionen.php';

//	Testklasse für isEmailRegistered()
class IsEmailRegisteredTest extends TestCase
{
	private PDO $pdo;
	
	protected function setUp(): void
	{
		//	In-Memory SQLite Datenbank (wird nach jedem Test gelöscht)
		$this->pdo = new PDO('sqlite::memory:');
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//	Tabellen anlegen
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
		Eingabe einer ungültigen Email-Adresse: sf
		Die Email-Validierung muss fehlschlagen
	*/
	public function testInvalidEmailRejected(){
		$email = 'sf';
		$result = isEmailRegistered($this->pdo, $email);
		$this->assertSame(
			'Bitte eine gültige E-Mail-Adresse angeben.',
			$result
		);
	}
	
	/*
		Test Nr. 2
		Eingabe einer gültigen Email-Adresse: test@test.de
		Die Email-Validierung muss erfolgreich durchlaufen
	*/
	public function testUnregisteredEmailReturnsNull(){
		$email = 'test@hallo.de';
		$result = isEmailRegistered($this->pdo, $email);
		$this->assertNull($result);
	}
	
	/*
		Test Nr. 3
		Eingabe einer ungültigen Email-Adresse: test@test.de
		Die Email-Validierung muss fehlschlagen, weil die Email bereits
		hinterlegt ist
	*/
	public function testAlreadyRegisteredEmailReturnsError(){
		//	Die Email in die Test-DB einfügen
		$email = 'test@test.de';
		$stmt = $this->pdo->prepare("insert into users (email, password) values (?, ?)");
		$stmt->execute([$email, 'hashed']);
		
		$result = isEmailRegistered($this->pdo, $email);
		$this->assertSame(
			'Diese E-Mail ist bereits registriert.',
			$result
		);
	}
}
?>