// Importiert den zentralen fetch()-Wrapper für standardisierte HTTP-Anfragen
import {apiRequest} from "./fetchWrapper.js"
// Importiert Hilfsfunktion zur Token-Ermittlung aus dem localStorage
import {getToken} from "../utils/token.js"

// Holt Benutzerinformationen des aktuell eingeloggten Users vom Server
// Erweitern um folgendes:
// prüfen, ob response.first_name oder response.surname fehlen
export async function getUserInfo(){
	const endpoint = "/api/user_info.php";
	const method = "GET";
	const body = null;
	const token = getToken();
	
	return await apiRequest(endpoint, method, body, token);
}