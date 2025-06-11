<?php
	/**
	 * @httpcode 400 Bad Request
	 * @httpcode 401 Unauthorized
	 * @httpcode 405 Method Not Allowed
	 */

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
	 * Potenzial zur Modularisierung reicht bis einschliesslich Zeile 43:
	 * - Anfragevalidierung (Header und Body)
	 * - Einheitliche Fehlerbehandlung
	 */
	if($_SERVER['REQUEST_METHOD'] !== 'PATCH'){
		http_response_code(405);
		echo json_encode([
			'error' => 'Nur PATCH-Anfragen erlaubt'
		]);
		exit();
	}
	
	/*
	 * Liest die JSON-Daten aus der Anfrage und extrahiert die Todo-ID.
	 */
	$input = json_decode(file_get_contents('php://input'), true);
	$todoId = $input['id'] ?? '';
	
	/*
	 * Prueft, ob die Todo-ID uebergeben wurde.
	 * Gibt bei fehlender ID einen Fehler zurueck.
	 */
	if(!$todoId){
		http_response_code(400);
		echo json_encode([
			'error' => 'Todo-ID erforderlich'
		]);
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
	 *
	 * @todo Error-Code anpassen.
	 *
	 * @remarks
	 * Statt dem aktuell genuzten Error-Code: 400, einen zutreffenderen finden.
	 * Vorschlag: 401.
	 */
	if(!$token){
		http_response_code(400);
		echo json_encode([
			'error' => 'Autorisierung nicht moeglich, token:'.$token.' ist kein gueltiger token'
		]);
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
	
	/**
	 * Prueft, ob die Benutzerinformationen vorhanden sind.
	 * Gibt bei fehlender Autorisierung einen Fehler zurueck.
	 *
	 * @todo
     * Die Pruefung auf gueltige Benutzerinformationen inklusive Fehlerbehandlung
	 * kann ausgelagert werden, diese wird auch an anderen Stellen benoetigt.
	 * Ziel: zentrale Methode zur Autorisierungspruefung, die bei ungueltigem Benutzer
	 * einheitlich mit Fehlercode und Nachricht reagiert.
	 */
	if($userData === null){
		http_response_code(401);
		echo json_encode([
			'error' => 'Token nicht gueltig oder abgelaufen'
		]);
		exit();
	}
	
	$todoStatus = toggleTodo($pdo, $userId, $todoId);
	
	/**
	 * Antwortstruktur fuer Backend-Meldung.
	 *
	 * Erfolgsfall:
	 * @return array{
	 *   success: bool,
	 *   completeTodo: array{
	 *     todo_id: int,
	 *     todo_title: string,
	 *     todo_status: int,
	 *     todo_iat: string
	 *  }
	 * }
	 *
	 * Fehlerfall:
	 * @return array{
	 *   error: string
	 * }
	 */
	 
	/*
	 * Erfolgreiche Statusanpassung an das Frontend senden.
	 */
	if($todoStatus === 1 || $todoStatus === 0){
		// Aktualisiertes vollstaendiges Todo
		/*
		 * Rufe das gesamte, aktualisierte Todo ab.
		 */
		$stmt = $pdo->prepare("select * from todos where id = ?");
		$stmt->execute([$todoId]);
		$todoStatus = $stmt->fetch(PDO::FETCH_ASSOC);
		
		/*
		 * Todo-Struktur aufbereiten, damit diese im Frontend korrekt verarbeitet werden kann.
		 */
		$todoStatus = [
			'todo_id' => $todoStatus['id'],
			'todo_title' => $todoStatus['title'],
			'todo_status' => $todoStatus['is_done'],
			'todo_iat' => $todoStatus['created_at'],
		];
		
		/*
		 * Sende das aktualisierte Todo.
		 *
		 * @todo Tippfehler korrigieren: succes -> success
		 */
		echo json_encode([
			'succes' => true,
			'completeTodo' => $todoStatus
		]);
	/*
	 * Sende Fehlermeldung.
	 */
	}else{
		http_response_code(401);
		echo json_encode([
			'error' => 'Status wurde nicht geaendert'
		]);
		exit();
	}
?>