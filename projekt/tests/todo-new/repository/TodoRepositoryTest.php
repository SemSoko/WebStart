<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/todo-new/repository/TodoRepository.php';
	
	use todoNew\Repository\TodoRepository;
	
	class TodoRepositoryTest extends DatabaseTestCase{
		
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
		
		/**
		 * @return void
		 */
		public function testInsertTodoReturnsTrueOnSuccess(): void{
			$repo = new TodoRepository($this->pdo);
			
			$userId = 1;
			$title = 'Test-Todo ' . uniqid();
			
			$result = $repo->insertTodo($userId, $title);
			
			$this->assertTrue(
				$result,
				'insertTodo() gibt fuer gueltige Daten true zurueck.'
			);
		}
		
		public function testInsertTodoReturnsErrorOnInvalidUserId(): void{
			$repo = new TodoRepository($this->pdo);
			
			$userId = 928374;
			$title = 'Test-Todo ' . uniqid();
			
			$result = $repo->insertTodo($userId, $title);
			
			$this->assertFalse($result['success']);
		}
		
		public function testInsertTodoThrowsExceptionWhenTitleIsEmpty(): void{
			$repo = new TodoRepository($this->pdo);
			
			$userId = 1;
			$title = '';
			
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage('Titel darf nicht leer sein.');
			
			$result = $repo->insertTodo($userId, $title);
		}
		
		public function testInsertTodoThrowsExceptionWhenTitleTooLong(): void{
			$repo = new TodoRepository($this->pdo);
			
			$userId = 1;
			$title = str_repeat('a', 256);
			
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage('Title darf nicht länger als 255 Zeichen sein.');
			
			$result = $repo->insertTodo($userId, $title);
		}
		
		public function testInsertTodoReturnsErrorWhenTableMissing(): void{
			$repo = new TodoRepository($this->pdo);
			
			// Tabelle manuell loeschen, um SQL-Fehler zu erzwingen
			$this->pdo->exec("DROP TABLE todos");
			
			$userId = 1;
			$title = 'Ein Todo';
			
			$result = $repo->insertTodo($userId, $title);
			
			$this->assertIsArray($result);
			$this->assertArrayHasKey('success', $result);
			$this->assertFalse($result['success']);
			$this->assertSame('repository', $result['source']);
			$this->assertArrayHasKey('error', $result);
			$this->assertArrayHasKey('debug', $result);
		}
	}