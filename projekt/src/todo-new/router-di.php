<?php
	require_once __DIR__ . '/../../bootstrap/init.php';

	/*
	 * DI-Container und notwendige Konfigurationen einbinden
	 */
	require_once __DIR__ . '/../shared/service-container/Container.php';
	require_once __DIR__ . '/../shared/service-container/ServiceIds.php';
	require_once __DIR__ . '/TodoModule.php';
	
	use Shared\ServiceContainer\Container;
	use Shared\ServiceContainer\ServiceIds;
	use TodoNew\TodoModule;
	
	/*
	 * Erstellt eine neue Instanz des DI-Containers
	 */
	$container = new Container();
	
	/*
	 * Registriert alle Services und Controller des Todo-Moduls
	 */
	TodoModule::register($container);
	
	/*
	 * Ermittelt die HTTP-Methode der Anfrage (z.B.: GET, POST)
	 */
	$method = $_SERVER['REQUEST_METHOD'];
	
	/*
	 * Extrahiert den Pfad aus der vollstaendigen Request-URI
	 */
	$requestUri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

	/*
	 * Routing-Konfiguration: Welche URI fuehrt zu welcher Controller-Methode?
	 * Diese Zuordnung ist statisch. Dynamische Pfade koennen spaeter ergaenzt werden.
	 */

	/**
	 * @todo
	 * Dynamische Pfade (z. B. /api/todo-new/{id}) und Middleware (z. B. Auth)) ergaenzen
	 */
	$routes = [
		'GET' => [
			'/api/todo-new' => [TodoController::class, 'getAll'],
			/*
			 * Sonderroute fuer Status-Check
			 */
			'/api/todo.php/status' => '__status__'
		],
		'POST' => [
			'/api/todo-new' => [TodoController::class, 'add'],
		],
		'PATCH' => [
			'/api/todo-new' => [TodoController::class, 'toggleStatus'],
		],
		'DELETE' => [
			'/api/todo-new' => [TodoController::class, 'delete'],
		]
	];
	
	/*
	 * Versucht, eine passende Route basierend auf Methode und Pfad zu finden.
	 */
	$matchedRoute = $routes[$method][$requestUri] ?? null;
	
	/*
	 * Falls keine passende Route existiert: Fehlermeldung mit Status 404.
	 */
	if(!$matchedRoute){
		$response = $container->get(ServiceIds::RESPONSE);
		$response->error('Route not found', 404);
	}
	
	/*
	 * Sonderfall: Status-Rueckmeldung, um die Erreichbarkeit zu pruefen.
	 */
	if($matchedRoute === '__status__'){
		$response = $container->get(ServiceIds::RESPONSE);
		$response->status('Todo-Modul erreichbar');
		exit();
	}

	/*
	 * Zerlegt die Route in Klasse und Methodenname
	 */
	[$controllerClass, $methodName] = $matchedRoute;
	
	/*
	 * Entscheidet anhand der Controller-Klasse, welche
	 * konkrete Instanz aus dem Container geladen werden soll.
	 */
	switch($controllerClass){
		/*
		 * Entscheidet, welcher Controller fuer die aktuelle Route
		 * verwendet werden soll.
		 */
		case TodoController::class:
			/*
			 * Holt den TodoController aus dem DI-Container.
			 */
			$controller = $container->get(ServiceIds::TODO_CONTROLLER);
			break;
		
		/*
		 * Sicherheitsmechanismus fuer nicht registrierte Controller-Klassen.
		 */
		default:
			$response = $container->get(ServiceIds::RESPONSE);
			$response->error('Unbekannter Controller', 500);
	}
	
	/*
	 * Fuehrt die dem Pfad zugewiesene Methode des Controllers aus.
	 */
	$controller->$methodName();