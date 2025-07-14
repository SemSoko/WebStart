<?php
	namespace todoNew\Controller;

	require_once __DIR__ . '/../service/TodoServiceInterface.php';
	require_once __DIR__ . '/../../shared/response/ResponseHandlerInterface.php';
	
	require_once __DIR__ . '/../../shared/middleware/AuthMiddlewareInterface.php';
	require_once __DIR__ . '/../../shared/middleware/ValidationMiddlewareInterface.php';
	
	use Shared\Response\ResponseHandlerInterface;
	use Shared\Middleware\ValidationMiddlewareInterface;
	use Shared\Middleware\AuthMiddlewareInterface;
	use TodoNew\Service\TodoServiceInterface;

	/**
	 * Controller fuer Todo-Endpunkte.
	 *
	 * Verantwortlich fuer das Entgegennehmen der HTTP-Anfrage und
	 * Weitergabe an den Service.
	 */
	class TodoController{
		protected AuthMiddlewareInterface $auth;
		protected TodoServiceInterface $service;
		protected ValidationMiddlewareInterface $validation;
		protected ResponseHandlerInterface $response;
		
		/**
		 * Initialisiert den Controller mit einer Service-Instanz.
		 *
		 * @param TodoServiceInterface $service Service-Interface
		 */
		public function __construct(AuthMiddlewareInterface $auth,
									TodoServiceInterface $service,
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
			
			try{
				/*
				* Eingabefeld 'title' pruefen
				*/
				$titleTodo = $this->validation->requireField('title');
			}catch(\InvalidArgumentException $e){
				$this->response->error($e->getMessage(), 400);
				return;
			}
			
			// Weitergabe an Service
			$result = $this->service->addTodo($titleTodo, $userId);
			
			// Erfolg oder Fehler zurueckgeben
			/**
			 * @todo Bezeichnungen fuer das Feld: source in $result anpassen!!!
			 */
			// Schritt Nr. 1
			// Wurde success: false zurückgegeben?
			// Dann ist etwas schiefgelaufen – also müssen wir nicht erfolgreich
			// antworten, sondern Fehler behandeln.
			if(isset($result['success']) && $result['success'] === false){
				// Schritt Nr. 2
				// Woher kommt der Fehler?
				// Aus dem repository, dem service oder ist er nicht genau bekannt?
				$source = $result['source'] ?? '';
				
				// Schritt Nr. 3
				// Der Fehler kam aus der Datenbankschicht (z. B. INSERT fehlgeschlagen).
				// Also geben wir detaillierte Debug-Infos zurück.
				if($source === 'repository'){
					$this->response->debug('Repository-Fehler', $result);
				// Der Fehler passierte in der Logikschicht, z. B. ein ungültiger Titel
				// wurde trotzdem weitergegeben.
				}elseif($source === 'service'){
					$this->response->debug('Service-Fehler', $result);
				// Es gibt keinen source, oder wir wissen nicht woher der Fehler
				// kommt → also geben wir eine generische Fehlermeldung zurück.
				}else{
					$this->response->error($result['error'] ?? 'Unbekannter Fehler', 500);
				}
				
				return;
			}
			
			$this->response->success($result, 201);
		 }
	}
