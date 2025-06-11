// shared/api/...

// Funktionale-Imports
import {getTodos, addTodo, toggleTodoStatus, deleteTodo} from "./../shared/api/todo.js"
import {getUserInfo} from "./../shared/api/user.js"

// dashboard/dom/...

// Import von Selektoren
// Import Todolistengenerierung
import {dashboardSelectors} from "./dom/selectors.js"
import {createTodoListItem, createEmptyTodoMessage} from "./dom/create.js"

// Import von Renderern

// Imports zur Listen-Aktualisierung
import {renderTodoList, appendTodoItem} from "./render/todoRenderer.js"
import {removeTodoItem, updateTodoItem} from "./render/todoRenderer.js"

// Import von Events

import {registerDashboardEvents} from "./events/dashboardEvents.js"


/**
 * Initialisiert das Dashboard nach dem Laden des DOMs.
 *
 * Haupt-Einstiegspunkt der Anwendung.
 * Laedt Benutzerinformationen und Todos,
 * registriert Eventhandler und rendert die Oberflaeche.
 *
 * @remarks
 * Diese Logik soll in eine zentrale 'initDashboard()'-Funktion ausgelagert werden,
 * um Wiederverwendbarkeit, Testbarkeit und bessere Trennung der Verantwortlichkeiten
 * zu ermoeglichen.
 *
 * @todo Auslagerung in init-Modul und Refaktorisierung der index.js.
 */
document.addEventListener("DOMContentLoaded", () => {
	/**
	 * Ruft Benutzerinformationen ab und erzeugt eine Willkommensnachricht im Dashboard.
	 *
	 * @returns {void}
	 *
	 * @todo
	 * Auslagern in ein separates Modul zur Wiederverwendung und besseren Strukturierung.
	 */
	async function loadUserData(){
		try{
			/**
			 * Anfrage an die API zum Abrufen der Benutzerinformationen.
			 */
			const response = await getUserInfo();
			
			if("error" in response){
				console.error("Fehler: ", response?.responseText);
				return;
			}else{
				console.log("Userinfo: ", response);
				/**
				 * Erzeugen der Willkommensnachricht mit Vor- und Nachname.
				 */
				dashboardSelectors.welcomeMessageH1.textContent = `Welcome, ${response.first_name} ${response.surname}`;
			}
		}catch(err){
			console.error("Error: loadUserData() - ", err.message);
			dashboardSelectors.welcomeMessageH1.textContent = "Error: loadUserData()";
		}
	}
	
	/**
	 * Ruft die Todos des aktuellen Benutzers ab und rendert sie im Dashboard.
	 *
	 * @returns {void}
	 *
	 * @todo
	 * Auslagern in ein separates Modul zur Wiederverwendung und besseren Strukturierung.
	 */
	async function loadUserTodos(){
		try{
			/**
			 * Anfrage an die API zum Abrufen der Todos.
			 */
			const response = await getTodos();
			
			if("error" in response){
				console.error("Error: loadUserTodos() - ", response?.responseText);
				return;
			}else{
				console.log('Todos: ', response);
				/**
				 * Darstellung der Todos im UI mithilfe des zentralen Renderers.
				 */
				renderTodoList(response, {
					onToggle: toggleUserTodoStatus,
					onDelete: deleteUserTodo
				});
			}
		}catch(err){
			console.error("Error: loadUserTodos() - ", err.message);
		}
	}
	
	/**
	 * Aktualisiert den Status eines Todos und rendert die Aenderung im DOM.
	 *
	 * @param {number} id - Die ID des zu aktualisierenden Todos.
	 * @returns {void}
	 *
	 * @remarks
	 * Da 'updateTodoItem()' intern 'createTodoListItem()' verwendet,
	 * muessen sowohl 'onToggle' als auch 'onDelete' uebergeben werden,
	 * um die Eventhandler korrekt zu setzen.
	 *
	 * @todo
	 * Auslagern in ein separates Modul zur Wiederverwendung und besseren Strukturierung.
	 */
	async function toggleUserTodoStatus(id){
		try{
			/**
			 * API-Aufruf zur Statusaenderung.
			 */
			const response = await toggleTodoStatus(id);
			
			if("error" in response){
				console.error("Error: toggleUserTodoStatus() - ", response?.responseText);
				return;
			}else{
				/**
				 * Status aktualisieren und neuen Status im DOM abbilden.
				 */
				updateTodoItem(response.completeTodo, {
					onToggle: toggleUserTodoStatus,
					onDelete: deleteUserTodo
				});
				console.log('der neue Status des Todos:', response);
			}
		}catch(err){
			console.error("Error: toggleUserTodoStatus() - ", err.message);
		}
	}
	
	/**
	 * Fuegt ein neues Todo hinzu und rendert es im Dashboard.
	 * Wird als Callback an 'registerDashboardEvents' uebergeben.
	 *
	 * @param {string} title - Titel des neuen Todos.
	 * @returns {Promise<{success: boolean, message?: string} | void>}
	 *
	 * @todo
	 * Auslagern in ein separates Modul zur Wiederverwendung und besseren Strukturierung.
	 */
	async function addUserTodo(title){
		try{
			/**
			 * API-Aufruf um neues Todos hinzuzufuegen.
			 */
			const response = await addTodo(title);
			
			if("error" in response){
				console.error("Error: addUserTodo() - ", response?.responseText);
				return;
			}else{
				/**
				 * Neues Todo in die Liste einhaengen und im DOM rendern.
				 */
				appendTodoItem(response.completeTodo, {
					onToggle: toggleUserTodoStatus,
					onDelete: deleteUserTodo
				});
				console.log("Updated todolist: ", response);
				return response;
			}
		}catch(err){
			console.error("Error: addUserTodo() - ", err.message);
		}
	}
	
	registerDashboardEvents({
		onAddTodo: addUserTodo,
		getInput: () => dashboardSelectors.newTodoTitle,
		showMessage: (msg) => dashboardSelectors.messageAddTodo.textContent = msg
	});
	
	/**
	 * Loescht ein Todo
	 *
	 * @param {number} id - Die ID des zu loeschenden Todos.
	 * @returns {void}
	 *
	 * @todo
	 * Auslagern in ein separates Modul zur Wiederverwendung und besseren Strukturierung.
	 */
	async function deleteUserTodo(id){
		try{
			/**
			 * API-Aufruf um ein bestimmtes Todo zu loeschen.
			 */
			const response = await deleteTodo(id);
			
			if("error" in response){
				console.error("Error: deleteUserTodo() - ", response?.responseText);
				return;
			}else{
				/**
				 * Todo loeschen.
				 */
				removeTodoItem(id);
				console.log("Die neue Todo-Liste:", response);
			}
		}catch(err){
			console.error("Error: deleteUserTodo() - ", err.message);
		}
	}
	
	loadUserData();
	loadUserTodos();
});