<?php
	namespace Shared\Http;
	
	/**
	 * Schnittstelle zur Extraktion von Bearer-Token aus HTTP-Authorization-Headern.
	 *
	 * Ermoeglicht das Auslesen eines Tokens aus einem gegebenen oder
	 * automatisch erkannten Header.
	 *
	 * @package Shared\Http
	 */
	interface RequestTokenReaderInterface{
		/**
		 * Extrahiert den Bearer-Token aus dem Authorization-Header.
		 *
		 * @param string|null $authHeader
		 * Optionaler Headerwert (z.B.: $_SERVER['HTTP_AUTHORIZATION']).
		 * Wird automatisch ermittelt, falls null uebergeben wird.
		 *
		 * @return string|null
		 * Der extrahierte Token oder null bei Fehlern oder ungueltigem Format.
		 */
		public function getBearerToken(?string $authHeader = null): ?string;
	}