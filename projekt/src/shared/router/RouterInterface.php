<?php
	namespace Shared\Router;
	
	/**
	 * Definiert die Grundfunktionen fuer alle Modul-Router.
	 * Jeder Modul-Router kann damit Routen registrieren und Anfragen dispatchen.
	 */
	interface RouterInterface{
		/**
		 * Registriert eine neue Route.
		 *
		 * @param string $method HTTP-Methode (z.B. GET, POST)
		 * @param string $path URI-Pfad relativ zum Modul
		 * @param array|string $handler Ziel-Controller + Methode oder Sonderroute
		 * @return void
		 */
		public function add(string $method, string $path, array|string $handler): void;
		
		/**
		 * Fuehrt die passende Handler-Logik basierend auf Methode und URI aus.
		 *
		 * @param string $method HTTP-Methode der eingehenden Anfrage
		 * @param string $uri Pfad der eingehenden Anfrage (z.B.: aus $_SERVER)
		 * @return void
		 */
		public function dispatch(string $method, string $uri): void;
	}