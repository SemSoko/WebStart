<?php
	require_once '../../bootstrap/init.php';
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
	 * Potenzial zur Modularisierung reicht bis einschliesslich Zeile 39:
	 * - Anfragevalidierung (Header und Body)
	 * - Einheitliche Fehlerbehandlung
	 */
	if($_SERVER['REQUEST_METHOD'] !== 'POST'){
		//	Methode nicht erlaubt
		http_response_code(405);
		echo json_encode(['error' => 'Nur POST-Anfragen erlaubt']);
		exit();
	}
	
	/*
	 * Liest die JSON-Daten aus der Anfrage und extrahiert den Todo-Titel.
	 */
	$input = json_decode(file_get_contents('php://input'), true);
	$titleTodo = $input['title'];
	
	/*
	 * Prueft, ob ein Todo-Titel uebergeben wurde.
	 * Gibt bei fehlendem Titel einen Fehler zurueck.
	 */
	if(!$titleTodo){
		http_response_code(400);
		echo json_encode(['error' => 'Todo-Titel muss angegeben werden']);
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
	 */
	if($userData === null){
		http_response_code(401);
		echo json_encode(['error' => 'Token nicht gueltig oder abgelaufen']);
		exit();
	}
	
	$todoId = addTodo($pdo, $userId, $titleTodo);
	
	/**
	 * Antwortstruktur fuer Erfgolgsmeldung
	 *
	 * Erfolgsfall:
	 * @return array{
	 *   success: bool,
	 *   completeTodo: array{
	 *	   todo_id: int,
	 *     todo_title: string,
	 *     todo_status: int,
	 *     todo_iat: string
	 *  }
	 * }
	 *
	 * Fehlerfall:
	 * @return array{
	 *   success: false,
	 *   message: string
	 * }
	 *
	 * @see addTodo
	 *
	 * @todo
     * Diese JSON-Antwort sollte in eine Hilfsfunktion ausgelagert werden,
	 * um Wiederverwendung und klarere Struktur zu ermoeglichen.
	 * Langfristig waere auch eine eine vereinheitlichte JSON-Antwortstruktur fuer alle
	 * API-Endpunkte sinnvoll, die Payloads konsistent behandeln.
	 */
	
	/*
	 * Versende das erfolgreich erstellte Todo an das Frontend.
	 */
	if($todoId !== null){
		/*
		 * Todo mit allen Informationen aus der DB auslesen.
		 */
		$stmt = $pdo->prepare("select * from todos where id = ?");
		$stmt->execute([$todoId]);
		$newTodo = $stmt->fetch(PDO::FETCH_ASSOC);
		
		/*
		 * Bereite das Todo fuer die Antwort an das Frontend vor.
		 */
		$newTodo = [
			'todo_id' => $newTodo['id'],
			'todo_title' => $newTodo['title'],
			'todo_status' => $newTodo['is_done'],
			'todo_iat' => $newTodo['created_at']
		];
		
		echo json_encode([
			'success' => true,
			'completeTodo' => $newTodo
		]);
	}else{
		/*
		 * Todo wurde nicht erzeugt.
		 * Sende Fehlermeldung an das Frontend.
		 */
		echo json_encode([
			'success' => false,
			'message' => 'Fehler beim Hinzufuegen des Todos'
		]);
	}
?>