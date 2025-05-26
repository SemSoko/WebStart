<?php
	namespace Core;
	
	use Firebase\JWT\JWT;
	use Firebase\JWT\Key;
	use Firebase\JWT\ExpiredException;
	
	class JwtHandler{
		private string $secret;
		private string $algo;
		//	time to live in Sekunden
		private int $ttl;
		
		/*
			Wird beim Erstellen des Objekts aufgerufen.
			Initialisiert den Secret Key, das Signaturverfahren und die
			Lebensdauer (ttl) des Tokens.
		*/
		public function __construct(){
			$this->secret = getenv('JWT_SECRET');
			$this->algo = 'HS256';
			//	ttl - 24 Stunden
			$this->ttl = 60 * 60 * 24;
		}
		
		/*
			Erstellt ein JWT.
			Fügt automatisch das Erstellungsdatum (iat) und das Ablaufdatum
			(exp) hinzu. Gibt ein signiertes JWT als String zurück.
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
		public function validateToken(string $token): ?array{
			try{
				$decoded = JWT::decode($token, new Key($this->secret, $this->algo));
				return (array)$decoded;
			}catch(ExpiredException $e){
				http_response_code(401);
				echo json_encode(['error' => 'Token abgelaufen']);
				
				return null;
			}catch(\Exception $e){
				http_response_code(401);
				echo json_encode(['error' => 'Ungültiges Token']);
				
				return null;
			}
		}
		
		/*
			Extrahiert die user_id aus dem Token.
			Gibt null zurück, wenn das Token ungültig ist.
		*/
		public function getUserIdFromToken(string $token): ?int{
			$payload = $this->validateToken($token);
			
			return $payload['user_id'] ?? null;
		}
	}
?>