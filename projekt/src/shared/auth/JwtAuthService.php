<?php
	require_once __DIR__ . '/JwtHandler.php';

	namespace Shared\Auth;

	use Shared\Auth\JwtHandler;
	
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
		private JwtHandler $jwt;
		
		public function __construct(JwtHandler $jwt){
			$this->jwt = $jwt;
		}
		
		public function getUserId(): int{
			$token = JwtHandler::getBearerToken();
			if(!$token){
				throw new \RuntimeException('Token wurde nicht gefunden.');
			}
			
			$userData = $this->jwt->validateToken($token);
			$userId = $this->jwt->getUserIdFromToken;
			
			if($userData === null || $userId === null){
				throw new \RuntimeException('Token ungueltig oder User-ID fehlt.');
			}
			
			return $userId;
		}
	}