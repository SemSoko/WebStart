<?php
	namespace Shared\ServiceContainer;
	
	/**
	 * Minimaler Service-Container fuer manuelle Dependency Injection.
	 *
	 * - Verwaltet benannte Service-Registrierungen ueber Closures (Lazy Instantiation)
	 * - Speichert bereits erzeugte Instanzen fuer Wiederverwendung (Singletion-artig)
	 * - Unterstuetzt kein Autowiring und Caching - bewusst einfach gehalten
	 *
	 * Beispiel:
	 * $container-register('user-service', fn(Container $c) => new UserService());
	 * $userService = $container->get('user-service');
	 *
	 * @package Shared\ServiceContainer
	 */
	class Container{
		/**
		 * Registry von Service-Factories (Closures), jeweils registriert unter
		 * einem String-Key.
		 *
		 * @var array<string, callable>
		 */
		private array $factories = [];
		
		/**
		 * Cache fuer bereits erzeugte Instanzen.
		 *
		 * @var array<string, mixed>
		 */
		private array $instances = [];
		
		/**
		 * Registriert eine Factory-Funktion unter einer eindeutigen ID.
		 *
		 * @param string $id
		 * Eindeutiger Schluessel fuer den Service (z.B. 'todo-service').
		 *
		 * @param callable $factory
		 * Funktion, die den Service erstellt. Bekommt Container als Argument.
		 */
		public function register(string $id, callable $factory): void{
			$this->factories[$id] = $factory;
		}
		
		/**
		 * Gibt eine Instanz zu einem registrierten Schluessel zurueck.
		 *
		 * Erstellt die Instanz bei erstmaligem Zugriff via Factory-Funktion (Lazy Instantiation).
		 * Danach wird die gleiche Instanz zurueckgegeben (kein mehrfaces Bauen).
		 *
		 * @param string $id Schluessel des angefragten Services.
		 * @return mixed Die zugehoerige Instanz.
		 *
		 * @throws \RuntimeException Wenn keine Factory fuer den Schluessel registriert ist.
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