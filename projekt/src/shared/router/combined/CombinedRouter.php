<?php
	namespace Shared\Router\Combined;
	
	require_once __DIR__ . '/../RouterInterface.php';
	require_once __DIR__ . '/CombinedRouterInterface.php';
	
	use Shared\Router\RouterInterface;
	
	/**
	 * Kombiniert mehrere Modulrouter zu einem gemeinsamen Einstiegspunkt.
	 */
	class CombinedRouter implements CombinedRouterInterface{
		/**
		 * Liste der registrierten Modulrouter, z.B. ['todo' => TodoRouter]
		 *
		 * @var array<string, RouterInterface>
		 */
		private array $routers = [];
		
		/**
		 * Registriert einen Modulrouter unter einem eindeutigen Schluessel.
		 */
		public function registerRouter(string $modulKey, RouterInterface $router): void{
			$this->router[$modulKey] = $router;
		}
		
		/**
		 * Leitet die Anfrage an alle registrierten Modulrouter weiter,
		 * bis einer von ihnen sie erfolgreich verarbeitet.
		 */
		public function dispatch(string $method, string $uri): void{
			foreach($this->routers as $router){
				$router->dispatch($method, $uri);
			}
		}
	}