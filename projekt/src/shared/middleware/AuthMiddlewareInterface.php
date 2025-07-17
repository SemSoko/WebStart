<?php
	namespace Shared\Middleware;
	
	/**
	 * Schnittstelle fuer manuell ausgeloeste Authentifizierungspruefungen
	 * innerhalb von Controllern.
	 *
	 * Wird typischerweise innerhalb einer Controller-Methode aufgerufen,
	 * um Zugriff auf geschuetzte Ressourcen abzusichern.
	 *
	 * @package Shared\Middleware
	 */
	interface AuthMiddlewareInterface{
		/**
		 * Prueft das Authentifizierungstoken und gibt die Benutzer-ID zurueck.
		 *
		 * Bei Fehlern wird die Ausfuehrung beendet.
		 *
		 * @return int Die extrahierte Benutzer-ID.
		 */
		public function handle(): int;
	}