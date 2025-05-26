<?php
	//	ANPASSEN: REGISTER-FUNKTIONALITAET
	//	NAME UND VORNAME EINFUEGEN
	require_once __DIR__ . '/../../bootstrap/init.php';
	header('Content-Type: application/json');
	
	try{
		$pdo = Database::getConnection();
	}catch(\RuntimeException $e){
		http_response_code(500);
		//	Fuer Produktionsbetrieb
		echo json_encode(['error' => 'Interner Serverfehler. Bitte später erneut versuchen.']);
		echo json_encode(['error' => $e->getMessage()]);
		exit();
	}
	
	//	Das heisst, wir pruefen hier, ob unsere Anfrage per Post-
	//	Methode erfolgt, wenn ja, dann holen wir die Daten aus der Anfrage,
	//	die wir zuvor per JS-Code bzw fetch versendet haben?
	if($_SERVER['REQUEST_METHOD'] !== 'POST'){
		//	Methode nicht erlaubt
		//	Was ist hier mit Methode konkret gemeint und was bedeutet
		//	der http response code 405?
		//	405 = Method not allowed
		http_response_code(405);
		echo json_encode(['error' => 'Nur Post-Anfragen erlaubt']);
		exit();
	}
	
	//	JSON-Daten auslesen
	//	Bitte erlaetere mir die Funktion:
	//	file_get_contents() - Parameter und Rueckgabewert
	$input = json_decode(file_get_contents('php://input'), true);
	$email = $input['email'] ?? '';
	$password = $input['password'] ?? '';
	$firstName = $input['firstName'] ?? '';
	$surname = $input['surname'] ?? '';
	
	if(!$email || !$password || !$firstName || !$surname){
		//	Bad Request
		//	Was ist hier mit Methode konkret gemeint und was bedeutet
		//	der http response code 405?
		//	400 = Bad Request
		http_response_code(400);
		echo json_encode(['error' => 'E-Mail, Passwort, Vor- und Nachname sind erforderlich']);
		exit();
	}
	
	//	Prüfen, ob Email regestriert ist (inkl. Validierung)
	$emailError = isEmailRegistered($pdo, $email);
	if($emailError !== null){
		//	Falls E-Mail ungültig oder bereits registriert
		http_response_code(400);
		echo json_encode(['error' => $emailError]);
		exit();
	}
	
	try{
		//	Nutzer erstellen
		$result = createUser($pdo, $email, $password, $surname, $firstName);
		if(isset($result['error'])){
			//	Falls beim Erstellen des Nutzers ein Fehler auftritt
			http_response_code(400);
			echo json_encode(['error' => $result['error']]);
			exit();
		}
		//	Wenn die Regestrierung erfolgreich verlauft, dann wir eine
		//	Erfolgsmeldung zurueck gegebn.
		//	register.php ist dafür verantwortlich, das endgültige JSON auszugeben.
		//	Deshalb nutzt du dort echo json_encode(...) – denn diese Datei "spricht
		//	mit der Außenwelt" (z. B. JS/Browser).
		echo json_encode(['message' => 'Die Regestrierung war erfolgreich.']);
	
		}catch(\PDOException $e){
			//	Fehlerbehandlung, falls ein Fehler bei der DB-Abfrage oder beim
			//	Insert auftritt
			//	Hier muss also auch ein http_response_code stehen
			//	Und der eigentliche Fehler wird per JSON an die Aufrufende JS-Datei
			//	zurueckgegeben. Zuletzt wird nur exit() aufgerufen
			//	Ist das korrekt?
			http_response_code(500);
			echo json_encode(['error' => 'Fehler bei der Regestrierung: '.$e->getMessage()]);
			exit();
		}
?>