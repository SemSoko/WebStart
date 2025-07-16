<?php
	namespace Shared\ServiceContainer;
	
	/**
	 * Zentrale Konstantensammlung fuer Service-IDs im Dependency-Injection-Container.
	 *
	 * Dient der typsicheren Registrierung und Abfrage von Services ohne Magic Strings.
	 * Erhoeht Lesbarkeit, reduziert Tippfehler, verbessert Autovervollstaendigung.
	 *
	 * @package Shared\ServiceContainer
	 */
	final class ServiceIds{
		public const RESPONSE				= 'response';
		public const PDO					= 'pdo';
		public const TODO_REPO				= 'todo-repo';
		public const TODO_SERVICE			= 'todo-service';
		public const JWT_HANDLER			= 'jwt_handler';
		public const AUTH_SERVICE			= 'auth-service';
		public const AUTH_MIDDLEWARE		= 'auth-middleware';
		public const INPUT					= 'input';
		public const VALIDATOR				= 'validator';
		public const VALIDATION_MIDDLEWARE	= 'validation-middleware';
		public const TODO_CONTROLLER		= 'todo-controller';
		public const REQUEST_TOKEN_READER	= 'request_token_reader';
	}