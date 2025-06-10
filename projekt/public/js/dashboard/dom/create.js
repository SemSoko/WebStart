import {createTextElement} from "./../../shared/dom/elements.js"

/**
 * @typedef {Object} TodoItem
 * @property {number} todo_id - Die eindeutige ID des Todos.
 * @property {string} todo_title - Der Titel des Todos.
 * @property {number} todo_status - 1 fuer erledigt, 0 fuer offen.
 * @property {string} todo_iat - Zeitstempel der Erstellung (ISO-Format).
 */

/**
 * Erzeugt Todo-Nachricht fuer leere Todo-Liste
 *
 * @returns {HTMLLiElement} Li-Element mit eingegebenem Textinhalt
 */
export function createEmptyTodoMessage(){
	const li = createTextElement("li", "Nothing to do - Relax");
	li.dataset.empty = "true";
	
	return li;
}

/**
 * Erzeugt einen vollstaendigen Eintrag fuer die Todo-Liste
 * 
 * @param {TodoItem} todo - Ein einzelnes Todo-Objekt aus der API.
 * @param {{
 *    onToggle: function(number): void,
 *    onDelete: function(number): void
 *  }} callbacks
 * @returns {HTMLLiElement} Der vollstaendige Eintrag fuer die Todo-Liste
 */
export function createTodoListItem(todo, {onToggle, onDelete}){
	// Container <li> und inneres <div>
	/**
	 * Erstellen des Listeneintrags, welcher angehaengt wird
	 * Erstellen eines Containers, in welchen die Todo-Elemente eingefuegt werden
	 */
	const li = document.createElement("li");
	const textDiv = document.createElement("div");
	
	/**
	 * Jedem Todo wird eine ID hinzugefuegt
	 */
	li.dataset.id = todo?.todo_id;
	
	/**
	 * Erstellen des Titels fuer ein Todo
	 */
	const todoTitle = createTextElement("h3", (todo?.todo_title ?? "No title"));
	
	/**
	 * Statusanzeige eines Todos
	 */
	const todoCheckbox = document.createElement("input");
	todoCheckbox.type = "checkbox";
	todoCheckbox.dataset.id = todo?.todo_id;
	todoCheckbox.checked = todo?.todo_status == 1;

	/**
	 * Klickbares Label fuer die Statusanzeige eines Todos
	 */
	const checkboxLabel = document.createElement("label");
	checkboxLabel.appendChild(todoCheckbox);
	checkboxLabel.appendChild(
		document.createTextNode(todo?.todo_status == 1 ? " Done" : " In progress")
	);
	
	/**
	 * Erstellungsdatum des Todos
	 */
	const todoIat = createTextElement("h4", (todo?.todo_iat ?? "No date"));
	
	/**
	 * Button zum Loeschen eines Todos
	 */
	const deleteButton = document.createElement("button");
	deleteButton.textContent = "Delete";
	deleteButton.dataset.id = todo?.todo_id;
	
	/**
	 * Alle Komponenten eines Todos im Container buendeln
	 */
	textDiv.appendChild(todoTitle);
	textDiv.appendChild(checkboxLabel);
	textDiv.appendChild(todoIat);
	textDiv.appendChild(deleteButton);
	
	/**
	 * Container mit gebuendelten Todo-Komponenten in die Todo-Liste einhaengen
	 */
	li.appendChild(textDiv);
	
	/**
	 * Eventhandler fuer Todo-Statusanzeige
	 */
	if(todo?.todo_id){
		/**
		 * Todo-Statuswechsel-Callback - Done oder In progress
		 */
		todoCheckbox.addEventListener("change", () => onToggle(todo.todo_id));
		/**
		 * Todos-Loeschen-Callback
		 */
		deleteButton.addEventListener("click", () => onDelete(todo.todo_id));
	}
	
	return li;
}