<?php
	namespace Shared\Response;
	
	/**
	 * Interface fuer standardisierte HTTP-Antworten im JSON-Format.
	 *
	 * Definiert Methoden zur strukturierten Rueckgabe von Erfolgs-, Fehler-,
	 * Debug- und Statusantworten.
	 *
	 * @package Shared\Response
	 */
	interface ResponseHandlerInterface{
		/**
		 * Gibt eine erfolgreiche Antwort mit Daten zurueck.
		 *
		 * @param array $data Beliebige Nutzdaten.
		 * @param int $statusCode HTTP-Statuscode, Standard ist 200.
		 */
		public function success(array $data = [], int $statusCode = 200): void;
		
		/**
		 * Gibt eine einfache Fehlermeldung zurueck.
		 *
		 * @param string $message Fehlertext.
		 * @param int $statusCode HTTP-Fehlercode, Standard ist 400.
		 */
		public function error(string $message, int $statusCode = 400): void;
		
		/**
		 * Gibt eine Fehlermeldung mit Zusatzinfos fuer Entwickler.
		 *
		 * @param string $message Hauptfehlermeldung.
		 * @param array $details Zusaetzliche Debug-Daten.
		 * @param int $statusCode HTTP-Fehlercode, Standard ist 500.
		 */
		public function debug(string $message, array $details = [], int $statusCode = 500): void;
		
		/**
		 * Gibt eine Statusmeldung zur Systemverfuegbarkeit zurueck.
		 *
		 * @param string $message Kurze Nachricht, z.B. "OK".
		 * @param bool $success Erfolgsstatus (true/false).
		 * @param int $statusCode HTTP-Code, Standard ist 200.
		 */
		public function status(string $message = 'OK', bool $success = true, int $statusCode = 200): void;
	}