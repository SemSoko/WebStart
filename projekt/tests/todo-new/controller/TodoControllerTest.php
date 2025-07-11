<?php
	require_once __DIR__ . '/../../base/IntegrationTestCase.php';
	require_once __DIR__ . '/../../../src/todo-new/controller/TodoController.php';
	
	require_once __DIR__ . '/../../../src/shared/middleware/AuthMiddlewareInterface.php';
	require_once __DIR__ . '/../../../src/shared/middleware/ValidationMiddlewareInterface.php';
	require_once __DIR__ . '/../../../src/shared/response/ResponseHandlerInterface.php';
	
	use Shared\Middleware\AuthMiddlewareInterface;
	use Shared\Middleware\ValidationMiddlewareInterface;
	use Shared\Response\ResponseHandlerInterface;
	use todoNew\Controller\TodoController;
	use todoNew\Service\TodoService;
	use Tests\Base\IntegrationTestCase;
	
	class TodoControllerTest extends IntegrationTestCase{
		
		protected function createSchema(): void{
			$this->pdo->exec("
				create table users(
					id integer primary key autoincrement,
					email varchar(255) unique not null,
					password varchar(255) not null,
					created_at timestamp default current_timestamp
				);
			");
			
			$this->pdo->exec("
				CREATE TABLE todos(
					id INTEGER PRIMARY KEY AUTOINCREMENT,
					user_id INT NOT NULL,
					title VARCHAR(255) NOT NULL,
					is_done BOOLEAN DEFAULT FALSE,
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
				);
			");
		}
		
		protected function seedTestData(): void{
			$stmt = $this->pdo->prepare("insert into users (email, password) values (?, ?)");
			$stmt->execute(['test@test.de', password_hash('EinPasswort123-', PASSWORD_DEFAULT)]);
		}
		
		public function testAddCallsSuccessReponseOnValidInput(): void{
			$mockAuth = $this->createMock(AuthMiddlewareInterface::class);
			$mockAuth->method('handle')->willReturn(1);
			
			$mockValidation = $this->createMock(ValidationMiddlewareInterface::class);
			$mockValidation->method('requireField')->willReturn('Einkaufen');
			
			$mockService = $this->createMock(TodoService::class);
			$mockService->method('addTodo')->willReturn(['success' => true]);
			
			$mockResponse = $this->createMock(ResponseHandlerInterface::class);
			$mockResponse->expects($this->once())
				->method('success')
				->with(['success' => true], 201);
			
			$controller = new TodoController($mockAuth, $mockService,
									$mockValidation, $mockResponse);
			
			$controller->add();
		}
		
		public function testAddReturnsErrorResponseOnMissingTitle(): void{
			$mockAuth = $this->createMock(AuthMiddlewareInterface::class);
			$mockAuth->method('handle')->willReturn(1);
			
			$mockValidation = $this->createMock(ValidationMiddlewareInterface::class);
			$mockValidation->method('requireField')->willThrowException(new InvalidArgumentException('title fehlt'));
			
			$mockService = $this->createMock(TodoService::class);
			$mockService->expects($this->never())->method('addTodo');
			
			$mockResponse = $this->createMock(ResponseHandlerInterface::class);
			$mockResponse->expects($this->once())
				->method('error')
				->with('title fehlt', 400);
			
			$controller = new TodoController(
				$mockAuth,
				$mockService,
				$mockValidation,
				$mockResponse
			);
			
			$controller->add();
		}
		
		public function testAddReturnsDebugResponseOnServiceError(): void{
			$mockAuth = $this->createMock(AuthMiddlewareInterface::class);
			$mockAuth->method('handle')->willReturn(1);
			
			$mockValidation = $this->createMock(ValidationMiddlewareInterface::class);
			$mockValidation->method('requireField')->willReturn('Test-Todo');
			
			$mockService = $this->createMock(TodoService::class);
			$mockService->method('addTodo')->willReturn([
				'success' => false,
				'error' => 'Service failed',
				'source' => 'service'
			]);
			
			$mockResponse = $this->createMock(ResponseHandlerInterface::class);
			$mockResponse->expects($this->once())
				->method('debug')
				->with('Service-Fehler', [
					'success' => false,
					'error' => 'Service failed',
					'source' => 'service'
				]);
			
			$controller = new TodoController(
				$mockAuth,
				$mockService,
				$mockValidation,
				$mockResponse
			);
			
			$controller->add();
		}
		
		public function testAddReturnsDebugResponseOnRepositoryError(): void{
			$mockAuth = $this->createMock(AuthMiddlewareInterface::class);
			$mockAuth->method('handle')->willReturn(1);
			
			$mockValidation = $this->createMock(ValidationMiddlewareInterface::class);
			$mockValidation->method('requireField')->willReturn('Test-Todo');
			
			$mockService = $this->createMock(TodoService::class);
			$mockService->method('addTodo')->willReturn([
				'success' => false,
				'error' => 'Insert failed',
				'source' => 'repository'
			]);
			
			$mockResponse = $this->createMock(ResponseHandlerInterface::class);
			$mockResponse->expects($this->once())
				->method('debug')
				->with('Repository-Fehler', [
					'success' => false,
					'error' => 'Insert failed',
					'source' => 'repository'
				]);
			
			$controller = new TodoController(
				$mockAuth,
				$mockService,
				$mockValidation,
				$mockResponse
			);
			
			$controller->add();
		}
		
		public function testAddReturnsGenericErrorResponseOnUnknownError(): void{
			$mockAuth = $this->createMock(AuthMiddlewareInterface::class);
			$mockAuth->method('handle')->willReturn(1);
			
			$mockValidation = $this->createMock(ValidationMiddlewareInterface::class);
			$mockValidation->method('requireField')->willReturn('Test-Todo');
			
			$mockService = $this->createMock(TodoService::class);
			$mockService->method('addTodo')->willReturn([
				'success' => false,
				'error' => 'Unbekannter Fehler'
			]);
			
			$mockResponse = $this->createMock(ResponseHandlerInterface::class);
			$mockResponse->expects($this->once())
				->method('error')
				->with('Unbekannter Fehler', 500);
			
			$controller = new TodoController(
				$mockAuth,
				$mockService,
				$mockValidation,
				$mockResponse
			);
			
			$controller->add();
		}
	}