<?php
	namespace TodoNew\Repository;
	
	/**
	 * Schnittstelle fuer das Todo-Repository im Modul todo-new.
	 *
	 * Definiert die Datenzugriffs-Methoden fuer Todos.
	 * Ziel: Trennung von Logik (Service) und Infrastruktur (DB).
	 *
	 * @package TodoNew\Repository
	 */
	interface TodoRepositoryInterface{
		/**
		 * Fuegt ein neues Todo fuer einen Benutzer in die Datenbank ein.
		 *
		 * @param int $userId Benutzer-ID
		 * @param string $title Titel des neuen Todos
		 * @return array
		 * Erfolgsstruktur (z.B.: ['success' => true, 'todo_id' => 42]) oder
		 * Fehlerstruktur bei Problemen
		 */
		public function insertTodo(int $userId, string $title): bool|array;
		
		/**
		 * Holt ein Todo anhand seiner ID.
		 *
		 * @param int $todoId Die ID des Todos
		 * @return array
		 * Struktur mit den Feldern: todo_id, todo_title, todo_status, todo_iat
		 * oder Fehlerstruktur bei Problemen
		 */
		public function getTodoById(int $todoId): array;
	}