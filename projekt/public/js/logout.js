//	In Erweiterungen noch einfuegen, dass logout-System um:
//	-	Blacklisting,
//	-	Redis
//	zu erweitern.
document.addEventListener("DOMContentLoaded", () => {
	const logoutBtn = document.getElementById("logoutBtn");
	
	if(logoutBtn){
		logoutBtn.addEventListener("click", () => {
			//	Token loeschen
			localStorage.removeItem("jwt_token");
			//	Zueruck zur Login-Seite
			window.location.href = "login.html";
		});
	}
});