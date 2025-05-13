<?php
	session_start();
	session_unset();
	session_destroy();
	
	//	Zurück zur Login-Seite
	header('Location: login.php');
	exit();
?>