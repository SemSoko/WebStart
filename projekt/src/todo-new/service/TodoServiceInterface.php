<?php
	namespace TodoNew\Service;
	
	/**
	 * Schnittstelle fuer den Todo-Service.
	 *
	 * Definiert die oeffentlich zugaengliche Logik fuer das
	 * Hinzufuegen von Todos, unabhaengig von der konkreten
	 * Implementierung oder dem verwendenten Repository.
	 */
	interface TodoServiceInterface{
		public function addTodo(string $title, int $userId): array;
	}