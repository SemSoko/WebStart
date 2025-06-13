<?php
	/*
		namespace Shared\Response;: Diese Klasse gehört in das shared/response/-Modul unseres Projekts.
		Das macht spätere use-Statements möglich wie:
		use Shared\Response\Response;
	*/
	namespace Shared\Response;
	
	/**
	 * Stellt eine zentrale Antwortstruktur fuer API-Endpunkte bereit.
	 * Ziel: Einheitliche JSON-Antworten im Erfolgs- und Fehlerfall.
	 *
	 * @example
	 *    Response::success(['todos' => $todos]);
	 *    Response::error('Fehlende Eingabe', 400);
	 */
	Class Response{
		/**
		 * Gibt eine erfolgreiche API-Antwort als JSON aus.
		 * 
		 * @param array $data Beliebige Nutzdaten (Payload)
		 * @param int $statusCode HTTP-Statuscode (Standard: 200 OK)
		 *
		 * @return void
		 */
		public static function success(array $data = [], int $statusCode = 200): void{
			http_response_code($statusCode);
			echo json_encode([
				'success' => true,
				'data' => $data
			]);
			exit();
		}
		
		/**
		 * Gibt eine Fehlermeldung als JSON aus.
		 *
		 * @param string $message Fehlernachricht fuer den Client.
		 * @param int $statusCode HTTP-Fehlercode (Standard: 400 Bad Request)
		 *
		 * @return void
		 */
		public static function error(string $message, int $statusCode = 400): void{
			http_response_code($statusCode);
			echo json_encode([
				'success' => false,
				'message' => $message
			]);
			exit();
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
		 public static function debug(string $message, array $details = [], int $statusCode = 500): void{
			http_response_code($statuscode);
			echo json_encode([
				'success' => false,
				'message' => $message,
				'debug' => $details
			]);
			exit();
		 }
	}