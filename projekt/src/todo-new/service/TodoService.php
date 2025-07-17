<?php
	namespace todoNew\Service;

	require_once __DIR__ . '/TodoServiceInterface.php';
	require_once __DIR__ . '/../repository/TodoRepositoryInterface.php';
	
	use TodoNew\Repository\TodoRepositoryInterface;
	use TodoNew\Service\TodoServiceInterface;
	use Exception;
	
	/**
	 * Service-Schicht fuer die Todo-Funktionalitaet.
	 *
	 * Kapselt die Geschaeftslogik zum Hinzufuegen von Todos.
	 * Holt Daten vom Controller, verarbeitet sie und delegiert an das Repository.
	 * Traegt bei Fehlern strukturierte Informationen zur Diagnose bei.
	 *
	 * @package TodoNew\Service
	 */
	class TodoService implements TodoServiceInterface{
		/**
		 * Repository fuer die Datenzugriffe.
		 *
		 * @var TodoRepositoryInterface
		 */
		protected TodoRepositoryInterface $repository;
		
		/**
		 * Konstruktor mit Repository-Injektion.
		 *
		 * @param TodoRepository $repository
		 * Instanz des Repositories
		 */
		public function __construct(TodoRepositoryInterface $repository){
			$this->repository = $repository;
		}
		
		/**
		 * Fuegt ein neues Todo fuer einen Benutzer hinzu.
		 *
		 * - Leitet den Eintrag an das Repository weiter.
		 * - Ruft das Todo bei Erfolg erneut zur Bestaetigung ab.
		 * - Gibt Fehler aus Service oder Repository strukturiert zurueck.
		 *
		 * Die Fehlerstruktur des Repositories wird unveraendert durchgereicht.
		 *
		 * @param string $title Der Titel des Todos
		 * @param int $userId ID des Benutzers
		 * @return array Ergebnisstruktur
		 */
		public function addTodo(string $title, int $userId): array{
			try{
				$success = $this->repository->insertTodo($userId, $title);
				
				/*
				 * Pruefe: Wurde das Todo erfolgreich gespeichert?
				 */
				if(is_array($success) && ($success['success'] ?? false) === true){
					/*
					 * Versuche nun, das gerade gespeicherte Todo erneut aus der DB zu laden.
					 */
					$todoData = $this->repository->getTodoById($success['todo_id']);
					
					/*
					 * Pruefe: Wurde das Todo erfolgreich geladen?
					 */
					if(is_array($todoData) && ($todoData['success'] ?? true) !== false){
						/*
						 * Todo wurde erfolgreich geladen: Rueckgabe des vollstaendigen Todos.
						 */
						return $todoData;
					}else{
						/*
						 * Todo konnte nicht erneut geladen werden: Fehler im Service.
						 */
						return [
							'success' => false,
							'error' => $todoData['error'] ?? 'Fehler beim Abrufen des neuen Todos.',
							'debug' => $todoData['debug'] ?? [],
							'source' => 'service'
						];
					}
				}else{
					/*
					 * insertTodo schluf fehl: Fehler kommt aus dem Repository.
					 */
					return $success;
				}
			}catch(Exception $e){
				/*
				 * Unerwartete Ausnahem: Fehler im Service.
				 */
				return [
					'success' => false,
					'error' => $e->getMessage(),
					'source' => 'service'
				];
			}
		}
	}