<?php
	require_once __DIR__ . '/../../bootstrap/init.php';
	
	use Core\JwtHandler;
	
	//	Fuer fruehere Tests benoetigt, vor Umstellung auf Docker
	//	Nur starten, wenn nicht im Test-Modus
	/*
	if(session_status() === PHP_SESSION_NONE && php_sapi_name() !== 'cli'){
		session_start();
	}
	*/
	
	/**
	 * Versucht, einen Benutzer mit E-Mail und Passwort einzuloggen.
	 *
	 * Gibt im Erfolgsfall ein Array mit Erfolgsmeldung und JWT-Token zurueck.
	 * Gibt im Fehlerfall ein Array mit einer Fehlermeldung zurueck.
	 *
	 * Rueckgabeformat:
	 * Erfolgs-Array: ['message' => string, 'token' => string]
	 * Fehler-Array: ['error' => string]
	 *
	 * @param string $email Die E-Mail des Benutzers.
	 * @param string $password Das Passwort des Benutzers.
	 *
	 * @return array Erfolgs-Array mit Token oder Fehler-Array mit Fehlermeldung.
	 */
	function loginUser($email, #[\SensitiveParameter]$password){
		/*
		 * Holt eine bestehende oder erstellt eine neue PDO-Datenbankverbindung ueber die Database-Klasse.
		 */
		$pdo = Database::getConnection();
		
		/*
		 * Fuehrt die Fehlerbehandlung im Kontext der Datenbank aus.
		 */
		try{
			/*
			 * Benutzer aus der Datenbank abrufen.
			 */
			$stmt = $pdo->prepare("select id, password from users where email = ?");
			$stmt->execute([$email]);
			$user = $stmt->fetch();
			
			/*
			 * Sende Fehler an die aufrufende Instanz.
			 * Benutzer wurde nicht gefunden oder das Passwort stimmt nicht.
			 */
			if(!$user || !password_verify($password, $user['password'])){
				http_response_code(401);
				return [
					'error' => 'E-Mail oder Passwort ist ungueltig.'
				];
			}
			
			/*
			 * JWT-Token erzeugen.
			 */
			$jwt = new JwtHandler();
			$payload = [
				'user_id' => $user['id'],
				'iat' => time(),
				'exp' => time() + (60 * 60 * 24)
			];
			$token = $jwt->generateToken($payload);
			
			/*
			 * Erfolgreicher Login mit Token.
			 */
			return [
				'message' => 'Login erfolgreich',
				'token' => $token
			];
		}catch(\PDOException $e){
			http_response_code(500);
			return ['error' => 'Fehler beim Login: '.$e->getMessage()];
		}
	}

	/**
	 * Fuehrt die Login-Formularverarbeitung durch:
	 * - Prueft die Anfrage-Methode (nur POST erlaubt),
	 * - Liest und verarbeitet JSON-Daten aus der Anfrage,
	 * - Validiert E-Mail und Passwort,
	 * - Fuehrt den Login-Vorgang ueber loginUser() aus.
	 *
	 * Rueckgabeformat im Fehlerfall:
	 * Fehler-Array: ['error' => string]
	 *
	 * @return array Fehler-Array oder Rueckgabewert von loginUser().
	 */
	function processLoginForm(): array{
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
			return [
				'error' => 'Nur POST erlaubt'
			];
		}
		
		/*
		 * Liest die JSON-Daten aus der Anfrage und extrahiert E-Mail und Passwort.
		 */
		$input = json_decode(file_get_contents('php://input'), true);
		$email = trim($input['email'] ?? '');
		$password = $input['password'] ?? '';
		
		/*
		 * Validiert die E-Mail.
		 */
		if($error = isValidEmail($email)){
			return ['error' => $error];
		}
		
		/*
		 * Validiert das Passwort.
		 */
		if($error = isValidPassword($password)){
			return ['error' => $error];
		}

		return loginUser($email, $password);
	}
	
	/**
	 * Prueft, ob ein gueltiger JWT im Header vorhanden ist und gibt die Benutzer-ID zurueck.
	 * Beendet das Skript mit einem HTTP-Fehlercode und einer JSON-Fehlermeldung,
	 * falls kein oder ein ungueltiger Token vorhanden ist.
	 *
	 * Erwartetes Format im Header: Authorization: Bearer <token>
	 *
	 * Rueckgabeformat: ['error' => string]
	 *
	 * @return int Die Benutzer-ID aus dem Token.
	 */
	function requireJwtAuth(): ?int{
		/**
		 * Auslesen der Header Informationen.
		 *
		 * @todo
		 * Die Methode zur Header-Auslese (getallheaders vs. $_SERVER) vereinheitlichen.
		 * getallheaders() ist hier direkt verwendet, in anderen Dateien $_SERVER.
		 * Hilfsfunktion einfuehren, um alle Header-Zugriffe zu kapseln.
		 */
		$headers = getallheaders();
		if(!isset($headers['Authorization'])){
			http_response_code(401);
			print_r($headers);
			echo json_encode([
				'error' => 'Authorization-Header fehlt'
			]);
			exit();
		}
		
		/**
		 * Extraktion des Token aus dem Authorization-Header.
		 *
		 * @remarks
		 * Der regulaere Ausdruck prueft:
		 * - Ob Header mit "Bearer " beginnt,
		 * - und danach ein beliebiges nicht-Leerzeichen Token folgt.
		 * Bei abweichendem Format wird das Skript sofort mit einer
		 * JSON-Fehlermeldung beendet.
		 */
		$authHeader = $headers['Authorization'];
		if(!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)){
			http_response_code(401);
			echo json_encode(['error' => 'Ungueltiger Authorization-Header']);
			exit();
		}
		$token = $matches[1];

		/*
		 * Validieren des Tokens.
		 */
		$jwt = new JwtHandler();
		$userId = $jwt->getUserIdFromToken($token);
		
		/*
		 * Sicherheitspruefung: Beendet das Skript, falls das Token zwar formal korrekt war,
		 * aber ungueltig (z.B. abgelaufen, manipuliert oder falsch signiert).
		 * Die eigentliche Fehlermeldung wurde bereits im JwtHandler ausgegeben.
		 */
		if(!$userId){
			exit();
		}
		
		return $userId;
	}
	
	/**
	 * Setzt HTTP-Header, um das Caching durch Browser und Proxys zu verhindern.
	 *
	 * Eingesetzt fuer sensible Daten oder dynamischen Inhalte, damit der
	 * Browser keine veralteten Inhalte aus dem Cache laedt.
	 *
	 * @return void
	 */
	function preventBrowserCache(){
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Expires: 0");
	}
?>