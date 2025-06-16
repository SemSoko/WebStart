<?php
	namespace Shared/Auth;
	
	use Firebase\JWT\JWT;
	use Firebase\JWT\KEY;
	use Firebase\JWT\ExpiredException;
	
	/**
	 * Verarbeitet JSON Web Tokens (JWTs).
	 * Nutzt die Firebase-JWT-Bibliothek fuer sichere Token-Verwaltung.
	 */
	class JwtHandler{
		/**
		 * Geheimer Schluessel zur Signierung und Validierung von Tokens.
		 * Wird aus der Umgebungsvariablen JWT_SECRET geladen.
		 * @var string
		 */
		private string $secret;
		
		/**
		 * Signatur-Algorithmus (aktuell HS256)
		 * @var string
		 */
		private string $algo;
		
		/**
		 * Lebensdauer des Tokens in Sekunden (Time To Live).
		 * @var int
		 */
		private int $ttl;
		
		/**
		 * Initialisiert den Handler mit Secret, Signaturalgorithmus und
		 * Token-Gueltigkeit.
		 */
		public function __construct(){
			$this->secret = getenv('JWT_SECRET');
			$this->algo = 'HS256';
			$this->ttl = 60 * 60 * 24;
		}
		
		/**
		 * Erzeugt ein signiertes JWT aus Nutzdaten.
		 * Ergaenzt automatisch "iat" (Issued At) und "exp" (Ablauf).
		 *
		 * @param array $payload Die Nutzdaten, z.B. ['user_id' => 42]
		 * @return string Das signierte JWT
		 */
		public function generateToken(array $payload): string{
			$issuedAt = time();
			$payload = array_merge(
				$payload,
				[
					'iat' => $issuedAt,
					'exp' => $issuedAt + $this->ttl
				]
			);
			
			return JWT::encode($payload, $this->secret, $this->algo);
		}
		
		/**
		 * Validiert ein uebergebenes JWT.
		 *
		 * @param string $token Das zu pruefende JWT
		 * @return
		 * array|null Gueltiger Payload als Array oder Fehlerstruktur
		 * mit success=false
		 */
		public function validateToken(string $token): ?array{
			try{
				$decoded = JWT::decode($token, new Key($this->secret, $this->algo));
				return (array)$decoded;
			}catch(ExpiredException $e){
				// 401 muss im controller als HTTP-Code gesetzt werden.
				return [
					'success' => false,
					'error' => 'Fehler bei der Authentifizierung.',
					'debug' => [
						'exception' => $e->getMessage(),
						'trace' => $e->getTraceAsString()
					],
					'source' => 'shared/auth/validateToken'
				];
			}catch(\Exception $e){
				return [
					'success' => false,
					'error' => 'Fehler beim Authentifizierungsprozess',
					'debug' => [
						'exception' => $e->getMessage(),
						'trace' => $e->getTraceAsString()
					],
					'source' => 'shared/auth/validateToken'
				];
			}
		}
		
		
		/**
		 * Extrahiert die User-ID aus einem gueltigem Token.
		 * Wenn das Token ungueltig ist oder keine ID enthaelt, wird null
		 * zurueckgegeben.
		 *
		 * @param string $token Das zu analysierende JWT
		 * @return int|null Benutzer-ID oder null
		 */
		public function getUserIdFromToken(string $token): ?int{
			$payload = $this->validateToken($token);
			return $payload['user_id'] ?? null;
		}
		
		/**
		 * Extrahiert den Bearer-Token aus dem HTTP-Header.
		 *
		 * @param string|null $authHeader Optionaler Authorization-Header
		 * @return string|null Extrahierter Token oder null
		 */
		public static function getBearerToken(?string $authHeader = null): ?string{
			if($authHeader === null){
				$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
				
				if(empty($authHeader) && function_exists('apache_request_header')){
					$headers = apache_request_headers();
					$authHeader = $headers['Authorization'];
				}
			}
			
			if(str_starts_with($authHeader, 'Bearer ')){
				return trim(substr($authHeader, 7));
			}
			
			return null;
		}
		
		/**
		 * Validiert das aktuelle Token und extrahiert eine gueltige User-ID.
		 * Rueckgabe im Debug-Stil mit success-Status, Fehlermeldung oder user_id.
		 *
		 * @return array Strukturierte Antwort mit success + user_id oder Fehlerdetails
		 */
		public static function requireValidUserId(): array{
			$token = self::getBearerToken();
			
			if(!$token){
				return [
					'success' => false,
					'error' => 'Kein gueltiger uebergeben',
					'source' => 'shared/auth'
				];
			}
			
			$jwt = new JwtHandler();
			$userData = $jwt->validateToken($token);
			$userId = $jwt->getUserIdFromToken($token);
			
			if($userData === null || $userId === null){
				return [
					'success' => false,
					'error' => 'Die User-ID konnte nicht ermittelt werden.',
					'source' => 'shared/auth/requireValidUserId'
				];
			}
			
			return [
				'success' => true,
				'user_id' => $userId
			];
		}
	}