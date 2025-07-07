<?php
	/*
	 * Zugriff auf $_SERVER ist Infrastruktur
	 * Diese Klasse hat eine Aufgabe (SRP): HTTP-Header lesen
	 * Sie ist modulunabhängig und projektweit wiederverwendbar
	 * „Helper“ ist ein gängiger Begriff für solche Hilfsdienste
	 */
	
	namespace Shared\Http;
	
	class RequestHelper{
		/**
		 * Extrahiert den Bearer-Token aus dem HTTP-Header.
		 *
		 * @param string|null $authHeader Optionaler Authorization-Header
		 * @return string|null Extrahierter Token oder null
		 */
		public static function getBearerToken(?string $authHeader = null): ?string{
			if($authHeader === null){
				$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
				
				if(empty($authHeader)){
					$headers = static::getApacheRequestHeaders();
					$authHeader = $headers['Authorization'] ?? '';
				}
			}
			
			if(str_starts_with($authHeader, 'Bearer ')){
				return trim(substr($authHeader, 7));
			}
			
			return null;
		}
		
		public static function getApacheRequestHeaders(): array{
			return function_exists('apache_request_headers') ? apache_request_headers() : [];
		}
	}