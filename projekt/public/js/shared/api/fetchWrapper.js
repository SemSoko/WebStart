/**
 * @typedef {Object} ApiError
 * @property {boolean} error - Zeigt an, dass ein Fehler aufgetreten ist.
 * @property {string} responseText - Die Antwort des Servers als Text.
 */

/**
 * Fuehrt eine HTTP-Anfrage an eine beliebige API aus.
 *
 * @param {string} endpoint - Die URL der API.
 * @param {string} [method='GET'] - Die HTTP-Methode (z.B. 'GET', 'POST').
 * @param {Object|null} [body=null] - Der Request-Body, wird als JSON gesendet.
 * @param {string|null} [token=null] - Bearer-Token zur Authentifizierung (optional).
 * @returns {Promise<Object|ApiError>} API-Antwort im JSON-Format oder ein Objekt mit Fehlertext
 */
export async function apiRequest(endpoint, method='GET', body=null, token=null){
	/**
	 * Der header enthaelt JSON-Daten.
	 */
	const headers = {
		"Content-Type": "application/json"
	};
	/**
	 * Wenn Token gesetzt, wird der Header um diesen erweitert.
	 */
	if(token){
		headers["Authorization"] = `Bearer ${token}`;
	}
	
	/**
	 * Vorbereitungsarbeiten fuer API-Abfrage per fetch()
	 */
	const options = {
		method,
		headers
	};
	
	/**
	 * Einhaengen des Bodys, wenn vorhanden
	 */
	if(body){
		options.body = JSON.stringify(body);
	}
	
	/**
	 * Anfrage an Server senden und auf Antwort warten
	 */
	const response = await fetch(endpoint, options);
	
	/**
	 * Pruefen des Typs der API-Antwort
	 */
	const contentType = response.headers.get('Content-Type') || "";
	
	/**
	 * Erfolgsfall, wenn Antworttyp JSON
	 */
	if(contentType.includes("application/json")){
		const data = await response.json();
		return data;
	
	/**
	 * 
	 * @returns {Promise<Object|}
	 */
	}else{
		const text = await response.text();
		return{
			error: true,
			responseText: text
		};
	}
}