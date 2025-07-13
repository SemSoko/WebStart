<?php
	namespace TodoNew;
	
	// Db einbinden: Database
	require_once '/../core/db.php';
	// Repository einbinden
	require_once __DIR__ . '/repository/TodoRepository.php';
	// Service einbinden
	require_once __DIR__ . '/service/TodoService.php';
	// JwtHandlerNew einbinden
	require_once __DIR__ . '/../shared/auth/JwtHandler-new.php';
	// AuthMiddleware einbinden
	require_once __DIR__ . '/../shared/middleware/AuthMiddleware.php';
	// DefaultInputProvider einbinden
	require_once __DIR__ . '/../shared/http/DefaultInputProvider.php';
	// JsonFieldValidator einbinden
	require_once __DIR__ . '/../shared/validation/JsonFieldValidator.php';
	// ValidationMiddleware einbinden
	require_once __DIR__ . '/../shared/middleware/ValidationMiddleware.php';
	// Controller einbinden
	require_once __DIR__ . '/controller/TodoController.php';
	// JsonResponseHandler einbinden
	require_once __DIR__ . '/../shared/response/JsonResponseHandler.php';
	// Container einbinden
	require_once __DIR__ . '/../shared/service-container/Container.php';
	// ServiceIDs einbinden
	require_once __DIR__ . '/../shared/service-container/ServiceIds.php';
	
	use todoNew\Repository\TodoRepository;
	use todoNew\Service\TodoService;
	use Shared\Auth\JwtHandlerNew;
	use Shared\Middleware\AuthMiddleware;
	use Shared\Http\DefaultInputProvider;
	use Shared\Validation\JsonFieldValidator;
	use Shared\Middleware\ValidationMiddleware;
	use todoNew\Controller\TodoController;
	use Shared\ServiceContainer\Container;
	
	use Shared\ServiceContainer\ServiceIds;
	
	class TodoModule{		
		/*
		 * Static ist ok weil:
		 *	Du arbeitest mit:
		 *	Expliziter Konfiguration
		 *	Kompositionswurzeln (Composition Root = Container + Module)
		 *	Testbarer, zustandsloser Architektur
		 *	Kein Singleton, keine globale State-Objekte
		 *
		 *	static hier bricht nicht Clean Code / SOLID, weil:
		 *	Es ist keine Geschäftslogik.
		 *
		 *	Es ist keine Domain-Klasse, sondern Setup-Helfer.
		 *
		 *	Du nutzt TodoModule::register($container) explizit in deiner Bootstrap-Phase.
		 *
		 *	Du instanziierst deine eigentlichen Klassen über den Container (nicht über das Module!).
		 *
		 *	Stell dir TodoModule vor wie ein Bauplan, nicht wie ein laufendes Objekt.
		 *	Das static bedeutet: „Ich brauche kein Objekt – ich sage nur, wie es zusammengebaut wird.“
		 *
		 *	Wenn du später auf Autowiring umsteigen willst (Reflection, Symfony-like),
		 *	kannst du das jederzeit ersetzen – diese register()-Calls sind dann optional.
		 *
		 *	Fazit: static hier = ist testbar, ist klar, ist SOLID-konform
		 *	Du verlierst keine Modularität oder Testbarkeit.
		*/
		public static function register(Container $container): void{
			// ResponseHandler
			// 0.
			$container->register(ServiceIds::RESPONSE, fn() => new JsonResponseHandler());
			
			// Database Connection
			// 1. Datenbankverbindung (PDO)
		    // - Wird benoetigt, um SQL-basierte Repositories zu initialisieren.
			$container->register(ServiceIds::PDO, fn() => Database::getConnection());
			
			// Repository
			// 2. Repository-Schicht
		    // - Verwaltet den Zugriff auf die persistierten Todos (CRUD).
		    // - Erwartet ein PDO-Objekt fuer DB-Zugriffe.
			$container->register(ServiceIds::TODO_REPO, fn(Container $c) =>
				new TodoRepository($c->get(ServiceIds::PDO))
			);
			
			// Service
			// 3. Service-Schicht (Business-Logik)
		    // Kapselt fachliche Regeln (z.B. keine leeren Titel).
		    // Arbeitet mit dem Repository zur Datenhaltung.
			$container->register(ServiceIds::TODO_SERVICE, fn(Container $c) =>
				new TodoService($c->get(ServiceIds::TODO_REPO))
			);
			
			
			// JwtHandlerNew
			// 4. Authentifizierungs-Service (Low-Level)
		    // - Implementiert AuthServiceInterface.
		    // - Stellt Methoden zur Token-Verarbeitung (z.B. JWT-Validierung) bereit.
			$container->register(ServiceIds::AUTH_SERVICE, fn() => new JwtHandlerNew());
			
			// Middleware
		    // 5. AuthMiddleware (High-Level)
		    // - Schutz von Routen durch Pruefung eines gueltigen Tokens.
		    // - Erwartet einen AuthService + ResponseHandler
			$container->register(ServiceIds::AUTH_MIDDLEWARE, fn(Container $c) =>
				new AuthMiddleware($c->get(ServiceIds::AUTH_SERVICE), $c->get(ServiceIds::RESPONSE))
			);
			
			// DefaultInputProvider
			// 6.
			$container->register(ServiceIds::INPUT, fn() => new DefaultInputProvider());
			
			// FieldValidator
		    // 7. Eingabevalidierung (Low-Level)
		    // - Prueft JSON-Felder auf Vorhandensein und Gueltigkeit.
		    // - Implementiert FieldValidatorInteface.
			$container->register(ServiceIds::VALIDATOR, fn() => JsonFieldValidator());
			
			// ValidationMiddleware
		    // 8. ValidationMiddleware (High-Level)
		    // - Fuehrt Pflichtfeldpruefungen aus.
		    // - Verwendet FieldValidatorInterface + ResponseHandler zur Ausgabe
		    //   von Fehler.
			$container->register(ServiceIds::VALIDATION_MIDDLEWARE, fn(Container $c) =>
				new ValidationMiddleware($c->get(ServiceIds::VALIDATOR),
										$c->get(ServiceIds::RESPONSE),
										$c->get(ServiceIds::INPUT))
			);
			
			// Controller
		    // 9. Konstruktion des Controllers
		    // - Entgegennahme aller uebergeordneten Komponenten.
		    // - Der Controller selbst steuert die Ablaufkette und koordiniert
		    //   Middleware, Service & Response.
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