<?php
	require_once __DIR__ . '/../../base/IntegrationTestCase.php';
	require_once __DIR__ . '/../../../src/todo-new/repository/TodoRepository.php';
	require_once __DIR__ . '/../../../src/todo-new/service/TodoService.php';
	
	use todoNew\Service\TodoService;
	use todoNew\Repository\TodoRepository;
	
	class TodoServiceTest extends IntegrationTestCase{
		
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

		public function testAddTodoReturnsSuccessOnValidInput(): void{
			$repository = new TodoRepository($this->pdo);
			$service = new TodoService($repository);
			
			$userId = 1;
			$title = 'Einkaufen';
			
			$result = $service->addTodo($title, $userId);
			
			$expectedResult = ['success' => true];
			$this->assertSame($result, $expectedResult);
		}
		
		public function testAddTodoForwardsRepositoryError(): void{
			$mockRepo = $this->createMock(TodoRepository::class);
			$mockRepo->method('insertTodo')->willReturn([
				'success' => false,
				'error' => 'INSERT fehlgeschlagen',
				'debug' => [
					'errorInfo' => 'MariaDB-Info'
				],
				'source' => 'todo-new/repository'
			]);
			
			$service = new TodoService($mockRepo);
			
			$userId = 1222;
			$title = 'Einkaufen';
			$result = $service->addTodo($title, $userId);
			
			$this->assertFalse($result['success']);
			$this->assertSame('todo-new/repository', $result['source']);
		}
		
		public function testAddTodoReturnsServiceErrorOnException(): void{
			$repository = new TodoRepository($this->pdo);
			$service = new TodoService($repository);
			
			$userId = 999;
			$title = 'Ungueltiges Todo';
			
			$result = $service->addTodo($title, $userId);
			
			$this->assertFalse($result['success']);
			$this->assertSame('repository', $result['source']);
			$this->assertArrayHasKey('debug', $result);
			$this->assertStringContainsString('FOREIGN KEY', $result['debug']['exception']);
		}
	}