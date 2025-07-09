<?php
	namespace Shared\Http;
	
	require_once __DIR__ . '/RequestTokenReaderInterface.php';
	
	use Shared\Http\RequestTokenReaderInterface;
	
	/**
	 * Konkrete Implementierung von RequestTokenReaderInteface.
	 * Extrahiert Bearer-Tokens aus HTTP-Authorization-Headern.
	 */
	class RequestTokenReader implements RequestTokenReaderInterface{
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
		
		protected function getApacheRequestHeaders(): array{
			return function_exists('apache_request_headers') ? apache_request_headers() : [];
		}
	}