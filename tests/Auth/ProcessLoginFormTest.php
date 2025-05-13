<?php
//	funktionen_tests.php
use PHPUnit\Framework\TestCase;
	
require_once __DIR__ . '/../auth.php';
	
//	Testklasse für processLoginForm()
class ProcessLoginFormTest extends TestCase
{
	protected function setUp(): void{
		//	Simuliere leere POST-Daten und setzte den Request-Typ zurück
		$_POST = [];
		$_SESSION = [];
		$_SERVER['REQUEST_METHOD'] = 'GET';
	}

	/*
		Test Nr. 1
		Formulardaten werden nicht per POST-Request übertragen
		Senden eines anderen Request-Typs. Test muss entsprechenden Fehler
		ausgeben.
	*/
	public function testReturnsNullOnNonPostRequest(){
		$_SERVER['REQUEST_METHOD'] = 'GET';
		
		$result = processLoginForm();
		$this->assertNull($result);
	}
}