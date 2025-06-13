<?php
	require_once __DIR__ . '/../service/TodoService.php';
	
	/**
	 * Controller fuer Todo-Endpunkte.
	 *
	 * Verantwortlich fuer das Entgegennehmen der HTTP-Anfrage und
	 * Weitergabe an den Service.
	 */
	class TodoController{
		/**
		 * Fuegt ein neues Todo hinzu.
		 *
		 * Erwartet im Body: {"title: "..."}
		 * Gibt JSON-Antwort zurueck mit Erfolg oder Fehlermeldung.
		 *
		 * @return void
		 */
		 public function add(){
			/*
			 * Liest die JSON-Daten aus der Anfrage und extrahiert den Todo-Titel.
			 * Liest den Body der Anfrage (z. B. { "title": "Einkaufen" })
			 * Macht daraus ein PHP-Array
			 */
			$input = json_decode(file_get_contents('php://input'), true);
			
			/*
			 * Prueft, ob ein Todo-Titel uebergeben wurde.
			 * Gibt bei fehlendem Titel einen Fehler zurueck.
			 */
			if(!isset($input['title']) || empty(trim($input['title']))){
				http_response_code(400);
				echo json_encode([
					'error' => 'Todo-Titel muss angegeben werden'
				]);
				exit();
			}
			
			$titleTodo = trim($input['title']);
			
			// Weitergabe an Service
			$service = new TodoService();
			$result = $service->addTodo($titleTodo);
			
			// Erfolg oder Fehler zurueckgeben
			if($result['success']){
				http_response_code(201);
				echo json_encode([
				
				]);
			}else{
				http_response_code(500);
				echo json_encode([
					'error' => $result['error'] ?? 'Unbekannter Fehler'
				]);
			}
		 }
	}