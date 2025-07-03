<?php
	namespace Shared/Validation;
	
	require_once __DIR__ . '/FieldValidatorInterface.php';
	
	/**
	 * Validiert JSON-Eingaben zur Laufzeit (z.B. Pflichtfelder).
	 * Implementiert die zentrale Validierungs-Schnittstelle fuer DI.
	 *
	 * Diese Klasse prueft, ob bestimmte Felder in einem JSON-Request vorhanden
	 * sind und bestimmten Anforderungen genuegen (z.B. nicht leer).
	 */
	class JsonFieldValidator implements FieldValidatorInterface{
		/*
		 * Prueft, ob ein Pflichtfeld gesetzt und nicht leer ist.
		 *
		 * @param array $input Das JSON-Array (z.B. aus json_decode)
		 * @param string $field Der Feldname (z.B. 'title')
		 * @return bool true, wenn vorhanden und nicht leer, sonst false
		 */
		public function hasRequiredField(array $input, string $field): bool{
			if(!isset($input[$field])){
				return false;
			}
			
			return trim($input[$field]) !== '';
		}
		
		/**
		 * Holt den Wert eines Feldes aus dem JSON.
		 *
		 * @param array $input
		 * @param string $field
		 * @return string|null
		 */
		public function getSanitizedValue(array $input, string $field): ?string{
			if(!isset($input[$field])){
				return null;
			}
			
			return trim($input[$field]);
		}
	}