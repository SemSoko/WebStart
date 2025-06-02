import {createTodoListItem, createEmptyTodoMessage} from "./../dom/create.js"
import {dashboardSelectors} from "./../dom/selectors.js"

// vorher loadUserTodos
/**
 * Rendert die gesamte Todo-Liste neu (State-Reset)
 */
export function renderTodoList(todos, handlers){
	//	Das ist der sogenannte “State Reset”-Ansatz, und er wird auch in vielen
	//	React/SPA-Frameworks standardmäßig genutzt.
	//	Nach erfolgreichem Abruf des API-Endpunkts wird die Anzeige der Todos refresht
	//	In Kombination mit toggleUserTodoStatus() (Statusaenderung) und addUserTodo() (neues Todo)
	//	Das <ul>-Element wird vollständig geleert, bevor neue Einträge eingefügt werden.
	dashboardSelectors.todoRecordUl.innerHTML = '';
	
	if(todos.length > 0){
		//	Der <h2>-Titel wird je nach Zustand („Your ToDos“ oder „No todos available“) gesetzt.
		dashboardSelectors.ulHeaderH2.textContent = "Your ToDos";
					
		//	Jeder Eintrag wird mit createTodoListItem() erzeugt und ins DOM gehängt.
		todos.forEach(todo => {
			//	Erstellt li-Tag je Todoeintrag
			const li = createTodoListItem(todo, handlers);
				
			//	Die per forEach erstellen Todos in todoRecordUl einhaengen
			dashboardSelectors.todoRecordUl.appendChild(li);
		});
	}
	
	//	Wenn keine Todos vorhanden sind, wird eine entsprechende leere Nachricht angezeigt.
	if(todos.length === 0){
		renderEmptyMessage();
	}
}

// vorher loadUserTodos()
/**
 * Haengt ein neues Todo-Element unten an die Liste an
 */
export function appendTodoItem(todo, handlers){
	//	Wenn vorher "No todos available" stand -> leeren
	if(dashboardSelectors.todoRecordUl.firstChild?.dataset?.empty === "true"){
		dashboardSelectors.todoRecordUl.innerHTML = "";
	}
	
	//	Ueberschrift ggf. aktualisieren
	dashboardSelectors.ulHeaderH2.textContent = "Your ToDos";
	
	//	Neuen Eintrag erzeugen und anhaengen
	const li = createTodoListItem(todo, handlers);
	dashboardSelectors.todoRecordUl.appendChild(li);
}

// vorher loadUserTodos
/**
 * Entfernt ein Todo-Element anhand der ID
 *
 *
 */
export function removeTodoItem(todoId){
	// Finde das passende li-Element anhand der ID
	const liToRemove = dashboardSelectors.todoRecordUl.querySelector(`li[data-id="${todoId}"]`);
	
	if(liToRemove){
		liToRemove.remove();
	}
	
	if(!dashboardSelectors.todoRecordUl.querySelector("li")){
		renderEmptyMessage();
	}
}

// vorher loadUserTodos
/**
 * Aktualisiert ein bestehendes Todo im DOM
 *
 */
export function updateTodoItem(updatedTodo, handlers){
	// li mit passender ID finden
	const existingLi = dashboardSelectors.todoRecordUl.querySelector(`li[data-id="${updatedTodo.todo_id}"]`);
	
	if(!existingLi){
		return;
	}
	
	// Neues li mit neuem Inhalt erzeugen
	const updatedLi = createTodoListItem(updatedTodo, handlers);
	
	// Altes Element ersetzen
	dashboardSelectors.todoRecordUl.replaceChild(updatedLi, existingLi);
}

/*if(response.length === 0){
					dashboardSelectors.ulHeaderH2.textContent = "No todos available";
					dashboardSelectors.todoRecordUl.appendChild(createEmptyTodoMessage());
				}
*/
// vorher DOM direkt manipuliert
/**
 * Zeigt eine leere Nachricht, wenn Todos nicht vorhanden
 *
 *
 */
export function renderEmptyMessage(){
	// Container leeren
	dashboardSelectors.todoRecordUl.innerHTML = "";
	
	// Ueberschrift anpassen
	dashboardSelectors.ulHeaderH2.textContent = "No todos available";
	
	// Nachricht einfuegen
	const emptyMessage = createEmptyTodoMessage();
	dashboardSelectors.todoRecordUl.appendChild(emptyMessage);
}