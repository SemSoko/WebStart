<?php
	namespace Shared\Validation;
	
	require_once __DIR__ . '/FieldValidatorInterface.php';
	
	/**
	 * Laufzeitvalidierung von JSON-Eingaben (z.B.: Pflichtfelder).
	 *
	 * Implementiert FieldValidatorInterface zur Integration in DI-Systeme.
	 * Stellt Hilfsmethoden bereit, um typische Formulareingaben zu pruefen
	 * und bereinigt auszulesen.
	 *
	 * @package Shared\Validation
	 */
	class JsonFieldValidator implements FieldValidatorInterface{
		/*
		 * Prueft, ob ein Pflichtfeld vorhanden und nicht leer ist.
		 *
		 * Der Wert wird getrimmt - leere Strings oder reine Leerzeichen
		 * zaehlen als "leer".
		 *
		 * @param array $input JSON-Eingabedaten (z.B. aus json_decode).
		 * @param string $field Feldname, das geprueft werden soll (z.B.: 'title').
		 * @return bool true, wenn das Feld existiert und nicht leer, sonst false.
		 */
		public function hasRequiredField(array $input, string $field): bool{
			if(!isset($input[$field])){
				return false;
			}
			
			return trim($input[$field]) !== '';
		}
		
		/**
		 * Gibt den bereinigten (getrimmten) Wert eines Feldes zurueck.
		 *
		 * @param array $input JSON-Eingabedaten.
		 * @param string $field Der abzurufende Feldname.
		 * @return string|null Getrimmter Wert oder null, wenn das Feld nicht vorhanden ist.
		 */
		public function getValue(array $input, string $field): ?string{
			if(!isset($input[$field])){
				return null;
			}
			
			return trim($input[$field]);
		}
	}