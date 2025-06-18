<?php
	require_once __DIR__ . '/../auth/JwtHandler.php';
	require_once __DIR__ . '/../response/Response.php';

	namespace Shared\Middleware;
	
	use Shared\Auth\JwtHandler;
	use Shared\Response\Response;
	
	/**
	 * Middleware zur Authentifizierung geschuetzter Endpunkt.
	 *
	 * Diese Klasse verhindert den Zugriff auf bestimmte Routen,
	 * wenn kein gueltiger JWT-Token vorliegt.
	 *
	 * Bei Erfolg wird die User-ID zurueckgegeben.
	 * Bei Fehler wird eine strukturierte Fehlermeldung gesendet und
	 * die Ausfuehrung beendet.
	 *
	 * Beispiel-Rueckgabe bei Erfolg:
	 * return 42
	 *
	 * Beispielantwort bei Fehler:
	 * {
	 *    'error': 'Authentifizierung fehlgeschlagen',
	 *    'debug': { ... },
	 *    'source': 'shared/auth/requireValidUserId'
	 * }
	 *
	 * HTTP-Status: 401 Unauthorized
	 *
	 * @return
	 * int Die Benutzer-ID aus dem Token bei erfolgreicher Authentifizierung.
	 */
	class AuthMiddleware{
		/**
		 * Fuehrt die Authentifizierungspruefung durch.
		 * Wenn kein gueltiger Token vorliegt, wird der Zugriff abgebrochen.
		 *
		 * @return void Gibt bei Fehler eine strukturierte Antwort und beendet das Script.
		 */
		public static function handle(): int{
			$authResult = JwtHandler::requireValidUserId();
			
			if(!($authResult['success'] ?? false)){
				/*
				 * Sofort beenden bei Fehler
				 */
				 // Entwicklermodus:
				Response::debug('Authentifizierung fehlgeschlagen', $authResult, 401);
				// Produktionsmodus:
				// Response:error('Authentifizierung fehlgeschlagen', 401);
			}
			
			return $authResult['user_id'];
		}
	}