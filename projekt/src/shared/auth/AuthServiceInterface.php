<?php
	namespace Shared\Auth;
	
	interface AuthServiceInterface{
		/**
		 * Gibt die User-ID aus dem uebergebenen Token zurueck oder null bei Fehler.
		 *
		 * @param string $token
		 * @return int Die Benutzer-ID des aktuell eingeloggten Users oder null
		 */
		public function getUserId(string $token): ?int;
	}