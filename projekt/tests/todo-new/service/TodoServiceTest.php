<?php
	require_once __DIR__ . '/../../base/UnitTestCase.php';
	require_once __DIR__ . '/../../../src/todo-new/service/TodoService.php';
	require_once __DIR__ . '/../../../src/todo-new/repository/TodoRepository.php';
	
	use todoNew\Service\TodoService;
	use Tests\Base\UnitTestCase;
	use todoNew\Repository\TodoRepository;
	
	class TodoServiceTest extends UnitTestCase{
		public function testAddTodoReturnsSuccessOnValidInput(): void{
			$mockRepo = $this->createMock(TodoRepository::class);
			$mockRepo->method('insertTodo')->willReturn(true);
			
			$service = new TodoService($mockRepo);
			
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
			$mockRepo = $this->createMock(TodoRepository::class);
			$mockRepo->method('insertTodo')->willThrowException(new \Exception("Simulierter Fehler"));
			
			$service = new TodoService($mockRepo);
			
			$title = 'Ungueltiges Todo';
			$userId = 999;
			
			$result = $service->addTodo($title, $userId);
			
			$this->assertFalse($result['success']);
			$this->assertSame('service', $result['source']);
			$this->assertSame('Simulierter Fehler', $result['error']);
		}
	}