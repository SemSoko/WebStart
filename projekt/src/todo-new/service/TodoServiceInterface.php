<?php
	namespace TodoNew\Service;
	
	/**
	 * Schnittstelle fuer die Geschaeftslogik im todo-new-Modul.
	 *
	 * Definiert die oeffentlichen Methoden rund um das todo-new-Modul.
	 * Die Implementierung kann beliebig sein.
	 * Ziel: Trennung von Logik und Infrastruktur durch saubere Abstraktion.
	 *
	 * @package TodoNew\Service
	 */
	interface TodoServiceInterface{
		/**
		 * Fuegt ein neues Todo fuer einen Benutzer hinzu.
		 *
		 * @param string $title Titel des Todos (muss bereits validiert sein)
		 * @param int $userId ID des angemeldeten Benutzers
		 * @return array
		 * Ergebnisstruktur (z.B.: ['success' => true, 'id' => 123])
		 * oder Fehlerstruktur
		 */
		public function addTodo(string $title, int $userId): array;
	}