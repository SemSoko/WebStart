<?php
	namespace Shared\Auth;
	
	/**
	 * Interface fuer Authentifizierungsdienste (z.B.: JWT, Session).
	 *
	 * Definiert eine Methode zur Extraktion der Benutzer-ID aus einem Authentifizierungstoken.
	 *
	 * @package Shared\Auth;
	 */
	interface AuthServiceInterface{
		/**
		 * Extrahiert die Benutzer-ID aus einem gueltigen Authentifizierungstoken.
		 *
		 * @param string $token Das zu analysierende Authentifizierungstoken.
		 * @return int Benutzer-ID bei Erfolg, sonst null (z.B. bei Fehler oder ungueltigem Token).
		 */
		public function getUserId(string $token): ?int;
	}