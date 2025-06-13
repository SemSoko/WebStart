<?php
	// Controller einbinden
	require_once __DIR__ . '/controller/TodoController.php';
	
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
	
	// Route suchen
	$matchedRoute = $routes[$method][$requestUri] ?? null;
	
	// Kein Treffer
	// 404 ist ein Internet-Standard für: Nicht gefunden
	if(!$matchedRoute){
		http_response_code(404);
		echo json_encode(['error' => 'Route not found']);
		exit();
	}
	
	// Controller aufrufen
	// Stell dir $matchedRoute vor wie eine kleine Kiste mit zwei Dingen:
	// Jetzt holst du das raus und sagst:
	// $controllerClass = 'TodoController'; und $methodName = 'add';
	[$controllerClass, $methodName] = $matchedRoute;
	// $controller = new $controllerClass();
	// Das heißt: $controller = new TodoController();
	// Du erstellst eine Instanz der Klasse. So kannst du Funktionen der Klasse benutzen.
	$controller = new $controllerClass();
	// Das ist wie sagen: $controller->add();
	// Du rufst die Methode auf, die du aus dem Routing bekommen hast.
	$controller->$methodName();
	// Wenn das Routing sagt "POST /api/todo-new -> TodoController::add",
	// dann wird genau diese Methode automatisch aufgerufen.