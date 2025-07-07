<?php
	namespace Shared\Validation;
	
	/**
	 * Helferklasse zur Verarbeitung von JSON-Eingaben.
	 *
	 * Diese Klasse kapselt den Zugriff auf den HTTP-Request-Body und
	 * stellt sicher, dass der Body nur einmal pro Anfrage gelesen wird.
	 *
	 * @remarks
	 * Da PHP pro HTTP-Request neu startet (stateless), ist der statische
	 * Cache ($cachedInput) **nur innerhalb eines einzelnen Request gueltig**.
	 * Zwischen verschiedenen HTTP-Anfragen wird der Cache nicht beibehalten.
	 * Das bedeutet: Die Daten aus php://input sind nicht *sicher* dauerhaft
	 * gespeichert, sondern muessen je Anfrage neu gelesen werden. Der Cache
	 * dient lediglich zur Mehrfachverwendung *innerhalb* eines einzelnen
	 * Request-Lebenszyklus.
	 */
	class InputHelper{
		/**
		 * Zwischenspeicher fuer den bereits gelesenen JSON-Body.
		 * @var array|null
		 */
		private static ?array $cachedInput = null;
	
	
		/**
		 * Liest und gibt den JSON-Body zurueck.
		 * Beim ersten Zugriff wird der Body gelesen und zwischengespeichert.
		 * Weitere Aufrufe nutzen den Cache.
		 *
		 * @return array|null
		 */
		public static function getJsonBody(): ?array{
			if(self::$cachedInput === null){
				// delegiert an neue Methode
				$raw = self::getRawInput();
				self::$cachedInput = json_decode($raw, true);
			}
			
			return self::$cachedInput;
		}
		
		public static function getRawInput(): string{
			return file_get_contents('php://input');
		}
	}