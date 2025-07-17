<?php
	namespace Shared\Middleware;

	require_once __DIR__ . '/../http/InputProviderInterface.php';
	require_once __DIR__ . '/../response/ResponseHandlerInterface.php';
	require_once __DIR__ . '/../validation/FieldValidatorInterface.php';
	require_once __DIR__ . '/ValidationMiddlewareInterface.php';
	
	use Shared\Http\InputProviderInterface;
	use Shared\Response\ResponseHandlerInterface;
	use Shared\Validation\FieldValidatorInterface;
	
	/**
	 * Middleware zur Validierung von Pflichtfeldern im JSON-Body.
	 *
	 * Diese Klasse nutzt ein FieldValidatorInterface zur Laufzeitvalidierung
	 * und bricht die Verarbeitung bei ungueltigen Eingaben mit einer
	 * Fehlerantwort ab.
	 *
	 * @package Shared\Middleware
	 */
	class ValidationMiddleware implements ValidationMiddlewareInterface{
		/**
		 * Strategie zur Pruefung und Extraktion von Feldern.
		 *
		 * @var FieldValidatorInterface
		 */
		private FieldValidatorInterface $validator;
		
		/**
		 * Antwortdienst fuer strukturierte Fehlermeldungen.
		 *
		 * @var ResponseHandlerInterface
		 */
		private ResponseHandlerInterface $response;
		
		/**
		 * Liefert den JSON-Request-Body als Array.
		 *
		 * @var InputProviderInterface
		 */
		private InputProviderInterface $input;
		
		/**
		 * Initialisiert die Middleware mit validierungsrelevanten Abhaengigkeiten.
		 *
		 * @param FieldValidatorInterface $validator
		 * Validierungsstrategie (z.B. JsonFieldValidator).
		 *
		 * @param ResponseHandlerInterface $response
		 * Antwortdienst fuer Fehlermeldungen.
		 *
		 * @param InputProviderInterface $input
		 * Quelle des JSON-Inputs.
		 */
		public function __construct(
			FieldValidatorInterface $validator,
			ResponseHandlerInterface $response,
			InputProviderInterface $input
		){
			$this->validator = $validator;
			$this->response = $response;
			$this->input = $input;
		}
		
		/**
		 * Prueft, ob ein Pflichtfeld vorhanden und gueltig ist.
		 *
		 * Bricht die Verarbeitung ab, wenn das Feld fehlt oder leer ist.
		 * Gibt bei Erfolg den getrimmten Wert des Feldes zurueck.
		 *
		 * @param string $fieldName Name des zu pruefenden Pflichtfeldes.
		 * @return string Getrimmter Wert des Feldes.
		 */
		public function requireField(string $fieldName): string{
			$input = $this->input->getJsonBody();
			
			if(!is_array($input) || !$this->validator->hasRequiredField($input, $fieldName)){
				$this->response->error("Feld \"{$fieldName}\" muss angegeben werden", 400);
			}
			
			return $this->validator->getValue($input, $fieldName);
		}
	}