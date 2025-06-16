<?php
	namespace Shared/Auth;
	
	use Firebase\JWT\JWT;
	use Firebase\JWT\KEY;
	use Firebase\JWT\ExpiredException;
	
	class JwtHandler{
		private string $secret;
		private string $algo;
		private int $ttl;
		
		public function __construct(){
			$this->secret = getenv('JWT_SECRET');
			$this->algo = 'HS256';
			$this->ttl = 60 * 60 * 24;
		}
		
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
		
		public function getUserIdFromToken(string $token): ?int{
			$payload = $this->validateToken($token);
			return $payload['user_id'] ?? null;
		}
		
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