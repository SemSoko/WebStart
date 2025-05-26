<?php
	require_once __DIR__ . '/../../bootstrap/init.php';

	header('Content-Type: application/json');
	
	if($_SERVER['REQUEST_METHOD'] !== 'POST'){
		//	Methode nicht erlaubt
		http_response_code(405);
		echo json_encode(['error' => 'Nur POST-Anfragen erlaubt']);
		exit();
	}
	
	//	JSON-Daten auslesen
	$input = json_decode(file_get_contents('php://input'), true);
	$email = $input['email'] ?? '';
	$password = $input['password'] ?? '';
	
	if(!$email || !$password){
		//	Bad Request
		http_response_code(400);
		echo json_encode(['error' => 'E-Mail oder Passwort sind erforderlich']);
		exit();
	}
	
	//	Login durchfuehren
	$loginMessage = processLoginForm();
	if(isset($loginMessage['error'])){
		//	oder 401 je nach Fehlerart
		http_response_code(400);
	}else{
		http_response_code(200);
	}
	echo json_encode($loginMessage);
?>

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