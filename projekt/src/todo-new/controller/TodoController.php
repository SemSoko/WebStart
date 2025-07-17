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
	 * Controller fuer die Todo-API.
	 *
	 * Entgegnet HTTP-Anfragen und delegiert die Verarbeitung an die
	 * Service-Schicht.
	 *
	 * Kuemmert sich um die Authentifizierung, Validierung und Auswahl
	 * der passenden Antwort.
	 *
	 * @package TodoNew\Controller
	 */
	class TodoController{
		/**
		 * Middleware zur Authentifizierungspruefung.
		 *
		 * @var AuthMiddlewareInterface
		 */
		protected AuthMiddlewareInterface $auth;
		
		/**
		 * Service zur Verwaltung der Geschaeftslogik rund um Todos.
		 *
		 * @var TodoServiceInterface
		 */
		protected TodoServiceInterface $service;
		
		/**
		 * Middleware zur Validierung eingehender Nutzdaten.
		 *
		 * @var ValidationMiddlewareInterface
		 */
		protected ValidationMiddlewareInterface $validation;
		
		/**
		 * Handler fuer strukturierte JSON-Antworten.
		 *
		 * @var ResponseHandlerInterface
		 */
		protected ResponseHandlerInterface $response;
		
		/**
		 * Initialisiert den Controller mit allen notwendigen Abhaengigkeiten.
		 *
		 * @param AuthMiddlewareInterface $auth Authentifizierungs-Middleware
		 * @param TodoServiceInterface $service Geschaeftslogik fuer Todos
		 * @param ValidationMiddlewareInterface $validation Validierungslogik fuer Eingaben
		 * @param ResponseHandlerInterface $response Ausgabeformatrierung
		 */
		public function __construct(
			AuthMiddlewareInterface $auth,
			TodoServiceInterface $service,
			ValidationMiddlewareInterface $validation,
			ResponseHandlerInterface $response
		){
			$this->auth = $auth;
			$this->service = $service;
			$this->validation = $validation;
			$this->response = $response;
		}
		 
		/**
		 * Fuegt ein neues Todo hinzu.
		 *
		 * Erwartet ein JSON-Objekt im HTTP-Body mit dem Feld "title", z.B.:
		 * {
		 *    "title": "Einkaufen"
		 * }
		 *
		 * Ablauf:
		 * - Authentifizierung ueber token_get_all
		 * - Pflichtfeldpruefung fuer "title"
		 * - Uebergabe an die Service-Schicht
		 * - Fehlerdifferenzierung anhand der Quelle (`repository`, `service` oder unbekannt)
		 * - Ausgabe einer passenden JSON-Antwort mit Statuscode
		 *
		 * @return void
		 * Gibt direkt eine JSON-Antwort zurueck und beendet die Ausfuehrung.
		 */
		 public function add(){
			$userId = $this->auth->handle();
			
			try{
				$titleTodo = $this->validation->requireField('title');
			}catch(\InvalidArgumentException $e){
				$this->response->error($e->getMessage(), 400);
				return;
			}
			
			$result = $this->service->addTodo($titleTodo, $userId);
			
			/**
			 * Erfolg oder Fehler zurueckgeben
			 * @todo: Bezeichnungen fuer das Feld "source" in $result vereinheitlichen
			 *
			
			/*
			 * 1. Fehlerfall: success = false
			 */
			if(isset($result['success']) && $result['success'] === false){
				/*
				 * 2. Fehlerquelle bestimmten (repository, service oder unbekannt)
				 */
				$source = $result['source'] ?? '';
				
				if($source === 'repository'){
					/*
					 * Fehler kommt aus der Datenbankschicht -> Debug-Antwort mit Details
					 */
					$this->response->debug('Repository-Fehler', $result);
				}elseif($source === 'service'){
					/*
					 * Fehler aus der Logikschicht -> ebenfalls Debug-Antwort
					 */
					$this->response->debug('Service-Fehler', $result);
					/*
					 * Fehlerquelle unbekannt oder nicht angegeben -> generische Fehlermeldung
					 */
				}else{
					$this->response->error($result['error'] ?? 'Unbekannter Fehler', 500);
				}
				
				return;
			}
			
			$this->response->success($result, 201);
		 }
	}
