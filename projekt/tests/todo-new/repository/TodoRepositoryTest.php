<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/todo-new/repository/TodoRepository.php';
	
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
		
		public function testInsertTodoReturnsTrueOnSuccess(): void{
			// Wichtig, aktuell nimmt das Repository kein $pdo entgegen!
			$repo = new TodoRepository($this->pdo);
			
			$userId = 1;
			$title = 'Test-Todo ' . uniqid();
			
			$result = $repo->insertTodo($userId, $title);
			
			$this->assertTrue(
				$result,
				'insertTodo() gibt fuer gueltige Daten true zurueck.'
			);
		}
	}