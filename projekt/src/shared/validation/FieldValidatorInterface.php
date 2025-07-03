<?php
	namespace Shared\Validation;
	
	interface FieldValidatorInterface{
		/**
		 * Prueft, ob bestimmtes Feld im JSON-Input gesetzt und nicht leer ist.
		 *
		 * @param array $input JSON-Daten als assoziatives Array (z.B. aus json_decode)
		 * @param string $field Der zu pruefende Feldname
		 * @return bool true, wenn Feld vorhanden und nicht leer, sonst false
		 */
		public function hasRequiredField(array $input, string $field): bool;
		
		/**
		 * Gibt den bereinigten (getrimmten) Wert eines Feldes zurueck.
		 *
		 * @param array $input JSON-Daten als assoziatives Array
		 * @param string $field Der abzurufende Feldname
		 * @return string|null Der bereinigte Wert oder null, falls das Feld nicht existiert
		 */
		public function getValue(array $input, string $field): ?string;
	}