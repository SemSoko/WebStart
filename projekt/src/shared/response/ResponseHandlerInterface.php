<?php
	namespace Shared\Response;
	
	interface ResponseHandlerInterface{
		public function success(array $data = [], int $statusCode = 200): void;
		public function error(string $message, int $statusCode = 400): void;
		public function debug(string $message, array $details = [], int $statusCode = 500): void;
		public function status(string $message = 'OK', bool $success = true, int $statusCode = 200): void;
	}