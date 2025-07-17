<?php
	namespace todoNew\Repository;

	require_once __DIR__ . '/../../../bootstrap/init.php';
	require_once __DIR__ . '/TodoRepositoryInterface.php';
	
	use TodoNew\Repository\TodoRepositoryInterface;
	
	/**
	 * Repository fuer den Datenbankzugriff im Todo-Modul.
	 *
	 * Verwaltet das Speichern und Abrufen von Todos ueber PDO.
	 * Nutzt Dependency Injection fuer die Datenbankverbindung.
	 *
	 * @package TodoNew\Repository
	 */
	class TodoRepository implements TodoRepositoryInterface{
		/**
		 * @var \PDO Verbindung zur Datenbank.
		 * Wird per Dependency Injection bereitgestellt.
		 */
		protected \PDO $pdo;
		
		/**
		 * Initialisiert das Repository mit einer PDO-Verbindung.
		 *
		 * Die Verbindung wird ueber den Konstruktor injiziert,
		 * um Testbarkeit und Entkopplung zu foerdern.
		 *
		 * @param PDO $pdo Die zu verwendende PDO-Instanz fuer Datenbankzugriffe.
		 */
		public function __construct(\PDO $pdo){
			$this->pdo = $pdo;
		}
		
		/**
		 * Fuegt ein neues Todo fuer den angegebenen Benutzer hinzu.
		 *
		 * @param int $userId Benutzer-ID
		 * @param string $title Titel des Todos
		 * @return array Erfolgsstruktur mit todo_id oder strukturierter Fehler
		 * @throws \InvalidArgumentException Bei leerem oder zu langem Titel
		 */
		public function insertTodo(int $userId, string $title): bool|array{
			if(trim($title) === ''){
				throw new \InvalidArgumentException('Titel darf nicht leer sein.');
			}
			
			if(strlen($title) > 255){
				throw new \InvalidArgumentException('Title darf nicht länger als 255 Zeichen sein.');
			}
			
			try{
				$stmt = $this->pdo->prepare("INSERT INTO todos (user_id, title) values (?, ?)");
				$dbResult = $stmt->execute([$userId, $title]);
				if($dbResult){
					$todoId = (int)$this->pdo->lastInsertId();
					return [
						'success' => true,
						'todo_id' => $todoId
					];
				}else{
					return [
						'success' => false,
						'error' => 'INSERT fehlgeschlagen',
						'debug' => [
							'errorInfo' => $stmt->errorInfo()
						],
						'source' => 'todo-new/repository'
					];
				}
				
			}catch(\PDOException $e){
				return [
					'success' => false,
					'error' => 'Fehler beim Hinzufuegen des Todos in die Datenbank.',
					'debug' => [
						'exception' => $e->getMessage(),
						'trace' => $e->getTraceAsString()
					],
					'source' => 'repository'
				];
			}
		}
		
		/**
		 * Holt ein Todo anhand seiner ID aus der Datenbank.
		 *
		 * @param int $todoId Die ID des Todos
		 * @return array Struktur mit Todo-Daten oder strukturierter Fehler
		 */
		public function getTodoById(int $todoId): array{
			$stmt = $this->pdo->prepare("SELECT * from todos where id = ?");
			$dbResult = $stmt->execute([$todoId]);
			$newTodo = $stmt->fetch(\PDO::FETCH_ASSOC);
			
			if($dbResult && $newTodo){
				return[
					'todo_id' => $newTodo['id'],
					'todo_title' => $newTodo['title'],
					'todo_status' => $newTodo['is_done'],
					'todo_iat' => $newTodo['created_at']
				];
			}else{
				return [
					'success' => false,
					'error' => 'SELECT fehlgeschlagen',
					'debug' => [
						'errorInfo' => $stmt->errorInfo()
					],
					'source' => 'todo-new/repository'
				];
			}
		}
	}