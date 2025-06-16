<?php
	require_once __DIR__ . '/../service/TodoService.php';
	require_once __DIR__ . '/../../shared/response/Response.php';
	
	use Shared\Response\Response;
	
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
		 * Gibt eine strukturierte JSON-Antwort zurueck:
		 * - Erfolg: Response::success([...])
		 * - Fehler: Response::error(...) oder Response::debug(...)
		 *
		 * Hinweis zum Fehlerhandling:
		 * Das zurueckgelieferte $result-Array kann je nach Quelle
		 * folgende Form annehmen:
		 *
		 * - ['success' => true] -> Erfolgreich
		 * - ['success' => false, 'error' => ...] -> Bekannte Fehler
		 * - ['success' => false, 'error' => ..., 'source' => 'service']
		 * - ['success' => false, 'error' => ..., 'debug' => [...], 'source' => 'repository']
		 *
		 * Bei debug-Faellen wird die gesamte $result-Struktur an
		 * Response::debug uebergeben, um eine verschachtelte Darstellung
		 * fuer die Analyse zu ermoeglichen.
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
				Response::error('Todo-Titel muss angegeben werden', 400);
			}
			
			$titleTodo = trim($input['title']);
			
			// Weitergabe an Service
			$service = new TodoService();
			$result = $service->addTodo($titleTodo);
			
			// Erfolg oder Fehler zurueckgeben
			if($result['success'] === true){
				Response::success($result, 201);
			}elseif($result['success'] === false && ($result['source'] ?? '') === 'service'){
				Response::debug('Service-Fehler', $result);
			}elseif($result['success'] === false && ($result['source'] ?? '') === 'repository')){
				Response::debug('Repository-Fehler', $result);
			}else{
				Response::error(($result['error'] ?? 'Unbekannter Fehler'), 500);
			}
		 }
	}
