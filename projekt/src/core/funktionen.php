<?php
	/**
	 * Preuft, ob ein Passwort den definierten Sicherheitsanforderungen entspricht.
	 *
	 * Bedingungen:
	 * - Mindestens 8 Zeichen
	 * - Mindestens ein Grossbuchstabe, ein Kleinbuchstabe, eine Zahl, ein Sonderzeichen
	 * - Keine Leerzeichen erlaubt
	 *
	 * @param string $password Das zu ueberpruefende Passwort.
	 * @return string|null Fehlermeldung(en) als String oder null bei gueltigem Passwort.
	 *
	 * @todo
	 * Passwort als sensitiven Parameter kennzeichnen, um Klartext-Ausgaben z.B. im Logging
	 * zu vermeiden.
	 */
	function isValidPassword(string $password): ?string{
		$errors = [];
		
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
		
		return !empty($errors) ? implode('<br>', $errors) : null;
	}
	
	/**
	 * Prueft, ob eine E-Mail formal gueltig ist.
	 *
	 * @param string $email Die zu pruefende E-Mail.
	 * @return string|null Fehlermeldung bei ungueltiger Adresse, sonst null.
	 */
	function isValidEmail(string $email): ?string{
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			return 'Bitte eine gültige E-Mail-Adresse angeben.';
		}

		return null;
	}
	
	/**
	 * Prueft, ob eine E-Mail bereits in der Datenbank registriert ist.
	 *
	 * Fuehrt zuvor eine Validierung der E-Mail durch.
	 *
	 * @param PDO $pdo Die aktive PDO-Verbindung.
	 * @param string $email Die zu pruefende E-Mail.
	 * @return string|null Fehlermeldung bei bereits registrierter Adresse oder ungueltiger Eingabe, sonst null.
	 */
	function isEmailRegistered($pdo, $email){
		$emailError = isValidEmail($email);
		
		/*
		 * Prueft, ob E-Mail formal ungueltig ist.
		 */
		if($emailError !== null){
			return $emailError;
		}
		
		/*
		 * Abrufen der E-Mail aus der Datenbank.
		 */
		$stmt = $pdo->prepare("select id from users where email = ?");
		$stmt->execute([$email]);
		
		/*
		 * Gibt Fehler zurueck, wenn die E-Mail bereits existiert.
		 */
		if($stmt->fetch() !== false){
			return 'Diese E-Mail ist bereits registriert.';
		}
		
		return null;
	}
	
	/**
	 * Erstellt einen neuen Benutzer in der Datenbank.
	 * Validiert das Passwort und speichert alle Benutzerdaten sicher.
	 *
	 * @param PDO $pdo Die aktive PDO-Verbindung.
	 * @param string $email Die E-Mail des neuen Benutzers.
	 * @param string $password Das Passwort des neuen Benutzers.
	 * @param string $surname Der Nachname des Benutzers.
	 * @param string $firstName Der Vorname des Benutzers.
	 * @return string|null Fehlermeldung bei Fehlern, sonst null.
	 *
	 * @todo Validierung fuer Vor- und Nachnamen ergaenzen.
	 */
	function createUser($pdo, $email, $password, $surname, $firstName){
		$passwordError = isValidPassword($password);
		if($passwordError !== null){
			return $passwordError;
		}
		
		/*
		 * Hashen des Benutzerpassworts.
		 */
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		
		/*
		 * Benutzer in die Datenbank einfuegen.
		 *
		 * @todo In eine Hilfsfunktion auslagern.
		 */
		$stmt = $pdo->prepare("insert into users (email, password, surname, first_name) values (?, ?, ?, ?)");
		$stmt->execute([$email, $hashedPassword, $surname, $firstName]);
		
		return null;
	}
	
	/**
	 * Fuehrt eine Weiterleitung zur angegebenen URL durch und beendet das Skript.
	 *
	 * @param string $url Die Ziel-URL fuer die Weiterleitung.
	 * @return void
	 */
	function redirectTo($url){
		header("Location: $url");
		exit();
	}
?>