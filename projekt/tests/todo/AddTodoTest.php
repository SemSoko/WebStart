<?php
//	funktionen_tests.php
use PHPUnit\Framework\TestCase;
	
require_once __DIR__ . '/../todo.php';
require_once __DIR__ . '/../db.php';
	
//	Testklasse für addTodo()
class AddTodoTest extends TestCase
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
		Hinzufügen eines Todos in die DB, eines bestimmten Benutzers.
		Es wird geprüft, ob ein Todo erfolgreich in die DB eingefügt wird.
	*/
	public function testAddTodoInsertsRowCorrectly(){
		$userId = 1;
		$title = 'Ein neues Todo';
		
		addTodo($this->pdo, $userId, $title);
		
		$stmt = $this->pdo->prepare("select * from todos where user_id = ?");
		$stmt->execute([$userId]);
		$todos = $stmt->fetchAll();
		
		$this->assertCount(1, $todos);
		$this->assertEquals($title, $todos[0]['title']);
		$this->assertEquals(0, $todos[0]['is_done']);
	}
	
	/*
		Test Nr. 2
		Prüfen, wie sich die Funktion verhält, wenn probiert wird ein Todo ohne
		bzw. mit leerem Titel in die DB einzufügen.
	*/
	public function testAddTodoWithEmptyTitleThrowsException(){
		$userId = 1;
		$title = '';
		
		$this->expectException(InvalidArgumentException::class);
		$result = addTodo($this->pdo, $userId, $title);
	}
	
	/*
		Test Nr. 3
		Prüfen, wie sich die Funktion verhält, wenn eine ungültige BenutzerID
		angegeben wird bzw. dieser BenutzerID ein Todo zugeordnet werden soll.
	*/
	public function testAddTodoWithInvalidUserIdThrowsException(){
		$userId = 999;
		$title = 'Ganz was Neues';
		
		$this->expectException(PDOException::class);
		$result = addTodo($this->pdo, $userId, $title);
	}
	
	/*
		Test Nr. 4
		Prüft das Verhalten bei einer SQL-Injection
		Im Titel wird eine SQL-Injection an die DB übergeben.
		
		Wichtig
		Über prepare()-Methode wird SQL-Injection ausgehebelt
	*/
	public function testAddTodoWithSQLInjectionLikeInputIsStoredSafely(){
		$userId = 1;
		$title = "Test'); drop table todos;--";
		
		addTodo($this->pdo, $userId, $title);
		
		$stmt = $this->pdo->prepare("select title from todos where user_id = ?");
		$stmt->execute([$userId]);
		$todo = $stmt->fetch();
		
		$this->assertEquals($title, $todo['title']);
	}
	
	/*
		Test Nr. 5.1
		Prüft das Verhalten, wenn maximal zulässige Länge des Titels
		ausgereizt wird (Titel aus 255 characters).
	*/
	public function testAddTodoWith255CharTitleSucceeds(){
		$userId = 1;
		$title = str_repeat('a', 255);
		
		addTodo($this->pdo, $userId, $title);
		
		$stmt = $this->pdo->prepare("select title from todos where user_id = ?");
		$stmt->execute([$userId]);
		$todo = $stmt->fetch();
		
		$this->assertEquals($title, $todo['title']);
	}
	
	/*
		Test Nr. 5.2
		Prüft das Verhalten, wenn maximal zulässige Länge des Titels
		überschritten wird (Titel > 255 characters).
	*/
	public function testAddTodoWith256CharTitleThrowsException(){
		$userId = 1;
		$title = str_repeat('a', 234256);
		
		$this->expectException(InvalidArgumentException::class);
		addTodo($this->pdo, $userId, $title);
	}
}