// Funktionale-Imports
import {getTodos, addTodo, toggleTodoStatus, deleteTodo} from "./api/todo.js"
import {getUserInfo} from "./api/user.js"

// Import von Selektoren
import {dashboardSelectors} from "./dom/dashboard/selectors.js"
// Import Todolistengenerierung
import {createTodoListItem, createEmptyTodoMessage} from "./dom/dashboard/create.js"

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
				
				//	Das ist der sogenannte “State Reset”-Ansatz, und er wird auch in vielen
				//	React/SPA-Frameworks standardmäßig genutzt.
				//	Nach erfolgreichem Abruf des API-Endpunkts wird die Anzeige der Todos refresht
				//	In Kombination mit toggleUserTodoStatus() (Statusaenderung) und addUserTodo() (neues Todo)
				dashboardSelectors.todoRecordUl.innerHTML = '';
				
				if(response.length > 0){
					dashboardSelectors.ulHeaderH2.textContent = "Your ToDos";
					
					response.forEach(todo => {
						//	Erstellt li-Tag je Todoeintrag
						const li = createTodoListItem(todo, {
							toggleUserTodoStatus,
							deleteUserTodo
						});
						
						//	Die per forEach erstellen Todos in todoRecordUl einhaengen
						dashboardSelectors.todoRecordUl.appendChild(li);
					});
				}
				
				if(response.length === 0){
					dashboardSelectors.ulHeaderH2.textContent = "No todos available";
					dashboardSelectors.todoRecordUl.appendChild(createEmptyTodoMessage());
				}
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
				//	Das ist der sogenannte “State Reset”-Ansatz, und er wird auch in vielen
				//	React/SPA-Frameworks standardmäßig genutzt.
				//	Nach erfolgreichem Abruf des API-Endpunkts wird die Anzeige der Todos refresht
				//	In Kombination mit loadUserTodos()
				loadUserTodos();
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
				//	Das ist der sogenannte “State Reset”-Ansatz und er wird auch in vielen
				//	React/SPA-Frameworks standardmäßig genutzt.
				//	Nach erfolgreichem Abruf des API-Endpunkts wird die Anzeige der Todos refresht
				//	In Kombination mit loadUserTodos()
				loadUserTodos();
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
				//	Das ist der sogenannte “State Reset”-Ansatz und er wird auch in vielen
				//	React/SPA-Frameworks standardmäßig genutzt.
				//	Nach erfolgreichem Abruf des API-Endpunkts wird die Anzeige der Todos refresht
				//	In Kombination mit loadUserTodos()
				loadUserTodos();
				console.log("Die neue Todo-Liste:", response);
			}
		}catch(err){
			console.error("Error: deleteUserTodo() - ", err.message);
		}
	}
	
	loadUserData();
	loadUserTodos();
});