<?php
	function isValidPassword(string $password): ?string{
		$errors = [];
		
		// Alle Bedingungen werden einzeln überprüft
		if(strlen($password) < 8){
			$errors[] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
		}
		
		if(!preg_match('/[A-Z]/', $password)){
			$errors[] = 'Das Passwort muss mindestens einen Großbuchstaben enthalten.';
		}
		
		if(!preg_match('/[a-z]/', $password)){
			$errors[] = 'Das Passwort muss mindestens einen Kleinbuchstaben enthalten.';
		}
		
		if(!preg_match('/[0-9]/', $password)){
			$errors[] = 'Das Passwort muss mindestens eine Zahl enthalten.';
		}
		
		if(!preg_match('/[^\w\s]/', $password)){
			$errors[] = 'Das Passwort muss mindestens ein Sonderzeichen enthalten.';
		}
		
		if(preg_match('/\s/', $password)){
			$errors[] = 'Das Passwort darf keine Leerzeichen enthalten.';
		}
		
		//	Wenn alles in Ordnung
		return !empty($errors) ? implode('<br>', $errors) : null;
	}
	
	function isValidEmail(string $email): ?string{
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			return 'Bitte eine gültige E-Mail-Adresse angeben.';
		}
		
		//	Wenn alles in Ordnung
		return null;
	}
	
	//	Funktion zum Überprüfen, ob die E-Mail bereits registriert ist
	function isEmailRegistered($pdo, $email){
		//	Email validieren
		$emailError = isValidEmail($email);
		if($emailError !== null){
			//	Falls die E-Mail ungültig ist
			return $emailError;
		}
		
		$stmt = $pdo->prepare("select id from users where email = ?");
		$stmt->execute([$email]);
		
		//	Wenn die E-Mail bereits existiert, geben wir eine Fehlermeldung zurück
		if($stmt->fetch() !== false){
			return 'Diese E-Mail ist bereits registriert.';
		}
		
		return null;
	}
	
	//	Funktion zum Erstellen eines neuen Nutzers
	//	UNBEDINGT ANPASSEN, AKTUELL NUR PROVISORISCHE LOESUNG
	//	Vor- und Nachname noch validieren
	function createUser($pdo, $email, $password, $surname, $firstName){
		//	Passwort validieren
		$passwordError = isValidPassword($password);
		if($passwordError !== null){
			return $passwordError;
		}
		
		//	Passwort sicher hashen
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		
		
		//	Nutzer in die DB einfügen
		$stmt = $pdo->prepare("insert into users (email, password, surname, first_name) values (?, ?, ?, ?)");
		$stmt->execute([$email, $hashedPassword, $surname, $firstName]);
		
		//	Keine Fehler, null zurückgeben
		return null;
	}
	
	function redirectTo($url){
		header("Location: $url");
		exit();
	}
?>