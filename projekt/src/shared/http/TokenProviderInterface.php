<?php
	namespace Shared\Http;
	
	/**
	 * Definiert die Schnittstelle zum Extrahieren eines Bearer-Tokens aus
	 * einem HTTP-Authorization-Headers.
	 */
	interface RequestTokenReaderInterface{
		/**
		 * Gibt den Bearer-Token aus dem Authorization-Header zurueck.
		 *
		 * @param string|null $authHeader Optionaler Authorization-Header
		 * (z.B. aus $_SERVER oder apache_request_headers)
		 * @return string|null Der extrahierte Token oder null, falls nicht vorhanden/ungueltig
		 */
		public function getBearerToken(?string $authHeader = null): ?string;
	}