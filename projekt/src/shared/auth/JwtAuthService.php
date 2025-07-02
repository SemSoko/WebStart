<?php
	require_once __DIR__ . '/JwtHandler-new.php';

	namespace Shared\Auth;

	use Shared\Auth\JwtHandlerNew;
	
	class JwtAuthService implements AuthServiceInterface{
		/*
		 * Damit wird eine Instanz von JwtHandler an den Service uebergeben.
		 * Vorteile:
		 * - wir koennen verschiedene JwtHandler-Konfigurationen
		 * durchreichen (anderer Algo, TTL usw.)
		 * - das erhoeht die Testbarkeit (wir koennten z.B. MockJwtHandler
		 * uebergeben)
		 * - macht das System klarer steuerbar: JwtHandler ist keine globale
		 * Hilfe mehr, sondern wird kontrolliert genutzt
		 */
		private JwtHandlerNew $jwt;
		
		public function __construct(JwtHandlerNew $jwt){
			$this->jwt = $jwt;
		}
		
		public function getUserId(string $token): ?int{
			$userData = $this->jwt->validateToken($token);
			$userId = $this->jwt->getUserIdFromToken($token);
			
			if($userData === null || $userId === null){
				throw new \RuntimeException('Token ungueltig oder User-ID fehlt.');
			}
			
			return $userId;
		}
	}