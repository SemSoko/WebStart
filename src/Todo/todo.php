<?php
	function getTodosByUser($pdo, $userId){
		$stmt = $pdo->prepare("select * from todos where user_id = ?");
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
	}
	
	function toggleTodo($pdo, $userId, $todoId){
		$stmt = $pdo->prepare("update todos set is_done = not is_done where id = ? and user_id = ?");
		$stmt->execute([$todoId, $userId]);
	}
	
	function deleteTodo($pdo, $userId, $todoId){
		$stmt = $pdo->prepare("delete from todos where id = ? and user_id = ?");
		$stmt->execute([$todoId, $userId]);
	}
?>