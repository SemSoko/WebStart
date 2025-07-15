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
 * API-Abfrage der Benutzer-Todos
 *
 * @returns {Promise<Object|ApiError>} API-Antwort im JSON-Format oder ein Objekt mit Fehlertext
 */
export async function getTodos(){
	/**
	 * Ausstehende Erweiterung:
	 * prüfen, ob response.xxx, also ob eine gueltige Antwort von der API geliefert wird
	 */
	const endpoint = "/api/get_user_todos.php";
	const method = "GET";
	const body = null;
	const token = getToken();
	
	return await apiRequest(endpoint, method, body, token);
}

/**
 * @deprecated Verwendet die alte Backend-Antwortstruktur.
 * Verwende `addTodoModern()` für die neue Struktur.
 *
 * Einem Benutzer Todo hinzufuegen per API
 *
 * @returns {Promise<Object|ApiError>} API-Antwort im JSON-Format oder ein Objekt mit Fehlertext
 */
export async function addTodo(title){
	/**
	 * Ausstehende Erweiterung:
	 * prüfen, ob response.xxx, also ob eine gueltige Antwort von der API geliefert wird
	 */
	const endpoint = "/api/todo-new/";
	const method = "POST";
	const body = {title};
	const token = getToken();
	
	return await apiRequest(endpoint, method, body, token);
}

/**
 * Neues Todo per modularem Backend hinzufuegen (moderne Struktur)
 *
 * Wandelt Backend-Antwort in frontend-kompatibles Objekt um.
 */
export async function addTodoModern(title){
	const endpoint = "/api/todo-new/";
	const method = "POST";
	const body = {title};
	const token = getToken();
	
	const response = await apiRequest(endpoint, method, body, token);

	if(!response || response.success !== true || !response.data){
		return {
			error: true,
			message: response?.message || "Fehler beim Hinzufuegen",
			debug: response?.debug || null
		};
	}
	
	const {todo_id, todo_title, todo_status, todo_iat} = response.data;

	// Struktur anpassen fuer appendTodoItem(...)
	return{
		completeTodo: {
			id: todo_id,
			title: todo_title,
			isDone: todo_status,
			createdAt: todo_iat
		}
	};
}

/**
 * Todo-Status eines Nutzers aktualisieren
 *
 * @returns {Promise<Object|ApiError>} API-Antwort im JSON-Format oder ein Objekt mit Fehlertext
 */
export async function toggleTodoStatus(id){
	/**
	 * Ausstehende Erweiterung:
	 * prüfen, ob response.xxx, also ob eine gueltige Antwort von der API geliefert wird
	 */
	const endpoint = "/api/toggleUserTodoStatus.php";
	const method = "PATCH";
	const body = {id};
	const token = getToken();
	
	return await apiRequest(endpoint, method, body, token);
}

// Löscht ein Todo
// Erweitern um folgendes:
// prüfen, ob response.xxx, also ob eine gueltige Antwort von der API
// geliefert wird

/**
 * Todo eines Nutzers loeschen
 *
 * @returns {Promise<Object|ApiError>} API-Antwort im JSON-Format oder ein Objekt mit Fehlertext
 */
export async function deleteTodo(id){
	/**
	 * Ausstehende Erweiterung:
	 * prüfen, ob response.xxx, also ob eine gueltige Antwort von der API geliefert wird
	 */
	const endpoint = "/api/deleteUserTodo.php";
	const method = "DELETE";
	const body = {id};
	const token = getToken();
	
	return await apiRequest(endpoint, method, body, token);
}