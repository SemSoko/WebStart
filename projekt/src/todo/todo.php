<?php
	function getTodosByUser($pdo, $userId){
		$stmt = $pdo->prepare("select * from todos where user_id = ? order by created_at asc");
		$stmt->execute([$userId]);
		
		return $stmt->fetchAll();
	}
	
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
		
		$stmt = $pdo->prepare("select id from todos where id = ? and user_id = ?");
		$stmt->execute([$todoId, $userId]);
		$todo = $stmt->fetch();
		
		return $todo ? (int)$todo['id'] : null;
	}
	
	function getTodotatus($pdo, $todoId, $userId){
		$stmt = $pdo->prepare("select is_done from todos where id = ? and user_id = ?");
		$stmt->execute([$todoId, $userId]);
		$todo = $stmt->fetch();
		
		//	Wenn Status von Todo auf is_done dann:
		//	return = 1, sonst
		//	return 0
		//	Eintrag gefunden, is_done = 0 -> Todo nicht erledigt
		//	Eintrag gefunden, is_done = 1 -> Todo erledigt
		//	Kein Eintrag gefunden (fetch() gibt false) -> null, todo existiert nicht oder Fehler
		return $todo ? (int)$todo['is_done'] : null;
	}
	
	function toggleTodo($pdo, $userId, $todoId){
		$stmt = $pdo->prepare("update todos set is_done = not is_done where id = ? and user_id = ?");
		$stmt->execute([$todoId, $userId]);
		
		return  getTodotatus($pdo, $todoId, $userId);
	}
	
	function deleteTodo($pdo, $userId, $todoId){
		$stmt = $pdo->prepare("delete from todos where id = ? and user_id = ?");
		$stmt->execute([$todoId, $userId]);
		
		//	true, wenn geanu eine Zeile geloescht wurde
		return $stmt->rowCount() === 1;
	}
?>