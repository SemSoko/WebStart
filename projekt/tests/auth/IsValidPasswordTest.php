<?php
//	funktionen_tests.php
use PHPUnit\Framework\TestCase;
	
require_once __DIR__ . '/../funktionen.php';
	
//	Testklasse für isValidPassword()
class IsValidPasswordTest extends TestCase
{
	/*
		Test Nr. 1
		Eingabe eines gültigen Passworts: NameVonMir1-
	*/
	public function testValidPasswordIsAccepted(){
		$password = 'NameVonMir1-';
		$this->assertNull(isValidPassword($password));
	}
	
	/*
		Test Nr. 2
		Eingabe eines ungültigen Passworts: M2-a
		Das Passwort ist zu kurz
	*/
	public function testTooShortPasswordIsRejected(){
		$password = 'M2-a';
		$result = isValidPassword($password);
		$this->assertSame(
			'Das Passwort muss mindestens 8 Zeichen lang sein.',
			$result
		);
	}

	/*
		Test Nr. 3
		Eingabe eines ungültigen Passworts: ganzviele3-
		Das Passwort beinhaltet keine Großbuchstaben
	*/
	public function testMissingUppercaseIsRejected(){
		$password = 'ganzviele3-';
		$result = isValidPassword($password);
		$this->assertSame(
			'Das Passwort muss mindestens einen Großbuchstaben enthalten.',
			$result
		);
	}
	
	/*
		Test Nr. 4
		Eingabe eines ungültigen Passworts: GANZVIELE3-
		Das Passwort beinhaltet keine Kleinbuchstaben
	*/
	public function testMissingLowercaseIsRejected(){
		$password = 'GANZVIELE3-';
		$result = isValidPassword($password);
		$this->assertSame(
			'Das Passwort muss mindestens einen Kleinbuchstaben enthalten.',
			$result
		);
	}
	
	/*
		Test Nr. 5
		Eingabe eines ungültigen Passworts: einPasswort-
		Das Passwort beinhaltet keine Zahl
	*/
	public function testMissingDigitIsRejected(){
		$password = 'einPasswort-';
		$result = isValidPassword($password);
		$this->assertSame(
			'Das Passwort muss mindestens eine Zahl enthalten.',
			$result
		);
	}
	
	/*
		Test Nr. 6
		Eingabe eines ungültigen Passworts: einPasswort23
		Das Passwort beinhaltet kein Sonderzeichen
	*/
	public function testMissingSpecialCharIsRejected(){
		$password = 'einPasswort23';
		$result = isValidPassword($password);
		$this->assertSame(
			'Das Passwort muss mindestens ein Sonderzeichen enthalten.',
			$result
			
		);
	}
	
	/*
		Test Nr. 7
		Eingabe eines ungültigen Passworts: ein Passwort123-
		Das Passwort beinhaltet ein Leerzeichen (Whitespace)
	*/
	public function testWhitespaceIsRejected(){
		$password = 'ein Passwort123-';
		$result = isValidPassword($password);
		$this->assertSame(
			'Das Passwort darf keine Leerzeichen enthalten.',
			$result
		);
	}
	
	/*
		Test Nr. 8
		Eingabe eines ungültigen Passworts - Ein Leerzeichen
		Das Passwort muss mehrere Fehler auslösen. Das Fehlerarray muss mehrere
		Einträge enthalten.
	*/
	public function testMultipleErrorsAreReturned(){
		$password = ' ';
		$result = isValidPassword($password);
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
	}
}