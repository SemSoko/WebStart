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
	$userTodos = getTodosByUser($pdo, $userId);
	
	if(!is_array($userTodos)){
		http_response_code(500);
		echo json_encode(['error' => 'Benutzer-Todos nicht gefunden']);
		exit();
	}
	
	$formattedTodos = [];
	
	foreach($userTodos as $todo){
		$formattedTodos[] = [
			'todo_id' => $todo['id'],
			'todo_title' => $todo['title'],
			'todo_status' => $todo['is_done'],
			'todo_iat' => $todo['created_at']
		];
	}
	
	echo json_encode($formattedTodos);
?>