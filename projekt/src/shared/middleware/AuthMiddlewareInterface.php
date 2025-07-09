<?php
	namespace Shared\Middleware;
	
	/**
	 * Interface fuer Middleware, die eine Authentifizierung erzwingt.
	 */
	interface AuthMiddlewareInterface{
		/**
		 * Prueft einen gueltigen Token und liefert die Benutzer-ID.
		 * Gibt bei Fehler eine strukturierte Fehlermeldung aus und bricht ab.
		 *
		 * @return int Die extrahierte Benutzer-ID
		 */
		public function handle(): int;
	}