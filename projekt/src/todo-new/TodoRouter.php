<?php
	namespace TodoNew;
	
	require_once __DIR__ . '/../shared/router/RouterInterface.php';
	require_once __DIR__ . '/../shared/service-container/Container.php';
	require_once __DIR__ . '/../shared/service-container/ServiceIds.php';
	require_once __DIR__ . 'TodoModule.php';
	
	use Shared\Router\RouterInterface;
	use Shared\ServiceContainer\Container;
	use Shared\ServiceContainer\ServiceIds;
	use TodoNew\TodoModule;
	
	class TodoRouter implements RouterInterface{
		private array $routes = [];
		private Container $container;
		
		public function __construct(Container $container){
			$this->container = $container;
			
			/*
			 * Statische Routenregistrierung.
			 */
			$this->add('GET', '/api/todo-new', [TodoController::class, 'getAll']);
			$this->add('POST', '/api/todo-new', [TodoController::class, 'add']);
			$this->add('PATCH', '/api/todo-new', [TodoController::class, 'toggleStatus']);
			$this->add('DELETE', '/api/todo-new', [TodoController::class, 'delete']);
			
			/*
			 * Sonderroute (z.B.: Statuspruefung)
			 */
			$this->add('GET', '/api/todo.php/status', '__status__');
		}
		
		public function add(string $method, string $path, array|string $handler): void{
			$this->routes[$method][$path] = $handler;
		}
		
		public function dispatch(string $method, string $uri): void{
			$path = rtrim(parse_url($uri, PHP_URL_PATH), '/');
			$handler = $this->routes[$method][$path] ?? null;
			
			$response = $this->container->get(ServiceIds::RESPONSE);
			
			if(!$handler){
				$response->error('Route not found', 404);
				return;
			}
			
			if($handler === '__status__'){
				$response->status('Todo-Modul erreichbar');
				return;
			}
			
			[$controllerClass, $methodName] = $handler;
			
			$controller = match($controllerClass){
				TodoController::class => $this->container->get(ServiceIds::TODO_CONTROLLER),
				default => null
			};
			
			if(!$controller){
				$response->error('Unbekannter Controller', 500);
				return;
			}
			
			$controller->$methodName();
		}
	}