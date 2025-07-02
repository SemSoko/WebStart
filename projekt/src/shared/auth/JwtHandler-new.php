<?php
	namespace Shared\Auth;
	
	use Firebase\JWT\JWT;
	use Firebase\JWT\KEY;
	use Firebase\JWT\ExpiredException;
	
	/**
	 * Instanzbasierter JWT-Handler fuer Signierung und Validierung.
	 *
	 * Konfigurierbar via Konstruktorparameter
	 * Keine statischen Abhaengigkeiten mehr - voll DI-Kompatibel.
	 *
	 * Verarbeitet JSON Web Tokens (JWTs).
	 * Nutzt die Firebase-JWT-Bibliothek fuer sichere Token-Verwaltung.
	 */
	class JwtHandlerNew{
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
		public function __construct(string $secret = getenv('JWT_SECRET'), string $algo = 'HS256', int $ttl = 86400){
			$this->secret = $secret;
			$this->algo = $algo;
			$this->ttl = $ttl;
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
	}