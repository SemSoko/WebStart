<?php
	namespace Shared\Middleware;
	
	/**
	 * Inteface fuer Middleware, die Pflichtfelder aus einem JSON-Body validiert.
	 */
	interface ValidationMiddlewareInterface{
		/**
		 * Prueft, ob ein Pflichtfeld im JSON-Body vorhanden ist und gibt den
		 * Wert zurueck.
		 *
		 * @param string $fieldName Name des Pflichtfelds
		 * @return string Wert des Feldes
		 */
		public function requireField(string $fieldName): string;
	}