<?php
	require_once __DIR__ . '/../../base/UnitTestCase.php';
	require_once __DIR__ . '/../../../src/todo-new/service/TodoService.php';
	require_once __DIR__ . '/../../../src/todo-new/repository/TodoRepository.php';
	
	use todoNew\Service\TodoService;
	use Tests\Base\UnitTestCase;
	use todoNew\Repository\TodoRepository;
	
	class TodoServiceTest extends UnitTestCase{
		public function testAddTodoReturnsSuccessOnValidInput(): void{
			$repository = new TodoRepository($this->pdo); // muss gemockt werden
			$service = new TodoService($repository); // mit mock an interface uebergeben werden
			
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
			// muss ebenfalls gemockt werden
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