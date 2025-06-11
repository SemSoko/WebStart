// Importiert den zentralen fetch()-Wrapper für standardisierte HTTP-Anfragen
import {apiRequest} from "./fetchWrapper.js"
// Importiert Hilfsfunktion zur Token-Ermittlung aus dem localStorage
import {getToken} from "./../utils/token.js"

/**
 * @typedef {Object} ApiError
 * @property {boolean} error - Zeigt an, dass ein Fehler aufgetreten ist.
 * @property {string} responseText - Die Antwort des Servers als Text.
 */

/**
 * Abrufen der Benutzerinformationen
 *
 * @returns {Promise<Object|ApiError>} API-Antwort im JSON-Format oder ein Objekt mit Fehlertext
 */
export async function getUserInfo(){
	/**
	 * Ausstehende Erweiterung:
	 * prüfen, ob response.first_name oder response.surname fehlen
	 * JSDoc um @typedef-Obejkt: UserInfo erweitern
	 */
	const endpoint = "/api/user_info.php";
	const method = "GET";
	const body = null;
	const token = getToken();
	
	return await apiRequest(endpoint, method, body, token);
}