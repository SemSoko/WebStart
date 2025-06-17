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
		 * Erwartet im HTTP-Body eine JSON-Struktur mit dem Feld "title".
		 * Beispiel: { "title": "Einkaufen" }
		 *
		 * Die Methode verarbeitet die Anfrage, validiert Eingabedaten,
		 * uebergibt den Titel an den Service und leitet das Ergebnis
		 * an die passende Response-Methode weiter.
		 *
		 * Fehlerhafte oder unvollstaendige Eingaben werden direkt mit
		 * Response::error() beantwortet.
		 *
		 * Abhaengig von der Quelle des Fehlers nutzt der Controller:
		 * - Response::success() bei Erfolg
		 * - Response::debug() bei Service-/Repository-Fehlern
		 * - Response::error() bei sonstigen Fehler
		 *
		 * @return
		 * void Gibt direkt eine JSON-Antwort an den Client aus und beendet
		 * die Ausfuehrung.
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
			/**
			 * @todo Bezeichnungen fuer das Feld: source in $result anpassen!!!
			 */
			if($result['success'] === true){
				Response::success($result, 201);
			}elseif($result['success'] === false && ($result['source'] ?? '') === 'service'){
				Response::debug('Service-Fehler', $result);
			}elseif($result['success'] === false && ($result['source'] ?? '') === 'repository'){
				Response::debug('Repository-Fehler', $result);
			}else{
				Response::error(($result['error'] ?? 'Unbekannter Fehler'), 500);
			}
		 }
	}
