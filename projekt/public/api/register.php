<?php
	/**
	 * @httpcode 500 Internal Server Error
	 * @httpcode 405 Method Not Allowed
	 * @httpcode 400 Bad Request
	 */

	require_once __DIR__ . '/../../bootstrap/init.php';
	header('Content-Type: application/json');
	
	/*
	 * Holt eine bestehende oder erstellt eine neue PDO-Datenbankverbindung ueber die Database-Klasse.
	 */
	try{
		$pdo = Database::getConnection();
	}catch(\RuntimeException $e){
		http_response_code(500);
		//	Fuer Produktionsbetrieb
		echo json_encode(['error' => 'Interner Serverfehler. Bitte später erneut versuchen.']);
		echo json_encode(['error' => $e->getMessage()]);
		exit();
	}

	/**
	 * Prueft, ob die korrekte Anfrage-Methode verwendet wurde.
	 * Beendet die Anfrage bei falscher Methode mit einem Fehlercode.
	 *
	 * @todo
     * Auslagern in eine generische Hilfsfunktion zur Wiederverwendung in anderen Endpunkten.
	 * Parameter sollten sein: erwartete HTTP-Methode, Fehlernachricht, Fehlercode.
	 */
	if($_SERVER['REQUEST_METHOD'] !== 'POST'){
		http_response_code(405);
		echo json_encode(['error' => 'Nur Post-Anfragen erlaubt']);
		exit();
	}
	
	/*
	 * Liest die JSON-Daten aus der Anfrage und extrahiert:
	 * - E-Mail,
	 * - Passwort,
	 * - Vorname,
	 * - Nachname
	 */
	$input = json_decode(file_get_contents('php://input'), true);
	$email = $input['email'] ?? '';
	$password = $input['password'] ?? '';
	$firstName = $input['firstName'] ?? '';
	$surname = $input['surname'] ?? '';
	
	/*
	 * Prueft, ob:
	 * - E-Mail,
	 * - Passwort,
	 * - Vorname,
	 * - Nachname
	 * uebergeben wurde.
	 * Gibt bei fehlendem Titel einen Fehler zurueck.
	 */
	if(!$email || !$password || !$firstName || !$surname){
		http_response_code(400);
		echo json_encode(['error' => 'E-Mail, Passwort, Vor- und Nachname sind erforderlich']);
		exit();
	}
	
	$emailError = isEmailRegistered($pdo, $email);
	
	/*
	 * Prueft, ob die empfangene E-mail gueltig ist.
	 */
	if($emailError !== null){
		http_response_code(400);
		echo json_encode(['error' => $emailError]);
		exit();
	}
	
	/*
	 * Fuehrt die Fehlerbehandlung im Kontext der Datenbank aus.
	 */
	try{
		$result = createUser($pdo, $email, $password, $surname, $firstName);
		/**
		 * Antwortstruktur fuer Fehlermeldung
		 *
		 * @return array{
		 *    error: string 
		 * }
		 */
		 
		/**
		 * @todo
		 * Die Fehlerbehandlung muss angepasst werden.
		 * Wenn das result-Array nicht gesetzt ist, kann darauf auch nicht
		 * zugegriffen werden. Genau dies wird aber gemacht.
		 */
		 
		/*
		 * Sende Fehlermeldung, wenn Nutzer nicht erstellt werden konnte.
		 */
		if(isset($result['error'])){
			http_response_code(400);
			echo json_encode(['error' => $result['error']]);
			exit();
		}
		
		/**
		 * Antwortstruktur fuer Erfgolgsmeldung
		 *
		 * @return array{
		 *    message: string 
		 * }
		 *
		 * @remarks
		 * Hier muessen die Daten als JSON versendet werden, weil eine direkte
		 * Kommunikation zwischen Front- und Backend besteht.
		 * Siehe: Grundstruktur-API-Architektur-Rueckgabeprinzipien.txt (in Aufzeichnungen)
		 */
		
		/*
		 * Sende Erfolgsmeldung, wenn Nutzer erstellt werden konnte.
		 */
		echo json_encode(['message' => 'Die Regestrierung war erfolgreich.']);
		}catch(\PDOException $e){
			http_response_code(500);
			echo json_encode(['error' => 'Fehler bei der Regestrierung: '.$e->getMessage()]);
			exit();
		}
?>