<?php
	namespace TodoNew;
	
	/*
	 * Import aller notwendigen Klassen (Datenbank, Services, Middleware, etc.)
	 */
	require_once __DIR__ . '/../core/db.php';
	require_once __DIR__ . '/repository/TodoRepository.php';
	require_once __DIR__ . '/service/TodoService.php';
	require_once __DIR__ . '/../shared/auth/JwtHandler-new.php';
	require_once __DIR__ . '/../shared/auth/JwtAuthService.php';
	require_once __DIR__ . '/../shared/middleware/AuthMiddleware.php';
	require_once __DIR__ . '/../shared/http/DefaultInputProvider.php';
	require_once __DIR__ . '/../shared/http/RequestTokenReader.php';
	require_once __DIR__ . '/../shared/validation/JsonFieldValidator.php';
	require_once __DIR__ . '/../shared/middleware/ValidationMiddleware.php';
	require_once __DIR__ . '/controller/TodoController.php';
	require_once __DIR__ . '/../shared/response/JsonResponseHandler.php';
	require_once __DIR__ . '/../shared/service-container/Container.php';
	require_once __DIR__ . '/../shared/service-container/ServiceIds.php';
	
	use todoNew\Repository\TodoRepository;
	use todoNew\Service\TodoService;
	use Shared\Auth\JwtHandlerNew;
	use Shared\Middleware\AuthMiddleware;
	use Shared\Http\DefaultInputProvider;
	use Shared\Validation\JsonFieldValidator;
	use Shared\Middleware\ValidationMiddleware;
	use todoNew\Controller\TodoController;
	use Shared\Response\JsonResponseHandler;
	use Shared\ServiceContainer\Container;
	use Shared\Http\RequestTokenReader;
	use Shared\Auth\JwtAuthService;
	use Shared\ServiceContainer\ServiceIds;
	use \Database;
	
	/**
	 * Modul-Setup fuer todo-new.
	 *
	 * Diese Klasse registriert alle benoetigten Komponenten.
	 * (Datenbank, Services, Middleware, Controller) im DI-Container.
	 *
	 * Wird explizit vom Router aufgerufen: TodoModule::register($container)
	 *
	 * @package TodoNew
	 */
	class TodoModule{		
		/**
		 * Registriert alle Services und Komponenten des Todo-Moduls
		 * im Dependency-Injection-Container.
		 *
		 * @param Container $container Der zentrale Service-Container
		 */
		public static function register(Container $container): void{
			/*
			 * 0. Response-Handler (JSON-basierte HTTP-Antworten).
			 */
			$container->register(ServiceIds::RESPONSE, fn() => new JsonResponseHandler());
			
			/*
			 * 1. PDO-Datenbankverbindung (zentrale Grundlage fuer SQL-Repositories).
			 */
			$container->register(ServiceIds::PDO, fn() => Database::getConnection());
			
			/*
			 * 2. Repository (verwaltet den DB-Zugriff fuer Todos).
			 */
			$container->register(ServiceIds::TODO_REPO, fn(Container $c) =>
				new TodoRepository($c->get(ServiceIds::PDO))
			);
			
			/*
			 * 3. Service-Schicht (Geschaeftslogik rund um Todos).
			 */
			$container->register(ServiceIds::TODO_SERVICE, fn(Container $c) =>
				new TodoService($c->get(ServiceIds::TODO_REPO))
			);
			
			/*
			 * 4. Token-Leser (extrahiert Token aus HTTP-Request-Header).
			 */
			$container->register(ServiceIds::REQUEST_TOKEN_READER, fn() => new RequestTokenReader);
			
			/*
			 * 5. Low-Level Auth (JWT-Verarbeitung).
			 */
			$container->register(ServiceIds::JWT_HANDLER, fn() => new JwtHandlerNew());
			
			/*
			 * 6. AuthService (baut auf JWT-Handler auf, prueft Token-Gueltigkeit).
			 */
			$container->register(ServiceIds::AUTH_SERVICE, fn(Container $c) =>
				new JwtAuthService($c->get(ServiceIds::JWT_HANDLER))
			);
			
			/*
			 * 7. Middleware: Zugriffsschutz ueber Token-Pruefung.
			 */
			$container->register(ServiceIds::AUTH_MIDDLEWARE, fn(Container $c) =>
				new AuthMiddleware($c->get(ServiceIds::AUTH_SERVICE),
									$c->get(ServiceIds::RESPONSE),
									$c->get(ServiceIds::REQUEST_TOKEN_READER))
			);
			
			/*
			 * 8. Eingabeverarbeitung (liest JSON aus HTTP-Body).
			 */
			$container->register(ServiceIds::INPUT, fn() => new DefaultInputProvider());
			
			/*
			 * 9. Feldvalidierung (prueft JSON-Felder auf Gueltigkeit).
			 */
			$container->register(ServiceIds::VALIDATOR, fn() => new JsonFieldValidator());
			
			/*
			 * 10. Middleware: Pflichtfeld-Validierung.
			 */
			$container->register(ServiceIds::VALIDATION_MIDDLEWARE, fn(Container $c) =>
				new ValidationMiddleware($c->get(ServiceIds::VALIDATOR),
										$c->get(ServiceIds::RESPONSE),
										$c->get(ServiceIds::INPUT))
			);
			
			/*
			 * Controller fuer /api/todo-new
			 */
			$container->register(ServiceIds::TODO_CONTROLLER, fn(Container $c) =>
				new TodoController(
					$c->get(ServiceIds::AUTH_MIDDLEWARE),
					$c->get(ServiceIds::TODO_SERVICE),
					$c->get(ServiceIds::VALIDATION_MIDDLEWARE),
					$c->get(ServiceIds::RESPONSE)
				)
			);
		}
	}