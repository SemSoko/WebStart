<?php
	require_once '../../bootstrap/init.php';
	header('Content-Type: application/json');
	
	$pdo = Database::getConnection();
	
	if($_SERVER['REQUEST_METHOD'] !== 'POST'){
		//	Methode nicht erlaubt
		http_response_code(405);
		echo json_encode(['error' => 'Nur POST-Anfragen erlaubt']);
		exit();
	}
	
	//	JSON-Daten auslesen
	$input = json_decode(file_get_contents('php://input'), true);
	$titleTodo = $input['title'];
	
	if(!$titleTodo){
		http_response_code(400);
		echo json_encode(['error' => 'Todo-Titel muss angegeben werden']);
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
	
	$todoId = addTodo($pdo, $userId, $titleTodo);
	
	if($todoId !== null){
		//	Neues Todo aus DB lesen
		//	Mit allen dazugehoerenden Spalten
		$stmt = $pdo->prepare("select * from todos where id = ?");
		$stmt->execute([$todoId]);
		$newTodo = $stmt->fetch(PDO::FETCH_ASSOC);
		
		//	Feldnamenanpassen fuer Rendering
		$newTodo = [
			'todo_id' => $newTodo['id'],
			'todo_title' => $newTodo['title'],
			'todo_status' => $newTodo['is_done'],
			'todo_iat' => $newTodo['created_at']
		];
		
		//	Vollstaendiges Todo als JSON senden
		echo json_encode([
			'success' => true,
			'completeTodo' => $newTodo
		]);
	}else{
		echo json_encode([
			'success' => false,
			'message' => 'Fehler beim Hinzufuegen des Todos'
		]);
	}
?>