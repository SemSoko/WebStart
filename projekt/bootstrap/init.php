<?php
	//	Basis-Pfad zum Projektverzeichnis (/var/www/app)
	//	__DIR__ verweist auf das aktuelle Verzeichnis (bootstrap)
	//	dirname(__DIR__) geht eine Ebene höher -> /var/www/app
	//	BASE_PATH kann im gesamten Projekt für Pfadangaben verwendet werden
	define('BASE_PATH', dirname(__DIR__));
	
	//	Lade Composer-Autoloader fuer alle Abhaengigkeiten (z.B. dotenv, JWT etc.)
	require_once BASE_PATH . '/vendor/autoload.php';
	
	//	Lade interne Projektmodule
	require_once BASE_PATH . '/src/core/db.php';
	require_once BASE_PATH . '/src/core/funktionen.php';
	require_once BASE_PATH . '/src/core/JwtHandler.php';
	require_once BASE_PATH . '/src/auth/auth.php';
	require_once BASE_PATH . '/src/todo/todo.php';

	/*
	//	Lade Umgebungsvariablen aus .env-Datei im Root-Verzeichnis (/var/www/.env)
	try{
		$dotenv = Dotenv\Dotenv::createImmutable(dirname(BASE_PATH));
		$dotenv->load();
		
	}catch(Exception $e){
		die('Environment file could not be loaded: '.$e->getMessage());
	}
	*/
?>