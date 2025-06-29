<?php
	use PHPUnit\Framework\TestCase;
	
	require_once __DIR__ . '/../../../src/todo-new/repository/TodoRepository.php';
	
	class TodoRepositoryTest extends TestCase{
		/*
		 * Sollte man nicht eine Testklasse erstellen, die nachfolgendes als
		 * static Funktion kapselt, damit es wiederverwendet werden kann?
		 *
		 * Brauchen wir hier ueberhaupt eine inmemory loesung? geht es auch ohne?
		 */
		protected function setUp(): void{
			$this->pdo = new PDO("sqlite::memory");
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->exec('pragma foreign_keys = on');
			
			/*
			 * Benutzer- und Todos-Tabelle erstellen
			 */
			$this->pdo->exec("
				create table users(
					id integer primary key autoincrement,
					email varchar(255) unique not null,
					password varchar(255) not null,
					created_at timestamp default current_timestamp
				);
			");
			
			$this->pdo->exec("
				create table todos(
					id integer primary key autoincrement,
					user_id int not null,
					title varchar(255) not null,
					is_done boolean default false,
					created_at timestamp default current_timestamp,
					foreign key (user_id) references users(id) on delete cascade
				);
			");
			
			$stmt = $this->pdo->prepare("insert into users (email, password) values (?, ?)");
			$stmt->execute(['test@test.de', password_hash('EinPasswort123-', PASSWORD_DEFAULT)]);
		}
		
		public function testInsertTodoReturnsTrueOnSuccess(): void{
			$repo = new TodoRepository();
			
			$userId = 1;
			$title = 'Test-Todo ' . uniqid();
			
			$result = $repo->insertTodo($userId, $title);
			
			$this-assertTrue(
				$result,
				'insertTodo() gibt fuer gueltige Daten true zurueck.'
			);
		}
	}