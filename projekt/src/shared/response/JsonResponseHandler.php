<?php
	/*
		namespace Shared\Response;: Diese Klasse gehört in das shared/response/-Modul unseres Projekts.
		Das macht spätere use-Statements möglich wie:
		use Shared\Response\Response;
	*/
	namespace Shared\Response;

	require_once __DIR__ . '/ResponseHandlerInterface.php';
	
	/**
     * Standardisierte API-Antwortstruktur fuer alle HTTP-Responses im Projekt.
	 *
	 * Erfolgreiche Antwort (Response::success()):
	 * {
	 *    "success" : true,
	 *    "data": { ... }
	 * }
	 *
	 * Validierungs- oder Clientfehler (Response::error()):
	 * {
	 *    "success": false,
	 *    "message": "Fehlermeldung"
	 * }
	 *
	 * Interner Fehler mit Debug-Daten (Response::debug()):
	 * {
	 *    "success": false,
	 *    "message": "Fehlermeldung",
	 *    "debug": { ... }
	 * }
	 *
	 * Systemstatus (Response::status()):
	 * {
	 *    "success": true,
	 *    "status": "OK",
	 *    "timestamp": "yyyy-mm-dd-T-hh-mm-ss"
	 * }
	 *
	 * @package Shared\Response
	 */	
	class JsonResponseHandler implements ResponseHandlerInterface{
		/**
		 * Geschuetzte exit()-Funktion, damit sie im Test ueberschrieben werden kann.
		 */
		protected function doExit(): void{
			exit();
		}
		
		/**
		 * Gibt eine erfolgreiche API-Antwort als JSON aus.
		 * 
		 * @param array $data Beliebige Nutzdaten (Payload)
		 * @param int $statusCode HTTP-Statuscode (Standard: 200 OK)
		 *
		 * @return void
		 */
		public function success(array $data = [], int $statusCode = 200): void{
			http_response_code($statusCode);
			echo(json_encode([
				'success' => true,
				'data' => $data
			]));
			$this->doExit();
		}
		
		/**
		 * Gibt eine Fehlermeldung als JSON aus.
		 *
		 * @param string $message Fehlernachricht fuer den Client.
		 * @param int $statusCode HTTP-Fehlercode (Standard: 400 Bad Request)
		 *
		 * @return void
		 */
		public function error(string $message, int $statusCode = 400): void{
			http_response_code($statusCode);
			echo(json_encode([
				'success' => false,
				'message' => $message
			]));
			$this->doExit();
		}
		
		/**
		 * Gibt eine detaillierte Fehlermeldung fuer Debug-Zwecke zurueck.
		 *
		 * Nur in der Entwicklungsumgebung verwenden, nicht fuer den Produktivbetrieb gedacht!
		 *
		 * @param string $message Hauptfehlermeldung
		 * @param array $details Zusaetzliche Informationen fuer Entwickler
		 * @param int $statusCode HTTP-Statuscode (Standard: 400)
		 *
		 * @return void
		 */
		public function debug(string $message, array $details = [], int $statusCode = 500): void{
			http_response_code($statusCode);
			echo(json_encode([
				'success' => false,
				'message' => $message,
				'debug' => $details
			]));
			$this->doExit();
		}
		
		/**
		 * Gibt eine einfache Statusantwort zur Pruefung der Erreichbarkeit zurueck.
		 *
		 * Kann z. B. fuer Health Checks oder Verfuegbarkeitspruefungen verwendet werden.
		 *
		 * @param string $message Statusinformation (Standard: "OK")
		 * @param bool $success Gibt an, ob der Status als Erfolg gewertet wird
		 * @param int $statusCode HTTP-Statuscode (Standard: 200)
		 *
		 * @return void
		 */
		public function status(string $message = 'OK', bool $success = true, int $statusCode = 200): void{
			http_response_code($statusCode);
			echo(json_encode([
				'success' => $success,
				'status' => $message,
				'timestamp' => gmdate('c')
			]));
			$this->doExit();
		}
	}