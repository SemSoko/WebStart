<?php
	namespace todoNew\Repository;

	require_once __DIR__ . '/../../../bootstrap/init.php';
	require_once __DIR__ . '/TodoRepositoryInterface.php';
	
	use TodoNew\Repository\TodoRepositoryInterface;
	
	/**
	 * Fuegt ein neues Todo fuer den angegebenen Benutzer hinzu.
	 * Nutzt eine via Konstruktor injizierte PDO-Instanz (Dependency
	 * Injection).
	 *
	 * Bei erfolgreichem INSERT:
	 *   return true
	 *
	 * Bei SQL-Ausfuehrungsfehler ohne Exception:
	 * return [
	 *    'success' => false,
	 *    'INSERT fehlgeschlagen',
	 *    'debug' => [
	 *       'errorInfo' => [...PDO Fehlerinfo...]
	 *    ],
	 *    'source' => 'todo-new/repository'
	 * ]
	 *
	 * Bei PDO-Exception (z.B. Verbindungsfehler):
	 * return [
	 *    'success' => false,
	 *    'error' => 'Fehler beim Hinzufuegen des Todos in die Datenbank.',
	 *    'debug' => [
	 *       'exception' => 'Fehlermeldung',
	 *       'trace' => 'Stacktrace'
	 *    ],
	 *    'source' => 'repository'
	 * ]
	 *
	 * @param int $userId Benutzer-ID
	 * @param string $title Titel des Todos
	 * @return bool|array true bei Erfolg, sonst strukturierter Fehler
	 */
	class TodoRepository implements TodoRepositoryInterface{
		protected \PDO $pdo;
		
		/**
		 * Initialisiert das Repository mit einer PDO-Verbindung.
		 *
		 * @param PDO $pdo Die zu verwendende PDO-Verbindung.
		 */
		public function __construct(\PDO $pdo){
			$this->pdo = $pdo;
		}
		/**
		 * Fuegt ein neues Todo fuer den angegebenen Benutzer hinzu.
		 *
		 * @param int $userId Benutzer-ID
		 * @param string $title Titel des Todos
		 * @return bool Erfolg des INSERT-Vorgangs
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
				/*
				 * Gibt ein strukturiertes Fehler-Array mit Debug-Infos
				 * zurueck (fuer Controller sichtbar)
				 */
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
		
		public function getTodoById(int $todoId): array{
			$stmt = $this->pdo->prepare("SELECT * from todos where id = ?");
			$dbResult = $stmt->execute([$todoId]);
			$newTodo = $stmt->fetch(PDO::FETCH_ASSOC);
			
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