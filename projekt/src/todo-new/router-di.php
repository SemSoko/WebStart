<?php
	// Init einbinden
	require_once __DIR__ . '/../../bootstrap/init.php';

	// Controller einbinden
	require_once __DIR__ . '/controller/TodoController.php';
	// Service einbinden
	require_once __DIR__ . '/service/TodoService.php';
	// Repository einbinden
	require_once __DIR__ . '/repository/TodoRepository.php';
	
	require_once __DIR__ . '/../shared/response/JsonResponseHandler.php';
	require_once __DIR__ . '/../shared/http/DefaultInputProvider.php';
	
	require_once __DIR__ . '/../shared/middleware/AuthMiddleware.php';
	require_once __DIR__ . '/../shared/validation/JsonFieldValidator.php';
	
	
	use Shared\Response\JsonResponseHandler;
	use Shared\Http\JsonFieldValidator;
	use Shared\Auth\JwtHandlerNew;
	use Shared\Validation\JsonFieldValidator;
	use todoNew\Service\TodoService;
	
	// HTTP-Methode (z. B. GET, POST, ...)
	
	// $_SERVER
	// $_SERVER['REQUEST_URI'] /api/todo-new/router.php?title=Einkaufen
	// parse_url(..., PHP_URL_PATH) /api/todo-new/router.php
	// $_SERVER['HTTP_HOST'] example.de
	// $_SERVER['HTTPS'] on oder leer
	// $_SERVER['SERVER_PROTOCOL'] HTTP/1.1
	$method = $_SERVER['REQUEST_METHOD'];
	
	// Pfad extrahieren
	// Was ist $_SERVER['REQUEST_URI']?
	// Das ist der komplette Pfad, den der Browser beim Aufruf der Seite mitgibt.
	// Beispiel:
	// POST /api/todo-new/router.php?title=Einkaufen
	// Dann ist:
	// $_SERVER['REQUEST_URI'] = "/api/todo-new/router.php?title=Einkaufen"
	
	// Was macht parse_url(..., PHP_URL_PATH)?
	// parse_url() nimmt eine URL und gibt dir nur den Pfad zurück, also ohne Parameter.
	// Beispiel:
	// $requestUri = parse_url('/api/todo-new/router.php?title=Einkaufen', PHP_URL_PATH);
	// Ergebnis:
	// $requestUri = "/api/todo-new/router.php"
	// So kannst du genau den Teil vergleichen, der in deinem Routing-Array steht.
	$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	// Um es spaeter besser nachvollziehen zu koennen.
	// echo('<br>'.$requestUri.'<br>');
	
	// Routing-Tabelle definieren
	/**
	 * @todo
	 * Dynamische Pfade (z. B. /api/todo-new/{id}) und Middleware (z. B. Auth)) ergaenzen
	 */
	$routes = [
		'GET' => [
			'/api/todo-new' => [TodoController::class, 'getAll'],
			// Zukunft: 'api/todo-new/{id}' => [TodoController::class, 'getById']
			'/api/todo.php/status' => '__status__'
		],
		'POST' => [
			'/api/todo-new' => [TodoController::class, 'add'],
		],
		'PATCH' => [
			'/api/todo-new' => [TodoController::class, 'toggleStatus'],
		],
		'DELETE' => [
			'api/todo-new' => [TodoController::class, 'delete'],
		]
	];
	
	/**
	 * Globale Instanzierung des Response-Handlers
	 *
	 * Diese Instanz wird zentral verwendet, um konsistente JSON-Antworten
	 * ueber alle Schichten hinweg (Middleware, Controller, Fehlerfaelle im
	 * Router) sicherzustellen.
	 *
	 * Vorteile:
	 * - Vermeidung mehrfacher Instanzierung
	 * - Einheitliches Format fuer Fehler- und Statusantworten
	 * - Ermoeglicht Format fuer Fehler- und Statusantworten
	 * - Ermoeglicht spaetere Erweiterungen (z.B. Logging, Tracing) an einer Stelle
	 */
	$response = new JsonResponseHandler();
	// Route suchen
	$matchedRoute = $routes[$method][$requestUri] ?? null;
	
	// 404 ist ein Internet-Standard für: Nicht gefunden
	/**
	 * Gibt eine standardisierte Fehlermeldung aus, wenn keine passende Route
	 * gefunden wurde.
	 *
	 * Beispielantwort:
	 * {
	 *    'error': 'Route not found'
	 * }
	 *
	 * HTTP-Status: 404 Not Found
	 */
	if(!$matchedRoute){
		$response->error('Route not found', 404);
	}
	
	// Sonderfall: Status-Pruefung
	if($matchedRoute === '__status__'){
		$response->status('Todo-Modul erreichbar');
	}
	
	// Controller aufrufen
	// Stell dir $matchedRoute vor wie eine kleine Kiste mit zwei Dingen:
	// Jetzt holst du das raus und sagst:
	// $controllerClass = 'TodoController'; und $methodName = 'add';
	//[$controllerClass, $methodName] = $matchedRoute;
	// $controller = new $controllerClass();
	// Das heißt: $controller = new TodoController();
	// Du erstellst eine Instanz der Klasse. So kannst du Funktionen der Klasse benutzen.
	// $controller = new $controllerClass();
	// Das ist wie sagen: $controller->add();
	// Du rufst die Methode auf, die du aus dem Routing bekommen hast.
	// $controller->$methodName();
	// Wenn das Routing sagt "POST /api/todo-new -> TodoController::add",
	// dann wird genau diese Methode automatisch aufgerufen.
	
	[$controllerClass, $methodName] = $matchedRoute;
	
	switch($controllerClass){
		case TodoController::class:
		    /**
		     * Dependency Injection fuer TodoController und seine Abhaengigkeiten
		     *
			 * Die Kette folgt den Prinzipien von:
			 * - Clean Architecture (Low-Level zu High-Level -> Controller aggregiert alles)
			 * - Dependency Inversion Principle (DIP) -> Controller hängt nur von Interfaces ab
			 * - Single Responsibility Principle (SRP) -> jede Klasse macht genau eine Sache
			 *
		     * Schrittweise Erstellung und Verkabelung der Abhaengigkeiten:
		     *
		     * ┌───────────────────────────────┐
		     * │            ROUTER             │
		     * └───────────────────────────────┘
		     *                │
		     * 1. Datenbankverbindung (PDO)
		     * - Wird benoetigt, um SQL-basierte Repositories zu initialisieren.
		     */
			$pdo = Database::getConnection(); // kommt aus der init.php
			
		    /**
		     * 2. Repository-Schicht
		     * - Verwaltet den Zugriff auf die persistierten Todos (CRUD).
		     * - Erwartet ein PDO-Objekt fuer DB-Zugriffe.
		     */
			$repository = new TodoRepository($pdo);
		
		    /**
		     * 3. Service-Schicht (Business-Logik)
		     * - Kapselt fachliche Regeln (z.B. keine leeren Titel).
		     * - Arbeitet mit dem Repository zur Datenhaltung.
		     */
			$service = new TodoService($repository);
			
		    /**
		     * 5. Authentifizierungs-Service (Low-Level)
		     * - Implementiert AuthServiceInterface.
		     * - Stellt Methoden zur Token-Verarbeitung (z.B. JWT-Validierung) bereit.
		     */
			$authService = new JwtHandlerNew();
		    /**
		     * 6. AuthMiddleware (High-Level)
		     * - Schutz von Routen durch Pruefung eines gueltigen Tokens.
		     * - Erwartet einen AuthService + ResponseHandler
		     */
			$auth = new AuthMiddleware($authService, $response);
			
			$inputProvider = new DefaultInputProvider();
			
		    /**
		     * 7. Eingabevalidierung (Low-Level)
		     * - Prueft JSON-Felder auf Vorhandensein und Gueltigkeit.
		     * - Implementiert FieldValidatorInteface.
		     */
			$validator = new JsonFieldValidator();
		    /**
		     * 8. ValidationMiddleware (High-Level)
		     * - Fuehrt Pflichtfeldpruefungen aus.
		     * - Verwendet FieldValidatorInterface + ResponseHandler zur Ausgabe
		     * von Fehler.
		     */
			$validation = new ValidationMiddleware($validator, $response, $inputProvider);
		
		    /**
		     * 9. Konstruktion des Controllers
		     * - Entgegennahme aller uebergeordneten Komponenten.
		     * - Der Controller selbst steuert die Ablaufkette und koordiniert
		     * Middleware, Service & Response.
		     */
			$controller = new TodoController($auth, $service, $validation, $response);
			break;
		
		default:
			$response->error('Unbekannter Controller', 500);
	}
	
	$controller->$methodName();