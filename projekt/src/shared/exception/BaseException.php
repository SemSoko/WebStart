<?php

	namespace Shared\Exception;
	
	abstract class BaseException extends \Exception{
		/**
		 * Gibt den passenden HTTP-Statuscode fuer diesen Fehler zurueck.
		 *
		 * @return int
		 * Der HTTP-Statuscode (z.B. 404 fuer NotFound, 400 fuer Validation, ...).
		 */
		public function getStatusCode(): int;
	}