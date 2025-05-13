<?php
	require_once 'db.php';
	require_once 'funktionen.php';
	
	$pdo = Database::getConnection();
	
	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		//	Email und Passwort aus dem Formular holen
		$email = trim($_POST['email']);
		$password = $_POST['password'];
		
		//	Prüfen, ob Email regestriert ist (inkl. Validierung)
		$emailError = isEmailRegistered($pdo, $email);
		if($emailError !== null){
			//	Falls E-Mail ungültig oder bereits registriert
			exit($emailError);
		}
		
		try{
			//	Nutzer erstellen
			$createError = createUser($pdo, $email, $password);
			if($createError !== null){
				//	Falls beim Erstellen des Nutzers ein Fehler auftritt
				exit($createError);
			}
			echo('<br>Die Regestrierung war erfolgreich.<br>');
		}catch(\PDOException $e){
			//	Fehlerbehandlung, falls ein Fehler bei der DB-Abfrage oder beim
			//	Insert auftritt
			exit('Datenbankfehler: '.$e->getMessage());
		}
	}
?>

<DOCTYPE html>
<html	lang="de">
	<head>
		<meta charset="utf-8">
		<title>Regestrierung</title>
	</head>
	
	<body>
		<form	action="register.php"	method="POST">
			<input	type="email"	name="email"	placeholder="E-Mail"
				required
				pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
				title="Bitte gib eine gültige E-Mail-Adresse ein.">
			<input	type="password"	name="password"	placeholder="Passwort"
				required
				minlength="8"
				pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}"
				title="Mindestens 8 Zeichen, 1 Großbuchstabe, 1 Kleinbuchstabe und 1 Zahl">
			<button	type="submit">Registrieren</button>
		</form>
	</body>
</html>