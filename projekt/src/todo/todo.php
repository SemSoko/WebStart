<?php
	/**
	 * Gibt alle Todos eines Benutzers sortiert nach Erstellungszeitpunkt zurueck.
	 *
	 * @param PDO $pdo Die aktive PDO-Verbindung.
	 * @param int $userId Die Benutzer-ID.
	 * @return array Liste der Todos als assoziatives Array.
	 */
	function getTodosByUser($pdo, $userId){
		$stmt = $pdo->prepare("select * from todos where user_id = ? order by created_at asc");
		$stmt->execute([$userId]);
		return $stmt->fetchAll();
	}
	
	/**
	 * Fuegt ein neues Todo fuer den Benutzer hinzu.
	 * Prueft, ob Titel leer ist und maximale Laenge des Todo-Titels.
	 *
	 * @param PDO $pdo Die aktive PDO-Verbindung.
	 * @param int $userId Die Benutzer-ID.
	 * @param string $title Der Titel des Todos.
	 * @return int|null Die ID des neu erstellten Todos oder null bei Fehler.
	 *
	 * @throws InvalidArgumentException Wenn der Titel leer oder zu lang ist.
	 */
	function addTodo($pdo, $userId, $title){
		if(trim($title) === ''){
			throw new InvalidArgumentException('Titel darf nicht leer sein.');
		}
		
		if(strlen($title) > 255){
			throw new InvalidArgumentException('Title darf nicht länger als 255 Zeichen sein.');
		}
		
		$stmt = $pdo->prepare("insert into todos (user_id, title) values (?, ?)");
		$stmt->execute([$userId, $title]);
		
		$todoId = (int)$pdo->lastInsertId();
		
		/**
		 * @remarks
		 * Sicherheits-Check: Verifiziert, dass der eingefuegte Datensatz auch tatsaechlich
		 * in der Datenbank existiert. Dies ergaenzt die Nutzung von lastInsertId() durch eine
		 * explizite Rueckpruefung. Liefert null, falls der Eintrag wider Erwarten nicht gefunden
		 * wird (beispielsweise bei Replikationsfehlern oder Rollbacks).
		 */
		$stmt = $pdo->prepare("select id from todos where id = ? and user_id = ?");
		$stmt->execute([$todoId, $userId]);
		$todo = $stmt->fetch();
		
		return $todo ? (int)$todo['id'] : null;
	}
	
	/**
	 * Prueft, ob ein bestimmtes Todo als erledigt markiert ist.
	 *
	 * @param PDO $pdo Die aktive PDO-Verbindung.
	 * @param int $todoId Die ID des Todos.
	 * @param int $userId Die Benutzer-ID.
	 * @return int|null 1 = erledigt, 0 = nicht erledigt, null = Todo existiert nicht.
	 *
	 * @todo Tippfehler im Funktionsnamen korrigieren.
	 */
	function getTodotatus($pdo, $todoId, $userId){
		$stmt = $pdo->prepare("select is_done from todos where id = ? and user_id = ?");
		$stmt->execute([$todoId, $userId]);
		$todo = $stmt->fetch();
		return $todo ? (int)$todo['is_done'] : null;
	}
	
	/**
	 * Wechselt den Status eines Todos zwischen erledigt und nicht erledigt.
	 *
	 * @param PDO $pdo Die aktive PDO-Verbindung.
	 * @param int $userId die Benutzer-ID.
	 * @param int $todoId Die ID des Todos.
	 * @return int|null Neuer Status (1 oder 0) oder null bei Fehler.
	 */
	function toggleTodo($pdo, $userId, $todoId){
		/*
		 * @remarks
		 * Nutzt eine SQL-Shortform ('not is_done'), um den Status direkt in
		 * der Datenbank umzuschalten.
		 */
		$stmt = $pdo->prepare("update todos set is_done = not is_done where id = ? and user_id = ?");
		$stmt->execute([$todoId, $userId]);
		
		return  getTodotatus($pdo, $todoId, $userId);
	}
	
	/**
	 * Loescht ein bestimmtes Benutzer-Todo.
	 *
	 * @param PDO $pdo Die aktive PDO-Verbindung.
	 * @param int $userId die Benutzer-ID.
	 * @param int $todoId Die ID des zu loeschenden Todos.
	 * @return bool true, wenn genau ein Todo geloescht wurde, sonst false.
	 */
	function deleteTodo($pdo, $userId, $todoId){
		$stmt = $pdo->prepare("delete from todos where id = ? and user_id = ?");
		$stmt->execute([$todoId, $userId]);
		/*
		 * @remarks
		 * Die Rueckgabe ist nur dann true, wenn exakt ein Eintrag geloescht wurde.
		 */
		return $stmt->rowCount() === 1;
	}
?>