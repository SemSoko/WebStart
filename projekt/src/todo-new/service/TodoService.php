<?php
	require_once __DIR__ . '/../repository/TodoRepository.php';
	
	/**
	 * Service-Schicht fuer Todo-Funktionalitaet.
	 *
	 * Kapselt die Geschaeftslogik rund um Todos.
	 */
	class TodoService{
		/**
		 * Fuegt ein neues Todo hinzu.
		 *
		 * @param string $title Der Titel des Todos.
		 * @return array ['success' => bool, 'error' => string|null]
		 */
		public function addTodo(string $title): array{
			$repository = new TodoRepository();
			
			try{
				// Beispiel-ID fuer Benutzer, spaeter durch echtes Auth-System ersetzen
				$userId = 1;
				
				// Weitergabe an Repository
				$success = $repository->insertTodo($userId, $title);
				
				if($success){
					return ['success' => true];
				} else {
					return ['success' => false, 'error' => 'Einfuegen fehlgeschlagen'];
				}
			}catch(Exception $e){
				return ['success' => false, 'error' => $e->getMessage()];
			}
		}
	}