<?php
	namespace Shared\Auth;

	require_once __DIR__ . '/AuthServiceInterface.php';
	require_once __DIR__ . '/JwtHandler-new.php';

	use Shared\Auth\JwtHandlerNew;
	
	/**
	 * Konkrete Implementierung des Authentifizierungsdienstes via JWT.
	 *
	 * Nutzt eine uebergebene JwtHandlerNew-Instanz zur Validierung
	 * von Tokens und zur Extraktion der Benutzer-ID.
	 *
	 * Vorteile:
	 * - Ermoeglicht Konfigurierbarkeit (Algo, TTL, etc.)
	 * - Erhoeht Testbarkeit (z.B.: durch Mocks)
	 * - Kein globaler Zustand - volle DI-Kompatibilitaet
	 *
	 * @package Shared\Auth
	 */
	class JwtAuthService implements AuthServiceInterface{
		/**
		 * JWT-Handler fuer Token-Validierung und Extraktion.
		 *
		 * @var JwtHandlerNew
		 */
		private JwtHandlerNew $jwt;
		
		/**
		 * Initialisiert den Service mit einem konfigurierbaren JWT-Handler.
		 *
		 * @param JwtHandlerNew $jet Instanz fuer Tokenverarbeitung.
		 */
		public function __construct(JwtHandlerNew $jwt){
			$this->jwt = $jwt;
		}
		
		/**
		 * Extrahiert die Benutzer-ID aus einem gueltigen Token.
		 *
		 * @param string $token JWT-Token, z.B. aus dem Authorization-Header.
		 * @return int|null Benutzer-ID bei Erfolg.
		 *
		 * @throws \RuntimeException Wenn das Token ungueltig ist oder keine ID enthaelt.
		 */
		public function getUserId(string $token): ?int{
			$userData = $this->jwt->validateToken($token);
			$userId = $this->jwt->getUserIdFromToken($token);
			
			if($userData === null || $userId === null){
				throw new \RuntimeException('Token ungueltig oder User-ID fehlt.');
			}
			
			return $userId;
		}
	}