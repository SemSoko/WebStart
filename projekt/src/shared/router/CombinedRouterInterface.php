<?php
	namespace Shared\Router;
	
	require_once __DIR__ . '/RouterInterface.php';
	
	use Shared\Router\RouterInterface;
	
	/**
	 * Definiert die Schnittstelle fuer zentrale Router, die mehrere
	 * Modul-Router buendeln.
	 */
	interface CombinedRouterInterface{
		/**
		 * Mountet einen Modul-Router unter einem bestimmten Basis-Pfad.
		 *
		 * Beispiel:
		 * $combinedRouter->mount('/api/todo', $todoRouter);
		 *
		 * @param string $basePath
		 * Pfad-Praefix, unter dem der Modul-Router erreichbar sein soll
		 * @param RouterInterface $router
		 * Der Modul-Router, der gebuendelt werden soll
		 */
		public function mount(string $basePath, RouterInterface $router): void;
		
		/**
		 * Fuehrt das Dispatching basierend auf HTTP-Methode und URI ueber
		 * alle eingebundenen Router aus.
		 *
		 * @param string $method HTTP-Methode der eingehenden Anfrage
		 * @param string $uri URI der eingehenden Anfrage
		 */
		public function dispatch(string $method, string $uri): void;
	}