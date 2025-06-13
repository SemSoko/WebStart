<?php
	require_once __DIR__ . '/../../../bootstrap/init.php';
	
	/**
	 * Repository-Klasse
	 *
	 * Verantwortlich fuer den Datenbankzugriff.
	 */
	class TodoRepository{
		/**
		 * Fuegt ein neues Todo fuer den angegebenen Benutzer hinzu.
		 *
		 * @param int $userId Benutzer-ID
		 * @param string $title Titel des Todos
		 * @return bool Erfolg des INSERT-Vorgangs
		 */
		public function insertTodo(int $userId, string $title): bool{
			$pdo = Database::getConnection();
			
			if(trim($title) === ''){
				throw new InvalidArgumentException('Titel darf nicht leer sein.');
			}
			
			if(strlen($title) > 255){
				throw new InvalidArgumentException('Title darf nicht länger als 255 Zeichen sein.');
			}
			
			$stmt = $pdo->prepare("INSERT INTO todos (user_id, title) values (?, ?)");
			return $stmt->execute([$userId, $title]);
		}
	}