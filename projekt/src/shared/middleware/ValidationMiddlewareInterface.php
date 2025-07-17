<?php
	namespace Shared\Middleware;
	
	/**
	 * Schnittstelle fuer Middleware zur Validierung von Pflichtfeldern in JSON-Requests.
	 *
	 * Dient der zentralen Pruefung und Extraktion von Feldern direkt im Controller.
	 *
	 * @package Shared\Middleware
	 */
	interface ValidationMiddlewareInterface{
		/**
		 * Prueft, ob ein bestimmtes Feld im JSON-Body vorhanden und gueltig ist.
		 *
		 * Wenn das Feld fehlt oder leer ist, wird ein Fehler ausgeloest.
		 * Implementierende Klassen entscheiden ueber die konkrete Fehlerbehandlung.
		 *
		 * @param string $fieldName Der Name des pruefenden Pflichtfeldes.
		 * @return string Der bereinigte Wert des Feldes.
		 */
		public function requireField(string $fieldName): string;
	}