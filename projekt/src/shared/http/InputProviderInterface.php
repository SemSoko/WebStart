<?php
	namespace Shared\Http;
	
	/**
	 * Schnistelle fuer Komponenten, die JSON-Daten aus HTTP-Requests
	 * bereitstellen.
	 *
	 * Dient z.B. als Basis fuer testbare Request-Eingabehelfer.
	 *
	 * @package Shared\Http
	 */
	interface InputProviderInterface{
		/**
		 * Liest und dekodiert den JSON-Request-Body.
		 *
		 * Erwartet typischerweise Inhalte aus php://input.
		 * Rueckgabe ist null, wenn der Body leer oder ungueltig ist.
		 *
		 * @return array|null
		 * Der dekodierte JSON-Body als array oder null bei Fehlern.
		 */
		public function getJsonBody(): ?array;
	}