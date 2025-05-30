import {createTextElement} from "../shared/elements.js"

// Erzeugt die leere Todo-Nachricht (z.B. wenn keine Todos vorhanden sind)
export function createEmptyTodoMessage(){
	return createTextElement("li", "Nothing to do - Relax");
}

/*
 Erzeugt ein vollstaendiges Listenelement fuer ein Todo
 {onToggle, onDelete}: Eventhandler, die beim Statuswechsel oder
 Loeschen aufgerufen werden
*/

export function createTodoListItem(todo, {onToggle, onDelete}){
	// Container <li> und inneres <div>
	const li = document.createElement("li");
	const textDiv = document.createElement("div");
	
	// Todo-Titel <h3>
	const todoTitle = createTextElement("h3", (todo?.todo_title ?? "No title"));
	
	// Checkbox zur Statusanzeige/-aenderung
	const todoCheckbox = document.createElement("input");
	todoCheckbox.type = "checkbox";
	todoCheckbox.dataset.id = todo?.todo_id;
	todoCheckbox.checked = todo?.todo_status == 1;
	
	// Label fuer die Checkbox mit Status-Text
	const checkboxLabel = document.createElement("label");
	checkboxLabel.appendChild(todoCheckbox);
	checkboxLabel.appendChild(
		document.createTextNode(todo?.todo_status == 1 ? " Done" : " In progress")
	);
	
	// Zeitstempel <h4>
	const todoIat = createTextElement("h4", (todo?.todo_iat ?? "No date"));
	
	// Delete-Button
	const deleteButton = document.createElement("button");
	deleteButton.textContent = "Delete";
	deleteButton.dataset.id = todo?.todo_id;
	
	// Alles ins Text-Div einhaengen
	textDiv.appendChild(todoTitle);
	textDiv.appendChild(checkboxLabel);
	textDiv.appendChild(todoIat);
	textDiv.appendChild(deleteButton);
	
	// Div in das Listen-Element einhaengen
	li.appendChild(textDiv);
	
	// Eventhandler binden, nur wenn todo_id existiert
	if(todo?.todo_id){
		// Beim Statuswechsel die uebergebene Callback-Funktion onToggle ausfuehren
		todoCheckbox.addEventListener("change", () => onToggle(todo.todo_id));
		// Beim Klick auf "Delete" die uebergebene Callback-Funktion onDelete ausfuehren
		deleteButton.addEventListener("click", () => onDelete(todo.todo_id));
	}
	
	// Rueckgabe des vollstaendigen Listenelements <li>
	return li;
}