document.addEventListener("DOMContentLoaded", () => {
	const form = document.getElementById("loginForm");
	const message = document.getElementById("messageLogin");
	
	form.addEventListener("submit", async (e) => {
		e.preventDefault();
		
		const email = form.email.value.trim();
		const password = form.password.value;
		
		try{
			const response = await fetch("/api/login.php", {
				method: "POST",
				headers: {
					"Content-Type": "application/json"
				},
				body: JSON.stringify({email, password})
			});
			
			const contentType = response.headers.get("Content-Type") || "";
			
			if(contentType.includes("application/json")){
				const data = await response.json();
				console.log("Anwtwort vom Server:", data);
				
				if(response.ok && data.token){
					localStorage.setItem("jwt_token", data.token);
					//	Optional: Weiterleitung
					window.location.href = "dashboard.html";
					message.textContent = "Login erfolgreich!";
				}else{
					message.textContent = data.error || "Login fehlgeschlagen.";
				}
			}else{
				const text = await response.text();
				console.log("Fehlerantwort (kein JSON):", text);
				message.textContent = "Serverfehler: Unerwartete Antwort";
			}
			
		}catch(err){
			console.error("Fehler beim Login:", err);
			message.textContent = "Netzwerkfehler oder Server nicht erreichbar.";
		}
	});
});