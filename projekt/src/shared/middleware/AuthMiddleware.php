<?php
	namespace Shared\Middleware;

	require_once __DIR__ . '/../response/ResponseHandlerInterface.php';
	require_once __DIR__ . '/../auth/AuthServiceInterface.php';
	require_once __DIR__ . '/../http/RequestTokenReaderInterface.php';
	require_once __DIR__ . '/AuthMiddlewareInterface.php';
	
	use Shared\Response\ResponseHandlerInterface;
	use Shared\Auth\AuthServiceInterface;
	use Shared\Http\RequestTokenReaderInterface;
	use Shared\Middleware\AuthMiddlewareInterface;
	
	/**
	 * Middleware zur Authentifizierung geschuetzter Aktionen.
	 *
	 * Diese Klasse wird innerhalb eines Controllers aufgerufen,
	 * um sicherzustellen, dass ein gueltiger Bearer-Token uebergeben wurde.
	 *
	 * Bei erfolgreicher Pruefung wird die Benutzer-ID aus dem Token zurueckgegeben.
	 * Bei Fehlern wird die Ausfuehrung beendet.
	 *
	 * Beispiel bei Fehler (Entwicklermodus):
	 * {
	 *	  'success': false,
	 *    'error': 'Authentifizierung fehlgeschlagen',
	 *    'debug': { ... },
	 *    'source': 'middleware/AuthMiddleware'
	 * }
	 *
	 * @package Shared\Middleware
	 */
	class AuthMiddleware implements AuthMiddlewareInterface{
		/**
		 * Dienst zur Authentifizierung und Extraktion der Benutzer-ID.
		 *
		 * @var AuthServiceInterface
		 */
		private AuthServiceInterface $authService;
		
		/**
		 * Dienst zur Ausgabe strukturierter JSON-Antworten.
		 *
		 * @var ResponseHandlerInterface
		 */
		private ResponseHandlerInterface $response;
		
		/**
		 * Dienst zur Extraktion des Bearer-Tokens aus dem Authorization-Header.
		 *
		 * @var RequestTokenReaderInterface
		 */
		private RequestTokenReaderInterface $tokenReader;
		
		/**
		 * Initialisiert die Middleware mit den benoetigten Abhaengigkeiten.
		 *
		 * @param AuthServiceInterface $authService
		 * Zum extrahieren der Benutzer-ID aus dem Token.
		 *
		 * @param ResponseHandlerInterface $response
		 * Fuer strukturierte Fehlerausgaben.
		 *
		 * @param RequestTokenReaderInterface $tokenReader
		 * Liest den Authorization-Header aus.
		 */
		public function __construct(
			AuthServiceInterface $authService,
			ResponseHandlerInterface $response,
			RequestTokenReaderInterface $tokenReader
		){
			$this->authService = $authService;
			$this->response = $response;
			$this->tokenReader = $tokenReader;
		}
		
		/**
		 * Fuehrt eine Authentifizierungspruefung durch und gibt die Benutzer-ID zurueck.
		 *
		 * Gibt bei Fehler automatisch eine Fehlerantwort im JSON-Format aus und
		 * beendet die Ausfuehrung.
		 *
		 * @return int Die extrahierte Benutzer-ID bei erfolgreicher Authentifizierung.
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