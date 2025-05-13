<?php
	//	Einbinden der db.php, um die getPDO() Funktion zu verwenden
	require_once 'auth.php';
	
	//	Die Eingabedaten verarbeiten
	$loginMessage = processLoginForm();
	
	//	Weiterleitung oder Fehlermeldung basierend auf dem Login-Ergebnis
	if($loginMessage === 'Login erfolgreich'){
		header('Location: dashboard.php');
		exit();
	}
?>

<!DOCTYPE html>
<html	lang="de">
	<head>
		<meta charset="utf-8">
		<title>Login</title>
	</head>
	
	<body>
		<form	action="login.php"	method="POST">
			<input	type="email"	name="email"	placeholder="E-Mail"	required>
			<input	type="password"	name="password"	placeholder="Passwort"	required>
			<button	type="submit">Einloggen</button>
		</form>
	</body>
</html>

<?php
	/*
	Alt oben ist neue Variante von login
	//	Einbinden der db.php, um die getPDO() Funktion zu verwenden
	require_once 'db.php';
	require_once 'funktionen.php';
	
	session_start();
	$pdo = getPDO();
	
	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		$email = trim($_POST['email']);
		$password = $_POST['password'];
		
		//	Email validieren - Funktion ist im Modul: ./funktionen.php
		$emailError = isValidEmail($email);
		if($emailError !== null){
			exit($emailError);
		}
		
		try{
			//	Benutzer aus der DB laden
			$stmt = $pdo->prepare("select id, password from users where email = ?");
			$stmt->execute([$email]);
			$user = $stmt->fetch();
			
			if(!$user || !password_verify($password, $user['password'])){
				exit('Email oder Passwort ist ungültig.');
			}
			
			//	Session starten
			$_SESSION['user_id'] = $user['id'];
			echo('Login erfolgreich');
			
			//	Weiterleitung
			header('Location: dashboard.php');
			exit;
		}catch(\PDOException $e){
			exit('Fehler beim Login: '.$e->getMessage());
		}
	}
	*/
?>