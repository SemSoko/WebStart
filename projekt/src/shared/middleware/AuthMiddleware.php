<?php
	namespace Shared\Middleware;

	require_once __DIR__ . '/../response/ResponseHandlerInterface.php';
	require_once __DIR__ . '/../auth/AuthServiceInterface.php';
	require_once __DIR__ . '/../http/RequestTokenReaderInterface.php';
	require_once __DIR__ . '/AuthMiddlewareInterface.php';
	
	use Shared\Response\ResponseHandlerInterface;
	use Shared\Auth\AuthServiceInterface;
	use Shared\Http\RequestTokenReaderInterface;
	use Shared\Middleware\AuthMiddlewareInterface.php
	
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
	class AuthMiddleware implements AuthMiddlewareInterface{
		private AuthServiceInterface $authService;
		private ResponseHandlerInterface $response;
		private RequestTokenReaderInterface $tokenReader;
		
		public function __construct(AuthServiceInterface $authService,
									ResponseHandlerInterface $response,
									RequestTokenReaderInterface $tokenReader){
			$this->authService = $authService;
			$this->response = $response;
			$this->tokenReader = $tokenReader;
		}
		
		/**
		 * Fuehrt die Authentifizierungspruefung durch.
		 * Wenn kein gueltiger Token vorliegt, wird der Zugriff abgebrochen.
		 *
		 * @return void Gibt bei Fehler eine strukturierte Antwort und beendet das Script.
		 */
		public function handle(): int{
			$token = $this->tokenReader->getBearerToken();
			
			if(!$token){
				// Entwicklermodus:
				$this->response->debug(
					'Kein Token uebergeben', [
						'success' => false,
						'source' => 'middleware/AuthMiddleware'
					], 401
				);
				// Produktionsmodus:
				// $this->response->error('Authentifizierung fehlgeschlagen', 401);
			}
			
			$userId = $this->authService->getUserId($token);
			
			if($userId === null){
				$this->response->debug('Ungueltiger oder abgelaufener Token', [
					'success' => false,
					'source' => 'middleware/AuthMiddleware'
				], 401);
			}
			
			return $userId;
		}
	}