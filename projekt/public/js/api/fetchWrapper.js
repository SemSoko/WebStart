export async apiRequest(endpoint, method='GET', body=null, token=null){
	// Das signalisiert dem Server: „Ich sende dir JSON-Daten“
	// Header-Objekt initialisieren, standardmäßig mit JSON als Content-Type
	const headers = {
		"Content-Type": "application/json"
	};
	// Optionaler Header hinzufügen, wenn token gesetzt ist
	// Bearer ist das standardisierte Schema für den Authorization-Header
	if(token){
		headers["Authorization"] = `Bearer ${token}`;
	}
	
	// Optionen für fetch() vorbereiten (HTTP-Methode + Header)
	const options = {
		method,
		headers
	};
	
	// Falls ein Body vorhanden ist, als JSON-String anhängen
	if(body){
		options.body = JSON.stringify(body);
	}
	
	// fetch()-Aufruf mit vorbereitetem Endpoint und Optionen
	const response = await fetch(endpoint, options);
	
	// Content-Type prüfen, um zu entscheiden, ob wir JSON oder Text erwarten
	const contentType = response.headers.get('Content-Type') || "";
	
	if(contentType.includes("application/json")){
		// Wenn JSON-Antwort -> als Objekt zurückgeben
		const data = await response.json();
		return data;
	}else{
		// Wenn kein JSON -> Text extrahieren und in Objekt verpackt zurückgeben
		const text = await response.text();
		return{
			error: true,
			responseText: text
		};
	}
}