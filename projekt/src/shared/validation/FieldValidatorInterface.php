<?php
	namespace Shared\Validation;
	
	/**
	 * Schnittstelle fuer Validierungs-Services, die mit JSON-Eingaben arbeiten.
	 *
	 * Dient der Pruefung und Extraktion einzelner Felder aus einem JSON-Body.
	 *
	 * @package Shared\Validation
	 */
	interface FieldValidatorInterface{
		/**
		 * Prueft, ob bestimmtes Feld im JSON-Eingabeobjekt gesetzt und nicht leer ist.
		 *
		 * Typisch fuer Pflichtfeldpruefungen in POST-/Put-Requests.
		 *
		 * @param array $input JSON-Daten als Array (z.B.: aus json_decode()).
		 * @param string $field Der zu pruefende Feldname.
		 * @return bool true, wenn Feld vorhanden und nicht leer, sonst false.
		 */
		public function hasRequiredField(array $input, string $field): bool;
		
		/**
		 * Gibt den bereinigten (getrimmten) Wert eines Feldes zurueck.
		 *
		 * Nuetzlich zur Weiterverarbeitung Validierung.
		 *
		 * @param array $input JSON-Daten als Array.
		 * @param string $field Der abzurufende Feldname.
		 * @return string|null Getrimmter Wert oder null, wenn das Feld fehlt.
		 */
		public function getValue(array $input, string $field): ?string;
	}