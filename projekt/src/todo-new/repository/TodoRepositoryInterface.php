<?php
	namespace TodoNew\Repository;
	
	/**
	 * Schnitstelle fuer TodoRepository zur Kapselung des Datenzugriffs.
	 */
	interface TodoRepositoryInterface{
		public function insertTodo(int $userId, string $title): bool|array;
	}