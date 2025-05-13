<?php
	require_once 'db.php';
	require_once 'funktionen.php';
	require_once 'JwtHandler.php';
	
	use Core\JwtHandler;
	
	//	Nur starten, wenn nicht im Test-Modus
	if(session_status() === PHP_SESSION_NONE && php_sapi_name() !== 'cli'){
		session_start();
	}
	
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
				return 'Email oder Passwort ist ungültig.';
			}
			
			//	JWT erzeugen
			$jwt = new JwtHandler();
			$payload = [
				'user_id' => $user['id'],
				'email' => $user['email'],
				//	Ausstellungszeitpunkt
				'iat' => time(),
				//	Ablaufzeitpunkt: 24H
				'exp' => time() + (60 * 60 * 24)
			];
			
			$token = $jwt->generateToken($payload);
			
			//	WICHTIG!!!!!!!!!!!!!!!!!!!!!!
			//	Übergangslösung, damit die Session verabeitet werden kann
			//	unbedingt ändern bzw. überall JWT einsetzen, damit die
			//	Verarbeitung überall erfolgen kann
			$_SESSION['user_id'] = $user['id']; // Jetzt ist die user_id in der Session verfügbar
			
			//	Rückgabe des Tokens
			return json_encode(['message' => 'Login erfolgreich', 'token' => $token]);
			
		}catch(\PDOException $e){
			//	Fehlerbehandlung
			return 'Fehler beim Login: '.$e->getMessage();
		}
	}

	//	Funktion zum Verarbeiten der Benutzereingaben
	function processLoginForm(){
		//	Wenn kein POST-Request, nichts tun
		if($_SERVER['REQUEST_METHOD'] !== 'POST'){
			return null;
		}
		
		//	Benutzereingaben
		$email = trim($_POST['email']);
		$password = $_POST['password'];
		
		//	Email validieren
		$emailError = isValidEmail($email);
		if($emailError !== null){
			//	Rückgabe der Fehlermeldung
			return $emailError;
		}
		
		//	Benutzer einloggen und JWT erhalten
		$result = loginUser($email, $password);
		
		//	Wenn Login erfolgreich und JWT erhalten
		$resultArray = json_decode($result, true);
		if(isset($resultArray['token'])){
			/*	
				Token speichern (z.B. in einer Session oder Weiterleitung)
				Hier können wir das JWT im Frontend speichern (localStorage,
				Cookies o.ä.)
			*/
			$_SESSION['jwt_token'] = $resultArray['token'];
			header('Location: dashboard.php');
			exit();
		}
		
		//	Wenn ein Fehler auftritt, gib den Fehler zurück
		return $result;
	}
	
	//	Session- und Loginprüfung
	function requireLogin(){
		if(session_status() === PHP_SESSION_NONE){
			session_start();
		}
		if(!isset($_SESSION['user_id'])){
			header('Location: login.php');
			exit();
		}
	}
	
	//	Cache-Leerung
	function preventBrowserCache(){
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Expires: 0");
	}
?>