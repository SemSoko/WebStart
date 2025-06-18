<?php
	namespace Shared\Middleware;
	
	require_once __DIR__ . '/../validation/JsonValidator.php';
	require_once __DIR__ . '/../response/Response.php';
	
	use Shared\Validation\JsonValidator;
	use Shared\Response\Response;
	
	/**
	 * Middleware zur Validierung von JSON-Daten.
	 *
	 * Prueft z. B., ob erforderliche Felder wie 'title' im Body gesetzt
	 * sind.
	 */
	class ValidationMiddleware{
		/**
		 * Validiert ein Pflichtfeld in der JSON-Eingabe.
		 * Wenn das Feld fehlt oder leer ist, wird die Anfrage mit Fehler
		 * abgebrochen.
		 *
		 * @param string $fieldName
		 * @return string Gueltiger, bereinigter Wert (z. B. Titel)
		 */
		public static function requireField(string $fieldName): string{
			$input = json_decode(file_get_contents('php://input'), true);
			
			if(!is_array($input) || !JsonValidator::required($input, $fieldName)){
				Response::error("Feld \"{$fieldName}\" muss angegeben werden", 400);
			}
			
			return JsonValidator::getValue($input, $fieldName);
		}
	}