<?php
//	funktionen_tests.php
use PHPUnit\Framework\TestCase;
	
require_once __DIR__ . '/../todo.php';
require_once __DIR__ . '/../db.php';
	
//	Testklasse für getTodosByUser()
class GetTodosByUserTest extends TestCase
{
	private PDO $pdo;
	
	protected function setUp(): void{
		$this->pdo = new PDO("sqlite::memory:");
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//	Benutzer- und Todos-Tabelle erstellen
		$this->pdo->exec("
			create table users(
				id int AUTO_INCREMENT primary key,
				email varchar(255) unique not null,
				password varchar(255) not null,
				created_at timestamp default current_timestamp
			);
		");
		
		$this->pdo->exec("
			create table todos(
				id int auto_increment primary key,
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
		Dieser Test ruft alle Todos eines Nutzers ab.
		Einem per setUp() erstellten Nutzer wird ein Todo hinzufegüt.
		Dieses wir aus der DB per getTodosByUser() abgerufen.
		Der Test muss alle hinzugefügten Daten erfolgreich abrufen können.
	*/
	public function testGetTodosByUser(){
		/*
			Testbenutzer ID holen (wir gehen davon aus, dass der Benutzer als
			Erster eingefügt wurde)
		*/
		$userId = 1;
		$todoName = 'Neues Todo';
		
		//	Ein Todo hinzufügen
		$stmt = $this->pdo->prepare("insert into todos (user_id, title) values (?, ?)");
		$stmt->execute([$userId, $todoName]);
		
		//	Todos für den Benutzer abfragen
		$todos = getTodosByUser($this->pdo, $userId);
		
		$this->assertCount(1, $todos);
		$this->assertEquals('Neues Todo', $todos[0]['title']);
		$this->assertEquals(0, $todos[0]['is_done']);
	}
	
	/*
		Test Nr. 2
		Dieser Test prüft die Abfrage:
		-	Nutzer hat keine Todos
		Entsprechend sollte die DB keine Einträge zurückgeben
	*/
	public function testGetTodosByUserReturnsEmptyArrayWhenNoTodosExist(){
		$userId = 1;
		$todos = getTodosByUser($this->pdo, $userId);
		$this->assertIsArray($todos);
		$this->assertCount(0, $todos);
	}
	
	/*
		Test Nr. 3
		Dieser Test prüft die Abfrage:
		-	Nutzer hat mehrere Todos
		Die DB muss meherere Einträge zurückliefern
	*/
	public function testGetTodosByUserReturnsMultipleTodos(){
		$userId = 1;
		$titleArr = array(
			"title1"	=>	"Eintrag Nr. 1",
			"title2"	=>	"Eintrag Nr. 2",
		);
		
		$stmt = $this->pdo->prepare("insert into todos (user_id, title) values (?, ?)");
		$stmt->execute([$userId, $titleArr['title1']]);
		$stmt->execute([$userId, $titleArr['title2']]);
		
		$todos = getTodosByUser($this->pdo, $userId);
		$this->assertCount(2, $todos);
		$this->assertEquals('Eintrag Nr. 1', $todos[0]['title']);
		$this->assertEquals('Eintrag Nr. 2', $todos[1]['title']);
	}
	
	/*
		Test Nr. 4
		Dieser Test soll sicherstellen, dass exakt die Todos ausgegeben werden,
		die zum angegebenen Benutzer gehören (welchen wir per userId angeben)
	*/
	public function testGetTodosByUserIgnoresTodosOfOtherUsers(){
		//	Weiteren Benutzer in die DB einfügen
		$email = 'test@asdfjl.de';
		$password = 'Msdiufh-23a';
		
		$stmt = $this->pdo->prepare("insert into users (email, password) values (?, ?)");
		$stmt->execute([$email, $password]);
		
		$userOne = 1;
		$titleOne = 'Ein Todo';
		
		$userTwo = 2;
		$titleTwo = 'Zwei Todo';
		
		$stmt = $this->pdo->prepare("insert into todos (user_id, title) values (?, ?)");
		$stmt->execute([$userOne, $titleOne]);
		$stmt->execute([$userTwo, $titleTwo]);
		
		$todos = getTodosByUser($this->pdo, $userOne);
		$this->assertCount(1, $todos);
		$this->assertEquals('Ein Todo', $todos[0]['title']);
	}
}