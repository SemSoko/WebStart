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
			
			try{
				$stmt = $pdo->prepare("INSERT INTO todos (user_id, title) values (?, ?)");
				$dbResult = $stmt->execute([$userId, $title]);
				if($dbResult){
					return true;
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
				
			}catch(PDOException $e){
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
	}