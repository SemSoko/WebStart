<?php
	namespace Shared\Response;

	require_once __DIR__ . '/ResponseHandlerInterface.php';
	
	/**
     * Standardisierte API-Antwortstruktur fuer alle HTTP-Responses im Projekt.
	 *
	 * Erfolgreiche Antwort - success:
	 * {
	 *    "success" : true,
	 *    "data": { ... }
	 * }
	 *
	 * Validierungs- oder Clientfehler - error:
	 * {
	 *    "success": false,
	 *    "message": "Fehlermeldung"
	 * }
	 *
	 * Interner Fehler mit Debug-Daten - debug:
	 * {
	 *    "success": false,
	 *    "message": "Fehlermeldung",
	 *    "debug": { ... }
	 * }
	 *
	 * Systemstatus - status:
	 * {
	 *    "success": true,
	 *    "status": "OK",
	 *    "timestamp": "yyyy-mm-ddTHH:MM:SSZZ"
	 * }
	 *
	 * @package Shared\Response
	 */	
	class JsonResponseHandler implements ResponseHandlerInterface{
		/**
		 * Fuehrt das Beenden des Programms aus.
		 *
		 * Diese Methode is protected, um sie in Tests zu ueberschreiben.
		 *
		 * @return void
		 */
		protected function doExit(): void{
			exit();
		}
		
		/**
		 * Gibt eine erfolgreiche API-Antwort als JSON aus.
		 * 
		 * @param array $data Beliebige Nutzdaten (Payload).
		 * @param int $statusCode HTTP-Statuscode (Standard: 200).
		 *
		 * @return void
		 */
		public function success(array $data = [], int $statusCode = 200): void{
			header('Content-Type: application/json');
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
		 * @param int $statusCode HTTP-Fehlercode (Standard: 400).
		 *
		 * @return void
		 */
		public function error(string $message, int $statusCode = 400): void{
			header('Content-Type: application/json');
			http_response_code($statusCode);
			echo(json_encode([
				'success' => false,
				'message' => $message
			]));
			$this->doExit();
		}
		
		/**
		 * Gibt eine detaillierte Fehlermeldung fuer Debug-Zwecke aus.
		 *
		 * Hinweis: Nur in der Entwicklungsumgebung verwenden!
		 *
		 * @param string $message Hauptfehlermeldung.
		 * @param array $details Zusaetzliche Informationen fuer Entwickler.
		 * @param int $statusCode HTTP-Fehlercode (Standard: 500).
		 *
		 * @return void
		 */
		public function debug(string $message, array $details = [], int $statusCode = 500): void{
			header('Content-Type: application/json');
			http_response_code($statusCode);
			echo(json_encode([
				'success' => false,
				'message' => $message,
				'debug' => $details
			]));
			$this->doExit();
		}
		
		/**
		 * Gibt eine einfache Statusantwort Verfuegbarkeitspruefungen aus.
		 *
		 * @param string $message Statusmeldung (z.B.: "OK")
		 * @param bool $success Gibt an, ob der Status als Erfolgreich gewertet wird.
		 * @param int $statusCode HTTP-Statuscode (Standard: 200).
		 *
		 * @return void
		 */
		public function status(string $message = 'OK', bool $success = true, int $statusCode = 200): void{
			header('Content-Type: application/json');
			http_response_code($statusCode);
			echo(json_encode([
				'success' => $success,
				'status' => $message,
				'timestamp' => gmdate('c')
			]));
			$this->doExit();
		}
	}