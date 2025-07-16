<?php
	namespace Shared\Auth;
	
	use Firebase\JWT\JWT;
	use Firebase\JWT\KEY;
	use Firebase\JWT\ExpiredException;
	
	/**
	 * Instanzbasierter JWT-Handler fuer Signierung und Validierung.
	 *
	 * Keine statischen Abhaengigkeiten - vollstaendig DI-kompatibel.
	 *
	 * Nutzt die Firebase-JWT-Bibliothek zur sicheren Verarbeitung von
	 * JSON Web Token (JWT).
	 */
	class JwtHandlerNew{
		/**
		 * Geheimer Schluessel zur Signierung und Validierung.
		 *
		 * @var string
		 */
		private string $secret;
		
		/**
		 * Verwendeter Signaturalgorithmus (z.B.: HS256)
		 *
		 * @var string
		 */
		private string $algo;
		
		/**
		 * Lebensdauer des Tokens in Sekunden (TTL - Time To Live).
		 *
		 * @var int
		 */
		private int $ttl;
		
		/**
		 * Konstruktor fuer den JWT-Handler.
		 *
		 * @param string|null $secret Falls nicht gesetzt, wird der Schluessel
		 * ueber die Umgebungsvariable JWT_SECRET (z.B.: aus einer .env-Datei)
		 * geladen.
		 * @param string $algo JWT-Signaturalgorithmus. Standard: HS256.
		 * @param int $ttl Gueltigkeitsdauer in Sekunden. Standard: 1 Tag (86400).
		 */
		public function __construct(?string $secret = null, string $algo = 'HS256', int $ttl = 86400){
			$this->secret = $secret ?? getenv('JWT_SECRET');
			$this->algo = $algo;
			$this->ttl = $ttl;
		}
		
		/**
		 * Erzeugt ein signiertes JWT mit automatisch gesetzten Zeitfeldern.
		 *
		 * @param array $payload Nutzdaten (z.B.: ['user_id' => 42]).
		 * @return string Das signierte JWT.
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
		 * Gibt bei Erfolg das Payload als Array zurueck.
		 * Bei Fehlern wird eine strukturierte Fehlerantwort geliefert.
		 *
		 * @param string $token Das zu pruefende JWT.
		 * @return array|null Gueltiger Payload oder Fehlerstruktur.
		 */
		public function validateToken(string $token): ?array{
			try{
				$decoded = JWT::decode($token, new Key($this->secret, $this->algo));
				return (array)$decoded;
			}catch(ExpiredException $e){
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
		 * Extrahiert die Benutzer-ID aus einem gueltigem JWT.
		 *
		 * @param string $token Das zu analysierende JWT.
		 * @return int|null Benutzer-ID oder null, wenn ungueltig.
		 */
		public function getUserIdFromToken(string $token): ?int{
			$payload = $this->validateToken($token);
			return $payload['user_id'] ?? null;
		}
	}