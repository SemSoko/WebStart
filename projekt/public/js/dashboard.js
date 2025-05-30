// Funktionale-Imports
import {getTodos, addTodo, toggleTodoStatus, deleteTodo} from "./api/todo.js"
import {getUserInfo} from "./api/user.js"

// Import von Selektoren
import {dashboardSelectors} from "./dom/selectors.js"

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
						//	Erstellt einen li-Tag pro Eintrag
						const li = document.createElement("li");
						
						//	Erstellt einen Text-Container fuer Infos zum Todo
						const textDiv = document.createElement("div");
						
						//	Title - Todo
						const todoTitle = document.createElement("h3");
						todoTitle.textContent = todo.todo_title;
						
						//	Checkbox - Todo - Input, das in Label gepackt wird
						const todoCheckbox = document.createElement("input");
						todoCheckbox.type = "checkbox";
						todoCheckbox.dataset.id = todo.todo_id;
						todoCheckbox.checked = todo.todo_status == 1;
						
						//	Status-Text fuer Checkbox
						const checkboxStatusText = document.createTextNode(
							todo?.todo_status == 1 ? " Done" : " In Progress"
						);
						
						//	Label fuer den Status-Text
						const labelStatusText = document.createElement("label");
						labelStatusText.appendChild(todoCheckbox);
						labelStatusText.appendChild(checkboxStatusText);
						
						//	Created - Iat
						const todoIat = document.createElement("h4");
						todoIat.textContent = todo.todo_iat;
						
						//	Button zum Loeschen eines Todo-Eintrag
						const deleteButton = document.createElement("button");
						deleteButton.textContent = "Loeschen";
						deleteButton.dataset.id = todo.todo_id;
						
						//	Daten in Div einhaengen
						textDiv.appendChild(todoTitle);
						textDiv.appendChild(labelStatusText);
						textDiv.appendChild(todoIat);
						textDiv.appendChild(deleteButton);
						
						//	textDiv jeweils in ein li einhaengen
						li.appendChild(textDiv);
						
						//	lis in ul einhaengen
						dashboardSelectors.todoRecordUl.appendChild(li);
						
						todoCheckbox.addEventListener("change", (e) => {
							const id = e.target.dataset.id;
							const status = e.target.checked;
							toggleUserTodoStatus(id);
						});
						
						deleteButton.addEventListener("click", () => {
							deleteUserTodo(todo.todo_id);
						});
					});	
				}
				
				if(response.length === 0){
					dashboardSelectors.ulHeaderH2.textContent = "No todos available";
					dashboardSelectors.todoRecordUl.innerHTML = "<li>Nothing to do - Relax</li>"
					
					return;
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