<?php
	namespace todoNew\Controller;

	require_once __DIR__ . '/../service/TodoService.php';
	require_once __DIR__ . '/../../shared/response/ResponseHandlerInterface.php';
	
	require_once __DIR__ . '/../../shared/middleware/AuthMiddlewareInterface.php';
	require_once __DIR__ . '/../../shared/middleware/ValidationMiddlewareInterface.php';
	
	use Shared\Response\ResponseHandlerInterface;
	use Shared\Middleware\ValidationMiddlewareInterface;
	use Shared\Middleware\AuthMiddlewareInterface;

	/**
	 * Controller fuer Todo-Endpunkte.
	 *
	 * Verantwortlich fuer das Entgegennehmen der HTTP-Anfrage und
	 * Weitergabe an den Service.
	 */
	class TodoController{
		protected AuthMiddlewareInterface $auth;
		protected TodoService $service;
		protected ValidationMiddlewareInterface $validation;
		protected ResponseHandlerInterface $response;
		
		/**
		 * Initialisiert den Controller mit einer Service-Instanz.
		 *
		 * @param TodoService $service Service-Schicht zur Verarbeitung
		 */
		public function __construct(AuthMiddlewareInterface $auth, TodoService $service,
									ValidationMiddlewareInterface $validation,
									ResponseHandlerInterface $response){
			$this->auth = $auth;
			$this->service = $service;
			$this->validation = $validation;
			$this->response = $response;
		}
		 
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
			 * Zugriffsschutz sofort pruefen.
			 * handle() nur in Methoden aufrufen, die Schutz brauchen.
			 */
			$userId = $this->auth->handle();
			
			/*
			 * Eingabefeld 'title' pruefen
			 */
			$titleTodo = $this->validation->requireField('title');
			
			// Weitergabe an Service
			$result = $this->service->addTodo($titleTodo, $userId);
			
			// Erfolg oder Fehler zurueckgeben
			/**
			 * @todo Bezeichnungen fuer das Feld: source in $result anpassen!!!
			 */
			if($result['success'] === true){
				$this->response->success($result, 201);
			}elseif($result['success'] === false && ($result['source'] ?? '') === 'service'){
				$this->response->debug('Service-Fehler', $result);
			}elseif($result['success'] === false && ($result['source'] ?? '') === 'repository'){
				$this->response->debug('Repository-Fehler', $result);
			}else{
				$this->response->error(($result['error'] ?? 'Unbekannter Fehler'), 500);
			}
		 }
	}
