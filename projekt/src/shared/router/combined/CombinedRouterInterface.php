<?php
	namespace Shared\Router\Combined;
	
	require_once __DIR__ . '/../RouterInterface.php';
	
	use Shared\Router\RouterInterface;
	
	/**
	 * Kombiniert mehrere Modulrouter zu einem gemeinsamen Einstiegspunkt.
	 * Jeder Router verabeitet seine eigenen Pfade isoliert.
	 */
	interface CombinedRouterInterface{
		/**
		 * Fuehrt Routing-Logik aus, indem alle registrierten
		 * Modulrouter durchlaufen werden.
		 *
		 * @param string $method Die HTTP-Methode (z.B.: GET, POST)
		 * @param string $uri Die angefragte URI (z.B.: /api/todo-new)
		 */
		public function dispatch(string $method, string $uri): void;
		
		/**
		 * Fuegt einen Modulrouter hinzu.
		 *
		 * @param string $moduleKey Eindeutiger Bezeichner fuer das Modul.
		 * @param RouterInterface $router Der zu registrierende Modulrouter.
		 */
		public function registerRouter(string $moduleKey, RouterInterface $router): void;
	}