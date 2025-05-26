<?php

/*
	Sollten noch globale Variablen vor Tests geleert?
	Gilt für alle Todo-Funktionen
*/

//	funktionen_tests.php
use PHPUnit\Framework\TestCase;
	
require_once __DIR__ . '/../todo.php';
require_once __DIR__ . '/../db.php';
	
//	Testklasse für toggleTodo()
class ToggleTodoTest extends TestCase
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
		Hier wird geprüft, ob der Todo-Status, durch auslösen der
		toggleTodo()-Funktion, korrekt angepasst wird - Von false zu true.
	*/
	public function testToggleTodoSetsIsDoneToTrue(){
		$userId = 1;
		$title = 'Ein Todo';
		
		//	Todo anlegen
		$stmt = $this->pdo->prepare("insert into todos (user_id, title, is_done) values (?, ?, ?)");
		$stmt->execute([$userId, $title, false]);
		
		$todoId = $this->pdo->lastInsertId();
		
		//	Toggle
		toggleTodo($this->pdo, $userId, $todoId);
		
		//	Prüfen
		$stmt = $this->pdo->prepare("select is_done from todos where id = ?");
		$stmt->execute([$todoId]);
		$isDone = $stmt->fetchColumn();
		
		$this->assertEquals(1, $isDone);
	}
	
	/*
		Test Nr. 2
		Hier wird geprüft, ob der Todo-Status, durch auslösen der
		toggleTodo()-Funktion, korrekt angepasst wird - Von true zu false.
	*/
	public function testToggleTodoSetsIsDoneToFalse(){
		$userId = 1;
		$title = 'Ein Todo';
		
		//	Todo anlegen
		$stmt = $this->pdo->prepare("insert into todos (user_id, title, is_done) values (?, ?, ?)");
		$stmt->execute([$userId, $title, true]);
		
		$todoId = $this->pdo->lastInsertId();
		
		//	Toggle
		toggleTodo($this->pdo, $userId, $todoId);
		
		//	Prüfen
		$stmt = $this->pdo->prepare("select is_done from todos where id = ?");
		$stmt->execute([$todoId]);
		$isDone = $stmt->fetchColumn();
		
		$this->assertEquals(0, $isDone);
	}
	
	/*
		Test Nr. 3
		Hier testen wir das Verhalten der Funktion dahingehend, dass ein Todo
		erst über eine User-ID angelegt wird und anschließend probiert wird,
		diesen Eintrag über eine andere User-ID zu manipulieren.
		
		Die Manipulation des Statuses darf nicht klappen.
	*/
	public function testToggleTodoWithWrongUserDoesNotAffectTodo(){
		$userId = 1;
		$wrongUserId = 2;
		$title = 'Fremdes Todo';
		
		//	Todo mit userId = 1 anlegen
		$stmt = $this->pdo->prepare("insert into todos (user_id, title, is_done) values (?, ?, ?)");
		$stmt->execute([$userId, $title, false]);
		$todoId = $this->pdo->lastInsertId();
		
		//	Probieren Toggle mit falscher Benutzer-ID auszulösen
		toggleTodo($this->pdo, $wrongUserId, $todoId);
		
		$stmt = $this->pdo->prepare("select is_done from todos where id = ?");
		$stmt->execute([$todoId]);
		$isDone = $stmt->fetchColumn();
		
		$this->assertEquals(0, (int)$isDone);
	}
	
	/*
		Test Nr. 4
		Hier testen wir, wie sich die Funktion verhält, wenn eine ungültige
		Todo-ID an sie übergeben wird. Es darf kein Todo zurückgeliefert werden.
		Bzw. muss die Spalte / DB-Eintrag leer sein.
	*/
	public function testToggleTodoWithInvalidTodoIdDoesNothing(){
		$userId = 1;
		$invalidTodoId = 999;
		
		//	Kein Eintrag vorhanden
		//	Sollte ohne Exception laufen
		toggleTodo($this->pdo, $userId, $invalidTodoId);
		
		//	Prüfen ob die Tabelle leer bleibt
		$stmt = $this->pdo->query("select count(*) from todos");
		$count = $stmt->fetchColumn();
		
		$this->assertEquals(0, $count);
	}
	
	/*
		Test Nr. 5
		In diesem Test wird sichergestellt, dass ein Todo-Eintrag nicht verändert
		wird, wenn versucht wird, ihn mit einer nicht existierenden Benutzer-ID
		umzuschalten.
		
		Unterschied zu Test Nr. 3:
		Dort prüfen wir, dass Benutzer keinen Zugriff auf Todo-Listen anderer
		(nicht ihrer eigenen) Benutzer erhalten.
		
		Hier hingegen geht es darum, dass eine völlig ungültige (nicht vorhandene)
		Benutzer-ID ebenfalls keinen Einfluss auf bestehende Todos haben darf.
	*/
	public function testToggleTodoWithInvalidUserIdDoesNothing(){
		//	Gültiger Todo
		$validUserId = 1;
		$invalidUserId = 999;
		$title = 'Todo';
		
		$stmt = $this->pdo->prepare("insert into todos (user_id, title, is_done) values (?, ?, ?)");
		$stmt->execute([$validUserId, $title, false]);
		$todoId = $this->pdo->lastInsertId();
		
		//	Toggle versuchen mit ungültigem Benutzer
		toggleTodo($this->pdo, $invalidUserId, $todoId);
		
		//	Prüfen, ob Todo unverändert ist
		$stmt = $this->pdo->prepare("select is_done from todos where id = ?");
		$stmt->execute([$todoId]);
		$isDone = $stmt->fetchColumn();
		
		$this->assertEquals(0, (int)$isDone);
	}
}