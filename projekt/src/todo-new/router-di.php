<?php
	/**
	 * @deprecated
	 * Veralteter Einstiegspunkt für todo-new Modul.
	 * Wurde ersetzt durch `entrypoint/todo-new.php`, bleibt bis zur vollständigen
	 * Umstellung bestehen.
	 */
	require_once __DIR__ . '/../../bootstrap/init.php';
	require_once __DIR__ . '/TodoRouter.php';
	
	use Shared\ServiceContainer\Container;
	use TodoNew\TodoModule;
	use TodoNew\TodoRouter;
	
	/*
	 * Ermittelt die HTTP-Methode der Anfrage (z.B.: GET, POST)
	 */
	$method = $_SERVER['REQUEST_METHOD'];
	/*
	 * URI
	 */
	$uri = $_SERVER['REQUEST_URI'];
	
	/*
	 * Erstellt DI-Container
	 */
	$container = new Container();
	
	/*
	 * Registriert alle Services und Controller des Todo-Moduls
	 */
	TodoModule::register($container);
	
	/*
	 * Modul-Router ausfuehren.
	 */
	$router = new TodoRouter($container);
	$router->dispatch($method, $uri);