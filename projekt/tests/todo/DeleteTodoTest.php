<?php

/*
	Sollten noch globale Variablen vor Tests geleert?
	Gilt für alle Todo-Funktionen
*/

//	funktionen_tests.php
use PHPUnit\Framework\TestCase;
	
require_once __DIR__ . '/../todo.php';
require_once __DIR__ . '/../db.php';
	
//	Testklasse für deleteTodo()
class DeleteTodoTest extends TestCase
{
	private PDO $pdo;
	
	protected function setUp(): void{
		$this->pdo = new PDO("sqlite::memory:");
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec('pragma foreign_keys = on');
		
		//	Benutzer- und Todos-Tabelle erstellen
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

	/*
		Test Nr. 1
		Prüfen, ob ein Todo korrekt gelöscht wird, wenn ein gültiger Benutzer
		es löscht.
	*/
	public function testDeleteTodoRemovesTodoFromDatabase(){
		$userId = 1;
		$title = 'Ein Todo';
		
		$stmt = $this->pdo->prepare("insert into todos (user_id, title) values (?, ?)");
		$stmt->execute([$userId, $title]);
		$todoId = $this->pdo->lastInsertId();
		
		deleteTodo($this->pdo, $userId, $todoId);
		
		$stmt = $this->pdo->prepare("select count(*) from todos where id = ?");
		$stmt->execute([$todoId]);
		$count = $stmt->fetchColumn();
		
		$this->assertEquals(0, $count);
	}
	
	/*
		Test Nr. 2
		Ein Benutzer darf nicht fremde Todos löschen.
	*/
	public function testDeleteTodoWithWrongUserDoesNotDelete(){
		$ownerId = 1;
		$wrongUserId = 2;
		$title = 'Fremdes Todo';
		
		$stmt = $this->pdo->prepare("insert into todos (user_id, title) values (?, ?)");
		$stmt->execute([$ownerId, $title]);
		$todoId = $this->pdo->lastInsertId();
		
		deleteTodo($this->pdo, $wrongUserId, $todoId);
		
		$stmt = $this->pdo->prepare("select count(*) from todos where id = ?");
		$stmt->execute([$todoId]);
		$count = $stmt->fetchColumn();
		
		$this->assertEquals(1, $count);
	}
	
	/*
		Test Nr. 3
		Wenn das Todo gar nicht existiert, soll nichts passieren (kein Fehler,
		keine Löschung).
	*/
	public function testDeleteNonexistentTodoDoesNothing(){
		$userId = 1;
		$title = 'Ein Todo';
		
		//	Ein Todo anlegen
		$stmt = $this->pdo->prepare("insert into todos (user_id, title) values (?, ?)");
		$stmt->execute([$userId, $title]);
		
		$nonExistentTodoId = 999;
		//	Es darf keine Exception auftreten
		deleteTodo($this->pdo, $userId, $nonExistentTodoId);
		
		//	Prüfen, dass es keine Todos gibt
		$stmt = $this->pdo->prepare("select count(*) from todos");
		$stmt->execute();
		$count = $stmt->fetchColumn();
		$this->assertEquals(1, $count);
	}
	
	/*
		Test Nr. 4
		Ein Todo mit gültiger ID darf nicht gelöscht werden, wenn die
		Benutzer-ID nicht existiert.
	*/
	public function testDeleteTodoWithInvalidUserIdDoesNothing(){
		$validUserId = 1;
		$invalidUserId = 2;
		$title = 'Ein Todo';
		
		$stmt = $this->pdo->prepare("insert into todos (user_id, title) values (?, ?)");
		$stmt->execute([$validUserId, $title]);
		$todoId = $this->pdo->lastInsertId();
		
		deleteTodo($this->pdo, $invalidUserId, $todoId);
		
		$stmt = $this->pdo->prepare("select count(*) from todos where id = ?");
		$stmt->execute([$todoId]);
		$count = $stmt->fetchColumn();
		
		$this->assertEquals(1, $count);
	}
}