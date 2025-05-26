//	Dieser Eventlistener ist global zustaendig fuer das gesamte HTML-Dokument?
//	Also fuer alle Events, die innerhalb von register.html ausgeloest werden?
//	Das bedeutet dann, dass wir hier auf alle Events innerhalb von register.html
//	horchen und sobald etwas ausgeloest wurde, reagiert diese JS-Datei?
//	Brauchen wir dann eigentlich diesen document.addEventListener ?
//	Was wenn wir nur den form-Eventlistener, reicht dieser nicht aus?
//	Denn wir wollen doch nur auf dieses eine Event reagieren?
document.addEventListener("DOMContentLoaded", () => {
	const form = document.getElementById("registerForm");
	const message = document.getElementById("messageRegister");
	
	//	Das submit-Event wird ausgeloest wenn ich auf den button im HTML-Formular klicke,
	//	welcher vom type=submit ist, korrekt?
	//	Und wir fuegen hier einen Eventlistener hinzu, welcher auf Events reagiert,
	//	welche vom HTML-Formular ausgehen?
	form.addEventListener("submit", async (e) => {
		e.preventDefault();
		
		const email = form.email.value.trim();
		const password = form.password.value;
		const firstName = form.first_name.value;
		const surname = form.surname.value;
		
		try{
			//	Was passiert hier konkret bei der Anfrage?
			//	Also einmal den gesamten fetch-Prozess bitte erklaeren.
			//	Hier verpacken wir unsere Daten in ein JSON, dieses
			//	JSON-Objekt wird an den PHP-Endpunkt: register.php versendet?
			const response = await fetch("/api/register.php", {
				method: "POST",
				headers: {
					"Content-Type": "application/json"
				},
				body: JSON.stringify({email, password, firstName, surname})
			});
			
			//	Was genau bedeutet das?
			//	Pruefen wir hier, ob die Antwort JSON ist?
			//	Bzw ein vorbereitender Schritt, zur Pruefung, ob
			//	die Antwort vom Typ JSON ist.
			const contentType = response.headers.get("Content-Type") || "";
			
			//	Hier dann die eigentliche Pruefung, ob es JSON ist.
			if(contentType.includes("application/json")){
				const data = await response.json();
				console.log("Anwtwort vom Server:", data);
				
				//	Heisst hier response.ok, bzw der Inhalt davon, dass es
				//	JSON-konform ist? Oder was genau?
				if(response.ok){
					//	Optional: Weiterleitung
					//	window.location.href = __DIR__ . "/dashboard.php";
					message.textContent = "Registrierung erfolgreich!";
					window.location.href = "login.html";
				}else{
					message.textContent = data.error || "Registrierung fehlgeschlagen.";
				}
			}else{
				const text = await response.text();
				console.log("Fehlerantwort (kein JSON):", text);
				message.textContent = "Serverfehler: Unerwartete Antwort";
			}
			
		}catch(err){
			console.error("Fehler beim Registrieren:", err);
			message.textContent = "Netzwerkfehler oder Server nicht erreichbar.";
		}
	});
});