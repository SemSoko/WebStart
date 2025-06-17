<?php
	require_once __DIR__ . '/../repository/TodoRepository.php';
	
	/**
	 * Service-Schicht fuer Todo-Funktionalitaet.
	 *
	 * Kapselt die Geschaeftslogik rund um Todos.
	 */
	class TodoService{
		/**
		 * Fuegt ein neues Todo hinzu und verarbeitet das Ergebnis des
		 * Repositories.
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
		 * @return array Strukturierte Erfolgs- und Fehlermeldung
		 */
		public function addTodo(string $title): array{
			$repository = new TodoRepository();
			
			try{
				// Beispiel-ID fuer Benutzer, spaeter durch echtes Auth-System ersetzen
				$userId = 1;
				
				// Weitergabe an Repository
				$success = $repository->insertTodo($userId, $title);
				
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