<?php
	namespace todoNew\Service;

	require_once __DIR__ . '/../repository/TodoRepository.php';
	
	use todoNew\Repository\TodoRepository;
	
	/**
	 * Service-Schicht fuer Todo-Funktionalitaet.
	 *
	 * Kapselt die Geschaeftslogik rund um Todos.
	 */
	class TodoService{
		protected TodoRepository $repository;
		
		/**
		 * Initialisiert den Service mit einem Repository.
		 *
		 * @param TodoRepository $repository Instanz des Repositories
		 */
		public function __construct(TodoRepository $repository){
			$this->repository = $repository;
		}
		
		/**
		 * Fuegt ein neues Todo fuer einen bestimmten Benutzer hinzu
		 * und verarbeitet das Ergebnis des Repositories.
		 *
		 * Erfolgreicher Eintrag:
		 * return [
		 *    'success' => true
		 * ]
		 *
		 * Fehler im Service selbst (z. B. unerwartete Exception):
		 * return [
		 *    'success' => false,
		 *    'error' => 'Fehlermeldung aus Exception'
		 *    'source' => 'service'
		 * ]
		 *
		 * Fehlerstruktur des Repositories wird unveraendert durchgereicht.
		 *
		 * @param string $title Der Titel des Todos.
		 * @param int $userId Die ID des angemeldeten Benutzers.
		 * @return array Strukturierte Erfolgs- und Fehlermeldung
		 */
		public function addTodo(string $title, int $userId): array{
			try{
				// Weitergabe an Repository
				$success = $this->repository->insertTodo($userId, $title);
				
				if(is_array($success) && ($success['success'] ?? false) === false){
					return $success;
				}else{
					return ['success' => true];
				}
			}catch(Exception $e){
				return [
					'success' => false,
					'error' => $e->getMessage(),
					'source' => 'service'
				];
			}
		}
	}