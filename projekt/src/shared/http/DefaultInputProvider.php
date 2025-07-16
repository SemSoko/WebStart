<?php
	namespace Shared\Http;
	
	require_once __DIR__ . '/InputProviderInterface.php';
	
	use Shared\Http\InputProviderInterface;
	
	/**
	 * Standardimplementierung des InputProviderInterface.
	 *
	 * Liest den JSON-Request-Body einmalig aus php://input und cached ihn
	 * zur Wiederverwendung.
	 *
	 * @package Shared\Http
	 */
	class DefaultInputProvider implements InputProviderInterface{
		/**
		 * Zwischenspeicher fuer bereits eingelesenen JSON-Body.
		 *
		 * @var array|null
		 */
		private ?array $cachedInput = null;
		
		/**
		 * Gibt den dekodierten JSON-Body zurueck.
		 *
		 * Der Inhalt wird einmalig gelesen und fuer Folgezugriffe gecached.
		 * Rueckgabe ist null, wenn der Input leer oder ungueltig ist.
		 *
		 * @return array|null JSON-Daten als Array oder null bei Fehlern.
		 */
		public function getJsonBody(): ?array{
			if($this->cachedInput === null){
				$raw = $this->getRawInput();
				$this->cachedInput = json_decode($raw, true);
			}
			
			return $this->cachedInput;
		}
		
		/**
		 * Liest den Rohinhalt des HTTP-Request-Bodys.
		 *
		 * Verwendet php://input fuer das einmalige Einlesen roher Daten.
		 *
		 * @return string Der rohe Request-Body.
		 */
		protected function getRawInput(): string{
			return file_get_contents('php://input');
		}
	}