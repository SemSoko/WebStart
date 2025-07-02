<?php
	namespace Shared\Auth;
	
	interface AuthServiceInterface{
		/**
		 * Gibt die aktuell authentifizierte User-ID zurueck
		 *
		 * @return int Die Benutzer-ID des aktuell eingeloggten Users
		 */
		public function getUserId(): int;
	}