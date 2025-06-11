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
	 */
	if($_SERVER['REQUEST_METHOD'] !== 'GET'){
		//	Methode nicht erlaubt
		http_response_code(405);
		echo json_encode([
			'error' => 'Nur Get-Anfragen erlaubt'
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
		//	1. Versuche zuerst den Header aus $_SERVER (Standardfall)
		$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
		
		//	2.	Wenn leer, nutze apache_request_headers (nur bei Apache verfuegbar)
		if(empty($authHeader) && function_exists('apache_request_headers')){
			$headers = apache_request_headers();
			if(isset($headers['Authorization'])){
				$authHeader = $headers['Authorization'];
			}
		}
		
		//	3.	Pruefe auf Bearer
		if(str_starts_with($authHeader, 'Bearer ')){
			return trim(substr($authHeader, 7));
		}
		
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
		echo json_encode([
			'error' => $userData['message']
		]);
		exit();
	}
	
	/**
	 * Benutzerdaten aus der Datenbank abrufen.
	 *
	 * @todo Auslagern in eine Hilfsfunktion.
	 */
	$stmt = $pdo->prepare("select first_name, surname from users where id = ?");
	$stmt->execute([$userId]);
	$user = $stmt->fetch();
	
	/**
	 * Antwortstruktur fuer Backend-Meldung.
	 *
	 * Erfolgsfall:
	 * @return array{
	 *   user_id: int,
	 *   surname: string,
	 *   first_name: string
	 * }
	 *
	 * Fehlerfall:
	 * @return array{
	 *   error: string
	 * }
	 */
	
	/*
	 * Sende Fehlermeldung, wenn Benutzer nicht gefunden.
	 */
	if(!$user){
		http_response_code(404);
		echo json_encode([
			'error' => 'Benutzer nicht gefunden'
		]);
		exit();
	}
	
	/*
	 * Sende Benutzerinformationen an das Frontend.
	 */
	echo json_encode([
		'user_id' => $userId,
		'surname' => $user['surname'],
		'first_name' => $user['first_name']
	]);
?>