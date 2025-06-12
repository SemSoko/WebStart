<?php
	namespace Core;
	
	use Firebase\JWT\JWT;
	use Firebase\JWT\Key;
	use Firebase\JWT\ExpiredException;
	
	/**
	 * Handler-Klasse fuer JWT-Verarbeitung (Erzeugung, Validierung, Auslesen).
	 *
	 * Nutzt die Firebase-JWT-Bibliothek zur sicheren Verwaltung von JSON Web Tokens.
	 */
	class JwtHandler{
		/**
		 * Geheimschluessel zur Signierung und Validierung von Tokens.
		 * Wird aus der Umgebungsvariablen JWT_SECRET geladen.
		 *
		 * @var string
		 */
		private string $secret;
		
		/**
		 * Verwendetes Signaturverfahren (aktuell HS256).
		 * @var string
		 */
		private string $algo;
		
		/**
		 * Lebensdauer des Tokens in Sekunden (Time To Live).
		 * @var int
		 */
		private int $ttl;
		
		/*
		 * Initialisiert den Handler mit Secret, Signaturalgorithmus und Token-Gueltigkeit.
		 * Laedt den Secret Key aus den Umgebungsvariablen.
		 */
		public function __construct(){
			$this->secret = getenv('JWT_SECRET');
			$this->algo = 'HS256';
			$this->ttl = 60 * 60 * 24;
		}
		
		/**
		 * Erstellt ein signiertes JWT auf Basis eines Payloads.
		 * Ergaenzt das Payload automatisch um Erstellungs- und Ablaufzeitpunkt (iat, exp).
		 *
		 * @param array $payload Daten, die im Token gespeichert werden sollen (z.B. user_id).
		 * @return string Signiertes JWT als String.
		 *
		 * @todo Fehlerbehandlung hinzufuegen: JWT::encode() kann eine Exception werfen.
		 */
		public function generateToken(array $payload): string{
			$issuedAt = time();
			$payload = array_merge($payload, [
				'iat' => $issuedAt,
				'exp' => $issuedAt + $this->ttl
			]);
			
			return JWT::encode($payload, $this->secret, $this->algo);
		}
		
		/*
			Prüft, ob das JWT gültig und nicht abgelaufen ist.
			Gibt den Payload als Array zurück (z. B. ['user_id' => 42]).
			Gibt null zurück, wenn das Token ungültig ist oder abgelaufen.
		*/
		/**
		 * Prueft, ob ein JWT gueltig und nicht abgelaufen ist.
		 *
		 * Gibt den Payload als Array zurueck oder null bei Fehlern.
		 * Sendet im Fehlerfall automatisch eine HTTP-Fehlermeldung.
		 *
		 * @param string $token Das zu validierende JWT.
		 * @return array|null Decodiertes Payload oder null bei ungueltigem/abgelaufenem Token.
		 */
		public function validateToken(string $token): ?array{
			try{
				$decoded = JWT::decode($token, new Key($this->secret, $this->algo));
				return (array)$decoded;
			}catch(ExpiredException $e){
				http_response_code(401);
				echo json_encode(
					['error' => 'Token abgelaufen']
				);
				
				return null;
			}catch(\Exception $e){
				http_response_code(401);
				echo json_encode(
					['error' => 'Ungültiges Token']
				);
				
				return null;
			}
		}
		
		/**
		 * Extrahiert die Benutzer-ID aus einem gueltigen Token.
		 *
		 * Gibt null zurueck, wenn das Token ungueltig ist oder user_id fehlt.
		 *
		 * @param string $token Das zu pruefende Token.
		 * @return int|null Die Benutzer-ID oder null.
		 */
		public function getUserIdFromToken(string $token): ?int{
			$payload = $this->validateToken($token);
			return $payload['user_id'] ?? null;
		}
	}
?>