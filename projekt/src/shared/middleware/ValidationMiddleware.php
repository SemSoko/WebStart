<?php
	require_once __DIR__ . '/../validation/InputHelper.php';
	require_once __DIR__ . '/../response/Response.php';
	require_once __DIR__ . '/../validation/FieldValidatorInterface.php';
	
	namespace Shared\Middleware;
	
	// Abhaengigkeiten
	use Shared\Validation\InputHelper;
	use Shared\Response\Response;
	use Shared\Validation\FieldValidatorInterface;
	
	/**
	 * Middleware zur Validierung von JSON-Daten.
	 *
	 * Diese Klasse ist fuer die Validierung von Pflichtfeldern im JSON-Body zustaendig.
	 * Sie verwendet eine validierungsstrategie-basierte Klasse, die ueber ein Interface
	 * bereitgestellt wird.
	 * -> So bleibt die Middleware flexibel und testbar.
	 * 
	 * Prueft z. B., ob erforderliche Felder wie 'title' im Body gesetzt
	 * sind.
	 */
	class ValidationMiddleware{
		/**
		 * Die Validierungslogik wird nicht fest verdrahtet,
		 * sondern ueber ein Interface abstrahiert -> Dependency Injection.
		 */
		private FieldValidatorInterface $validator;
		
		/**
		 * Konstruktor mit Injektion der konkreten Validator-Implementierung.
		 * Die Klasse erwartet hier *irgendeine* Klasse, die das
		 * Interface erfuellt, nicht zwingend JsonFieldValidator.
		 *
		 * @param FieldValidatorInterface $validator
		 */
		public function __construct(FieldValidatorInterface $validator){
			$this->validator = $validator;
		}
		
		/**
		 * Prueft, ob ein bestimmtes Pflichtfeld im JSON-Body gesetzt ist und
		 * gibt den bereinigten Wert zurueck.
		 * Wenn das Feld fehlt oder leer ist, wird die Anfrage mit Fehler
		 * abgebrochen.
		 *
		 * @param string $fieldName Der Feldname (z.B. 'title')
		 * @return string Der bereinigte (getrimmte) Wert des Feldes
		 */
		public function requireField(string $fieldName): string{
			// Hole den JSON-Body als assoziatives Array
			$input = InputHelper::getJsonBody();
			
			// Pruefe, ob das Feld vorhanden und nicht leer ist
			if(!is_array($input) || !$this->validator->hasRequiredField($input, $fieldName)){
				// Wenn nicht: sende Fehlerantwort und beende die Anfrage
				Response::error("Feld \"{$fieldName}\" muss angegeben werden", 400);
			}
			
			// Wenn alles OK: Rueckgabe des bereinigten Wertes
			return $this->validator->getSanitizedValue($input, $fieldName);
		}
	}