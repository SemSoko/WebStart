<?php
	namespace Shared\Http;
	
	require_once __DIR__ . '/RequestTokenReaderInterface.php';
	
	use Shared\Http\RequestTokenReaderInterface;
	
	/**
	 * Konkrete Implementierung von RequestTokenReaderInteface.
	 *
	 * Extrahiert Bearer-Tokens aus HTTP-Authorization-Headern.
	 * Nutzt sowohl $_SERVER als auch apache_request_headers() als Fallback.
	 *
	 * @package Shared\Http
	 */
	class RequestTokenReader implements RequestTokenReaderInterface{
		/**
		 * Gibt den Bearer-Token aus dem Authorization-Header zurueck.
		 *
		 * Wenn kein Header uebergeben wurde, wird versucht:
		 * 1. $_SERVER['HTTP_AUTHORIZATION']
		 * 2. apache_request_headers() (falls vorhanden)
		 * abzurufen.
		 *
		 * @param string|null $authheader
		 * Optionaler Authorization-Header (z.B.: aus Middleware)
		 *
		 * @return string|null
		 * Der extrahierte Token oder null, falls nicht vorhanden/ungueltig.
		 */
		public function getBearerToken(?string $authHeader = null): ?string{
			if($authHeader === null){
				$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
				
				if(empty($authHeader)){
					$headers = $this->getApacheRequestHeaders();
					$authHeader = $headers['Authorization'] ?? '';
				}
			}
			
			if(str_starts_with($authHeader, 'Bearer ')){
				return trim(substr($authHeader, 7));
			}
			
			return null;
		}
		
		/**
		 * Versucht, alle HTTP-Header mithilfe von apache_request_headers() abzurufen.
		 *
		 * Wird als Fallback verwendet, wenn $_SERVER['HTTP_AUTHORIZATION'] nicht
		 * verfuegbar ist, z.B. bei bestimmten Serverkonfigurationen oder
		 * Apache-Modulen.
		 *
		 * Gibt ein assoziatives Array zurueck, z.B. ['Authorization' => 'Bearer ...'].
		 * Gibt ein leeres Array zurueck, wenn apache_request_headers() nicht verfuegbar ist.
		 *
		 * @return array HTTP-Header als Schluessel-Wert-Paar.
		 */
		protected function getApacheRequestHeaders(): array{
			return function_exists('apache_request_headers') ? apache_request_headers() : [];
		}
	}