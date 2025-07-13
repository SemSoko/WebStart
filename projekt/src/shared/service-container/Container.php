<?php
	namespace Shared\ServiceContainer;
	
	/**
	 * Minimaler Service-Container fuer manuelle Dependency Injection
	 *
	 * - Verwaltet Registrierungen (z.B. Controller, Middleware, Services)
	 * - Baut Instanzen ueber Closures (Lazy Instantiation)
	 * - Unterstuetzt kein Autowiring / kein Caching - bewusst einfach gehalten
	 */
	class Container{
		/**
		 * Registrierte Factory-Funktionen
		 *
		 * @var array<string, callable>
		 */
		private array $factories = [];
		
		/**
		 * Bereits erzeugte Instanzen
		 *
		 * @var array<string, mixed>
		 */
		private array $instances = [];
		
		/**
		 * Registriert eine Factory-Funktion unter einem Schluessel
		 */
		public function register(string $id, callable $factory): void{
			$this->factories[$id] = $factory;
		}
		
		/**
		 * Gibt eine Instanz zum Schluessel zurueck (oder erstellt sie bei Bedarf)
		 */
		public function get(string $id): mixed{
			if(!isset($this->instances[$id])){
				if(!isset($this->factories[$id])){
					throw new \RuntimeException('No service registered for key: $id');
				}
				
				$this->instances[$id] = ($this->factories[$id]($this));
			}
			
			return $this->instances[$id];
		}
	}