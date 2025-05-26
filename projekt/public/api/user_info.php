<?php
	require_once __DIR__ . '/../../bootstrap/init.php';
	header('Content-Type: application/json');
	
	$pdo = Database::getConnection();
	
	if($_SERVER['REQUEST_METHOD'] !== 'GET'){
		//	Methode nicht erlaubt
		http_response_code(405);
		echo json_encode(['error' => 'Nur Get-Anfragen erlaubt']);
		exit();
	}
	
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
	
	if(!$token){
		//	Bad Request
		http_response_code(400);
		echo json_encode(['error' => 'Autorisierung nicht moeglich, token:'.$token.' ist kein gueltiger token']);
		exit();
	}
	
	//	JWT ueberprufen
	//	Neue Insanz der Klasse: JwtHandler
	//	Konstruktoraufruf
	$jwt = new \Core\JwtHandler();
	$userData = $jwt->validateToken($token);
	$userId = $jwt->getUserIdFromToken($token);
	
	if($userData === null){
		http_response_code(401);
		echo json_encode(['error' => $userData['message']]);
		exit();
	}
	
	//	Nutzerdaten aus DB holen
	//	Das koennte man doch aus auslagern, damit der Webserver
	//	hierrauf keinen Zugriff gibt ueber public?
	$stmt = $pdo->prepare("select first_name, surname from users where id = ?");
	$stmt->execute([$userId]);
	$user = $stmt->fetch();
	
	if(!$user){
		http_response_code(404);
		echo json_encode(['error' => 'Benutzer nicht gefunden']);
		exit();
	}
	
	echo json_encode([
		'user_id' => $userId,
		'surname' => $user['surname'],
		'first_name' => $user['first_name']
	]);
?>