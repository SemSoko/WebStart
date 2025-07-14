<?php
	namespace Shared\ServiceContainer;
	
	/**
	 * Einheitliche Service-IDs fuer den DI-Container
	 *
	 * Vermeidet Tippfehler und bietet Autovervollstaendigung bei der Nutzung
	 */
	final class ServiceIds{
		public const RESPONSE				= 'response';
		public const PDO					= 'pdo';
		public const TODO_REPO				= 'todo-repo';
		public const TODO_SERVICE			= 'todo-service';
		// JWT
		public const AUTH_SERVICE			= 'auth-service';
		public const AUTH_MIDDLEWARE		= 'auth-middleware';
		public const INPUT					= 'input';
		public const VALIDATOR				= 'validator';
		public const VALIDATION_MIDDLEWARE	= 'validation-middleware';
		public const TODO_CONTROLLER		= 'todo-controller';
		public const REQUEST_TOKEN_READER	= 'request_token_reader';
	}