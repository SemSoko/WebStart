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

document.addEventListener("DOMContentLoaded", () => {
	/*
		Abrufen der User-ID, um die Ueberschrift zu erstellen => welcomeMessageH1
		Diese Funktion in ein Modul auslagern, weil man so etwas noch an
		anderer Stelle gebrauchen koennte.
		
		Und in Tablle users noch einen Namen einfuegen und die Regestrierung anpassen
		Name soll eingegeben werden.
	*/
	//	async-Funktion innerhalb von anderer Funktion
	async function loadUserData(){
		try{
			const response = await getUserInfo();
			
			if("error" in response){
				console.error("Fehler: ", response?.responseText);
				return;
			}else{
				console.log("Userinfo: ", response);
				dashboardSelectors.welcomeMessageH1.textContent = `Welcome, ${response.first_name} ${response.surname}`;
			}
		}catch(err){
			console.error("Error: loadUserData() - ", err.message);
			dashboardSelectors.welcomeMessageH1.textContent = "Error: loadUserData()";
		}
	}
	
	async function loadUserTodos(){
		try{
			const response = await getTodos();
			
			if("error" in response){
				console.error("Error: loadUserTodos() - ", response?.responseText);
				return;
			}else{
				console.log('Todos: ', response);
				renderTodoList(response, {
					onToggle: toggleUserTodoStatus,
					onDelete: deleteUserTodo
				});
			}
		}catch(err){
			console.error("Error: loadUserTodos() - ", err.message);
		}
	}
	
	async function toggleUserTodoStatus(id){
		try{
			const response = await toggleTodoStatus(id);
			
			if("error" in response){
				console.error("Error: toggleUserTodoStatus() - ", response?.responseText);
				return;
			}else{
				// Weil updateTodoItem() intern createTodoListItem() aufruft,
				// und diese Funktion sowohl onToggle als auch onDelete braucht,
				// um beide Eventhandler korrekt zu setzen, uebergibt man beides
				// ...
				// todoCheckbox.addEventListener("change", () => onToggle(todo.todo_id));
				// deleteButton.addEventListener("click", () => onDelete(todo.todo_id));
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
	
	
	//	addUserTodo - Anfang
	async function addUserTodo(title){
		try{
			const response = await addTodo(title);
			
			if("error" in response){
				console.error("Error: addUserTodo() - ", response?.responseText);
				return;
			}else{
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
	
	//	Was wenn ich auf einer Seite mehr als ein submit-Event habe,
	//	wie muessen die Eventlistener angepasst werden?
	dashboardSelectors.addTodoForm.addEventListener("submit", async (e) => {
		e.preventDefault();
		
		const title = dashboardSelectors.newTodoTitle.value.trim();
		
		if(!title){
			dashboardSelectors.messageAddTodo.textContent = "Bitte einen Titel eingeben.";
			return;
		}
		
		const result = await addUserTodo(title);
		
		if(result?.success){
			dashboardSelectors.messageAddTodo.textContent = "Successfully added new todo";
			//	Eingabe zuruecksetzen
			dashboardSelectors.newTodoTitle.value = "";
		}else{
			dashboardSelectors.messageAddTodo.textContent = result?.message || "Error: addUserTodo-Eventlistener";
		}
	});
	
	//	addUserTodo - Ende
	
	/*
		WICHTIG
		WENN NUR EIN EINZELNES TODO IN DER LISTE IST, KOMMT ES ZU EINEM FEHLER BEIM LOESCHEN
		BZW WIRD DIE ANZEIGE NICHT AKTUALISIERT
	*/
	
	async function deleteUserTodo(id){
		try{
			const response = await deleteTodo(id);
			
			if("error" in response){
				console.error("Error: deleteUserTodo() - ", response?.responseText);
				return;
			}else{
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