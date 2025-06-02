// Importiert den zentralen fetch()-Wrapper für standardisierte HTTP-Anfragen
import {apiRequest} from "./fetchWrapper.js"
// Importiert Hilfsfunktion zur Token-Ermittlung aus dem localStorage
import {getToken} from "../utils/token.js"

// Ruft alle Todos des aktuellen Users vom Server ab
// Erweitern um folgendes:
// prüfen, ob response.xxx, also ob eine gueltige Antwort von der API
// geliefert wird
export async function getTodos(){
	const endpoint = "/api/get_user_todos.php";
	const method = "GET";
	const body = null;
	const token = getToken();
	
	return await apiRequest(endpoint, method, body, token);
}

// Erstellt ein neues Todo mit dem uebergebenen Titel
// Erweitern um folgendes:
// prüfen, ob response.xxx, also ob eine gueltige Antwort von der API
// geliefert wird
export async function addTodo(title){
	const endpoint = "/api/addUserTodo.php";
	const method = "POST";
	const body = {title};
	const token = getToken();
	
	return await apiRequest(endpoint, method, body, token);
}

// Ändert den Status eines bestimmten Todos
// Erweitern um folgendes:
// prüfen, ob response.xxx, also ob eine gueltige Antwort von der API
// geliefert wird
export async function toggleTodoStatus(id){
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
export async function deleteTodo(id){
	const endpoint = "/api/deleteUserTodo.php";
	const method = "DELETE";
	const body = {id};
	const token = getToken();
	
	return await apiRequest(endpoint, method, body, token);
}