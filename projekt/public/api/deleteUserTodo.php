<?php
	require_once __DIR__ . '/../../bootstrap/init.php';
	header('Content-Type: application/json');
	
	/*
	 * Holt eine bestehende oder erstellt eine neue PDO-Datenbankverbindung ueber die Database-Klasse.
	 */
	$pdo = Database::getConnection();
	
	/**
	 * Prueft, ob die korrekte Anfrage-Methode verwendet wurde.
	 * Beendet die Anfrage bei falscher Methode mit einem Fehlercode.
	 *
	 * @todo
     * Auslagern in eine generische Hilfsfunktion zur Wiederverwendung in anderen Endpunkten.
	 * Parameter sollten sein: erwartete HTTP-Methode, Fehlernachricht, Fehlercode.
	 * Potenzial zur Modularisierung reicht bis einschliesslich Zeile 36:
	 * - Anfragevalidierung (Header und Body)
	 * - Einheitliche Fehlerbehandlung
	 */
	if($_SERVER['REQUEST_METHOD'] !== 'DELETE'){
		//	Methode nicht erlaubt
		http_response_code(405);
		echo json_encode(['error' => 'Nur DELETE-Anfragen erlaubt']);
		exit();
	}
	
	/*
	 * Liest die JSON-Daten aus der Anfrage und extrahiert die Todo-ID.
	 */
	$input = json_decode(file_get_contents('php://input'), true);
	$todoId = $input['id'];
	
	/*
	 * Prueft, ob eine Todo-ID uebergeben wurde.
	 * Gibt bei fehlender ID einen Fehler zurueck.
	 */
	if(!$todoId){
		http_response_code(400);
		echo json_encode(['error' => 'Todo-ID muss angegeben werden']);
		exit();
	}
	
	/**
	 * Extrahiert den Bearer-Token aus dem Authorization-Header der HTTP-Anfrage.
	 * Unterstuezt Standard-Header sowie apache_request_headers() als Fallback.
	 *
	 * @return string|null Der Bearer-Token oder null wenn keiner gefunden wurde.
	 *
	 * @todo
	 * Auslagern in ein separates Modul zur Wiederverwendung und besseren Strukturierung.
	 */
	function getBearerToken(): ?string{
		/*
		 * Header direkt aus $_SERVER.
		 */
		$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
		
		/*
		 * Fallback fuer Apache.
		 */
		if(empty($authHeader) && function_exists('apache_request_headers')){
			$headers = apache_request_headers();
			if(isset($headers['Authorization'])){
				$authHeader = $headers['Authorization'];
			}
		}
		
		/*
		 * Extrahiere Token, wenn Bearer vorhanden
		 */
		if(str_starts_with($authHeader, 'Bearer ')){
			return trim(substr($authHeader, 7));
		}
		
		/*
		 * Kein gueltiger Token
		 */
		return null;
	}
	
	$token = getBearerToken();
	
	/*
	 * Prueft, ob ein gueltiger Token vorhanden ist.
	 * Gibt bei fehlendem oder ungueltigem Token einen Fehler zurueck.
	 *
	 * @todo
     * Diese Token-Pruefung sollte ausgelagert werden.
	 * Sie wird an mehreren Stellen im Projekt wiederverwendet.
	 * Ziel: zentrale Methode, die Token validiert und bei Fehlern eine
	 * konsistente Antwort erzeugt.
	 */
	if(!$token){
		//	Bad Request
		http_response_code(400);
		echo json_encode(['error' => 'Autorisierung nicht moeglich, token:'.$token.' ist kein gueltiger token']);
		exit();
	}
	
    /**
	 * Prueft, ob die Token-Signatur gueltig ist und liest die Benutzer-ID aus.
	 * 
	 * @todo
	 * Auslagern in ein separates Modul zur Wiederverwendung und besseren Strukturierung.
	 *
	 * @remarks
	 * Nutzt die JwtHandler-Klasse:
	 * 1. validiert den Token,
	 * 2. extrahiert die user_id
	 */
	$jwt = new \Core\JwtHandler();
	$userData = $jwt->validateToken($token);
	$userId = $jwt->getUserIdFromToken($token);
	
	/*
	 * Prueft, ob die Benutzerinformationen vorhanden sind.
	 * Gibt bei fehlender Autorisierung einen Fehler zurueck.
	 *
	 * @todo
     * Die Pruefung auf gueltige Benutzerinformationen inklusive Fehlerbehandlung
	 * kann ausgelagert werden, diese wird auch an anderen Stellen benoetigt.
	 * Ziel: zentrale Methode zur Autorisierungspruefung, die bei ungueltigem Benutzer
	 * einheitlich mit Fehlercode und Nachricht reagiert.
	 *
	 * @todo
	 * Die Logik der Fehlerbehandlung ist falsch.
	 * Unbedingt anpassen.
	 *
	 * @remarks
	 * Wenn userData null ist, kann nicht darauf zugegriffen werden.
	 * Dies fuehrt zu unvorhersehbaren Problemen im Programmablauf.
	 */
	if($userData === null){
		http_response_code(401);
		echo json_encode(['error' => $userData['message']]);
		exit();
	}
	
	$deleteSuccess = deleteTodo($pdo, $userId, $todoId);
	
	/**
	 * Antwortstruktur fuer Backend-Meldung
	 *
	 * Erfolgsfall:
	 * @return array{
	 *   success: bool
	 * }
	 *
	 * Fehlerfall:
	 * @return array{
	 *   success: false,
	 *   message: string
	 * }
	 *
	 * @see deleteTodo
	 *
	 * @todo
     * Diese JSON-Antwort sollte in eine Hilfsfunktion ausgelagert werden,
	 * um Wiederverwendung und klarere Struktur zu ermoeglichen.
	 * Langfristig waere auch eine eine vereinheitlichte JSON-Antwortstruktur fuer alle
	 * API-Endpunkte sinnvoll, die Payloads konsistent behandeln.
	 */
	
	/*
	 * Versende an Frontend, dass Todo erfolgreich geloescht wurde.
	 */
	if($deleteSuccess){
		
		echo json_encode(['success' => true]);
	}else{
		/*
		 * Todo wurde nicht geloescht.
		 * Sende Fehlermeldung an das Frontend.
		 */
		http_response_code(404);
		echo json_encode([
			'success' => false,
			'message'=> 'Todo wurde nicht gefunden oder konnte nicht geloescht werden'
		]);
	}
?>