<?php
	namespace Shared/Validation;
	
	/**
	 * Validiert JSON-Eingaben.
	 *
	 * Diese Klasse prueft, ob bestimmte Felder in einem JSON-Request vorhanden
	 * sind und bestimmten Anforderungen genuegen (z. B. nicht leer).
	 */
	class JsonValidator{
		/**
		 * Prueft, ob ein Pflichtfeld gesetzt und nicht leer ist.
		 *
		 * @param array $input Das JSON-Array (z. B. aus json_decode)
		 * @param string $field Der Feldname (z. B. 'title')
		 * @return bool true, wenn vorhanden und nicht leer, sonst false
		 */
		public static function required(array $input, string $field): bool{
			return isset($input[$field] && trim($input[$field]) !== '');
		}
		
		/**
		 * Holt den Wert eines Feldes aus dem JSON.
		 * Gibt null zurueck, wenn das Feld nicht existiert.
		 *
		 * @param array $input
		 * @param string $field
		 * @return string|null
		 */
		public static function getValue(array $input, string $field): ?string{
			return isset($input[$field]) ? trim($input[$field]) : null;
		}
	}