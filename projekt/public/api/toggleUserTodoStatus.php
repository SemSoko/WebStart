<?php
	require_once __DIR__ . '/../../bootstrap/init.php';
	header('Content-Type: application/json');
	
	$pdo = Database::getConnection();
	
	if($_SERVER['REQUEST_METHOD'] !== 'PATCH'){
		//	Methode nicht erlaubt
		http_response_code(405);
		echo json_encode(['error' => 'Nur PATCH-Anfragen erlaubt']);
		exit();
	}
	
	//	JSON-Daten auslesen
	$input = json_decode(file_get_contents('php://input'), true);
	$todoId = $input['id'] ?? '';
	
	if(!$todoId){
		http_response_code(400);
		echo json_encode(['error' => 'Todo-ID erforderlich']);
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
		echo json_encode(['error' => 'Token nicht gueltig oder abgelaufen']);
		exit();
	}
	
	//	Toggle-Funktion ruft Status zurueck
	$todoStatus = toggleTodo($pdo, $userId, $todoId);
	
	if($todoStatus === 1 || $todoStatus === 0){
		// Aktualisiertes vollstaendiges Todo
		$stmt = $pdo->prepare("select * from todos where id = ?");
		$stmt->execute([$todoId]);
		$todoStatus = $stmt->fetch(PDO::FETCH_ASSOC);
		
		// Feldnamenanpassen fuer Rendering
		$todoStatus = [
			'todo_id' => $todoStatus['id'],
			'todo_title' => $todoStatus['title'],
			'todo_status' => $todoStatus['is_done'],
			'todo_iat' => $todoStatus['created_at'],
		];
		
		// Vollstaendiges Todo senden
		echo json_encode([
			'succes' => true,
			'completeTodo' => $todoStatus
		]);
	}else{
		http_response_code(401);
		echo json_encode(['error' => 'Status wurde nicht geaendert']);
		exit();
	}
	
?>