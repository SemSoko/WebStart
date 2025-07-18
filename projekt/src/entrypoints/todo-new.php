<?php
	/**
	 * Einstiegspunkt fuer das todo-new Modul.
	 * Nutzt CombinedRouter, um den Modulrouter einzubinden.
	 */
	
	require_once __DIR__ . '/../../bootstrap/init.php';
	require_once __DIR__ . '/../todo-new/TodoModule.php';
	require_once __DIR__ . '/../todo-new/TodoRouter.php';
	require_once __DIR__ . '/../shared/router/combined/CombinedRouter.php';
	
	use Shared\ServiceContainer\Container;
	use TodoNew\TodoModule;
	use TodoNew\TodoRouter;
	use Shared\Router\Combined\CombinedRouter;
	
	/*
	 * 1. Ermittelt die HTTP-Methode der Anfrage (z.B.: GET, POST)
	 */
	$method = $_SERVER['REQUEST_METHOD'];
	
	/*
	 * 2. URI
	 */
	$uri = $_SERVER['REQUEST_URI'];
	
	/*
	 * 3. Erstellt DI-Container
	 */
	$container = new Container();
	
	/*
	 * 4. Registriert alle Services und Controller des Todo-Moduls
	 */
	TodoModule::register($container);
	
	/*
	 * 5. Modulrouter instanziieren
	 */
	$router = new TodoRouter($container);
	
	/*
	 * 6. CombinedRouter aufbauen & registrieren
	 * Kann man hier nicht ServiceIds nutzen oder neue Datei anegen fuer Consts-Vars-Module
	 */
	$combinedRouter = new CombinedRouter();
	$combinedRouter->registerRouter('todo-new', $todoRouter);
	
	/*
	 * 7. Dispatch ausfuehren
	 */
	$combinedRouter->dispatch($method, $uri);