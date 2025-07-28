<?php
	namespace Shared\Exception;
	
	require_once __DIR__ . '/BaseException.php';
	
	use Shared\Exception\BaseException;
	
	class RouteNotFoundException extends BaseException{
		public function getStatusCode(): int{
			/*
			 * Wir geben bewusst 404, weil das die URI selbst
			 * betrifft, nicht die Payload oder Auth.
			 */
			return 404;
		}
	}