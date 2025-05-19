<?php
	define('BASE_PATH', dirname(__DIR__));

	//	Framework Module
	require_once BASE_PATH . '/vendor/autoload.php';
	require_once BASE_PATH . '/db.php';
	require_once BASE_PATH . '/funktionen.php';
	require_once BASE_PATH . '/JwtHandler.php';
	require_once BASE_PATH . '/auth.php';
	require_once BASE_PATH . '/todo.php';

	//	Umgebungsvariablen aus .env-Datei
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
	$dotenv->load();
?>