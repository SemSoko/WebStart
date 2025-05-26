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
	
	//	Login-Funktion
	function loginUser($email, #[\SensitiveParameter]$password){
		//	Datenbank-Verbindung holen
		$pdo = Database::getConnection();
		
		//	Benutzer aus der DB holen
		try{
			//	Prepared Statement zum Abrufen des Benutzers
			$stmt = $pdo->prepare("select id, password from users where email = ?");
			$stmt->execute([$email]);
			$user = $stmt->fetch();
			
			//	Wenn kein Benutzer gefunden wird oder das Passwort nicht passt
			if(!$user || !password_verify($password, $user['password'])){
				http_response_code(401);
				return ['error' => 'E-Mail oder Passwort ist ungueltig.'];
			}
			
			//	JWT erzeugen
			$jwt = new JwtHandler();
			$payload = [
				'user_id' => $user['id'],
				//	Ausstellungszeitpunkt
				'iat' => time(),
				//	Ablaufzeitpunkt: 24H
				'exp' => time() + (60 * 60 * 24)
			];
			
			$token = $jwt->generateToken($payload);
			
			//	Rückgabe des Tokens, als Array
			return [
				'message' => 'Login erfolgreich',
				'token' => $token
			];
			
		}catch(\PDOException $e){
			//	Fehlerbehandlung\
			http_response_code(500);
			return ['error' => 'Fehler beim Login: '.$e->getMessage()];
		}
	}

	//	Funktion zum Verarbeiten der Benutzereingaben
	function processLoginForm(): array{
		//	Nur POST zulassen
		//	Wenn kein POST-Request, nichts tun
		if($_SERVER['REQUEST_METHOD'] !== 'POST'){
			http_response_code(405);
			return ['error' => 'Nur POST erlaubt'];
		}
		
		//	JSON-Body lesen
		$input = json_decode(file_get_contents('php://input'), true);
		$email = trim($input['email'] ?? '');
		$password = $input['password'] ?? '';
		
		//	Email validieren
		if($error = isValidEmail($email)){
			return ['error' => $error];
		}
		
		//	Passwort validieren
		if($error = isValidPassword($password)){
			return ['error' => $error];
		}
		
		//	Login durchfuehren
		//	Gibt ein Array zurueck
		return loginUser($email, $password);
	}
	
	//	Session- und Loginprüfung
	
	/*
		requireJwtAuth(): ?int
		Funktionserklärung: Definiert eine Funktion mit dem Namen
		requireJwtAuth.
		(): ?int = Rückgabewert ist entweder ein int (z. B. 42 für user_id)
		oder null.
		Das ? bedeutet: nullable type (optional).
		In der Praxis wird bei Fehlern aber ohnehin vorher exit() aufgerufen,
		daher wird null nie zurückgegeben.
		
		
	*/
	function requireJwtAuth(): ?int{
		//	Header auslesen
		$headers = getallheaders();
		if(!isset($headers['Authorization'])){
			http_response_code(401);
			print_r($headers);
			echo json_encode(['error' => 'Authorization-Header fehlt']);
			exit();
		}
		
		
		
		//	Token extrahieren (Format: Bearer <token>)
		$authHeader = $headers['Authorization'];
		if(!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)){
			http_response_code(401);
			echo json_encode(['error' => 'Ungueltiger Authorization-Header']);
			exit();
		}
		$token = $matches[1];
		
		// Token validieren
		$jwt = new JwtHandler();
		$userId = $jwt->getUserIdFromToken($token);
		
		if(!$userId){
			//	Fehler wird im Handler schon ausgegeben\
			exit();
		}
		
		//	Erfolgreich -> gib user_id zurueck (fuer z.B. DB-Zugriffe)
		return $userId;
	}
	
	//	Cache-Leerung
	function preventBrowserCache(){
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Expires: 0");
	}
?>