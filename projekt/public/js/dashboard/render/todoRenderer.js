import {createTodoListItem, createTodoListItemModern, createEmptyTodoMessage} from "./../dom/create.js"
import {dashboardSelectors} from "./../dom/selectors.js"

/**
 * @typedef {Object} TodoItem
 * @property {number} todo_id - Die eindeutige ID des Todos.
 * @property {string} todo_title - Der Titel des Todos.
 * @property {number} todo_status - 1 fuer erledigt, 0 fuer offen.
 * @property {string} todo_iat - Zeitstempel der Erstellung (ISO-Format).
 */
 
/**
 * @typedef {Object} Handlers
 *
 * @property {function} onToggle - Umschalten des Todo-Status.
 * @property {number} todo_id - ID des Todos, zur Differenzierung.
 * @param {number} todo_status - Der aktuelle Todo-Status.
 *
 * @property {function} onDelete - Loeschen eines Todos.
 * @param {number} todo_id - Die ID des Todos, das geloescht werden soll.
 */

/**
 * Rendert die gesamte Todo-Liste (State-Reset)
 * 
 * @param {TodoItem[]} todos - Array von Todo-Objekten aus der API.
 * @param {Handlers} handlers - Objekt mit Callback-Funktionen zum Umschalten und Loeschen von Todos.
 */
export function renderTodoList(todos, handlers){
	/**
	 * Bedient sich des sogenannten "State Reset"-Ansatzes.
	 * Wenn API-Endpuntk erfolgreich abgerufen wird, die Todo-Listen-Anzeige vollständig refresht.
	 */

	/**
	 * Das UL-Element wird vollständig geleert, bevor neue Einträge eingefügt werden.
	 */
	dashboardSelectors.todoRecordUl.innerHTML = '';
	
	/**
	 * Der Benutzer hat Todos
	 */
	if(todos.length > 0){
		dashboardSelectors.ulHeaderH2.textContent = "Your ToDos";

		/**
		 * Erstelle die Todo-Eintraege und haenge die Todos in das DOM.
		 */
		todos.forEach(todo => {
			const li = createTodoListItem(todo, handlers);
			dashboardSelectors.todoRecordUl.appendChild(li);
		});
	}
	
	/**
	 * Der Benutzer hat keine Todos.
	 */
	if(todos.length === 0){
		renderEmptyMessage();
	}
}

/**
 * @deprecated Diese Funktion nutzt die alte Todo-Struktur.
 * Verwende `appendTodoItemModern()` fuer neue API-Antworten.
 *
 * Haengt ein einzelnes Todo-Element an die Todo-Liste an.
 *
 * @param {TodoItem} todo - Ein Todo-Objekt aus der API.
 * @param {Handlers} handlers - Objekt mit Callback-Funktionen.
 */
export function appendTodoItem(todo, handlers){
	/**
	 * Loeschen des Default-Todo-Titels.
	 */
	if(dashboardSelectors.todoRecordUl.firstChild?.dataset?.empty === "true"){
		dashboardSelectors.todoRecordUl.innerHTML = "";
	}
	
	/**
	 * Ueberschrift der Todo-Liste aktualisieren.
	 */
	dashboardSelectors.ulHeaderH2.textContent = "Your ToDos";
	
	/**
	 * Todo erstellen und unten in der Liste einhaengen.
	 */
	const li = createTodoListItem(todo, handlers);
	dashboardSelectors.todoRecordUl.appendChild(li);
}

/**
 * Haengt ein einzelnes Todo-Element im neuen Format an die Todo-Liste an.
 *
 * Diese Funktion verwendet die modernisierte Todo-Struktur:
 * {id, title, isDone, createdAt}
 * und ruft intern `createTodoListItemModern()` auf.
 *
 * @param {TodoItem} todo - Ein Todo-Objekt aus der API.
 * @param {Handlers} handlers - Objekt mit Callback-Funktionen.
 */
export function appendTodoItemModern(todo, handlers){
	/**
	 * Loeschen des Default-Todo-Titels.
	 */
	if(dashboardSelectors.todoRecordUl.firstChild?.dataset?.empty === "true"){
		dashboardSelectors.todoRecordUl.innerHTML = "";
	}
	
	/**
	 * Ueberschrift der Todo-Liste aktualisieren.
	 */
	dashboardSelectors.ulHeaderH2.textContent = "Your ToDos";
	
	/**
	 * Todo erstellen und unten in der Liste einhaengen.
	 */
	const li = createTodoListItemModern(todo, handlers);
	dashboardSelectors.todoRecordUl.appendChild(li);
}

/**
 * Entfernt ein Todo-Element aus der Liste anhand seiner ID.
 *
 * @param {number} todoId - ID des Todos.
 */
export function removeTodoItem(todoId){
	/**
	 * Todo anhand der ID ermitteln.
	 */
	const liToRemove = dashboardSelectors.todoRecordUl.querySelector(`li[data-id="${todoId}"]`);
	
	/*
	 * Loeschen des ermittelten Todos
	 */
	if(liToRemove){
		liToRemove.remove();
	}
	
	/*
	 * Wenn Liste nach dem Loeschen leer ist, zeige default Nachricht an.
	 */
	if(!dashboardSelectors.todoRecordUl.querySelector("li")){
		renderEmptyMessage();
	}
}

/**
 * Aktualisiert ein bestehendes Todo im DOM
 *
 * @param {TodoItem} todo - Ein Todo-Objekt aus der API.
 * @param {Handlers} handlers - Objekt mit Callback-Funktionen.
 * @returns {void} bricht ab, wenn kein passendes Todo im DOM gefunden wird.
 */
export function updateTodoItem(updatedTodo, handlers){
	/**
	 * Todo anhand der ID ermitteln.
	 */
	const existingLi = dashboardSelectors.todoRecordUl.querySelector(`li[data-id="${updatedTodo.todo_id}"]`);
	
	/*
	 * Todo konnte nicht ermittelt werden.
	 */
	if(!existingLi){
		return;
	}
	
	/**
	 * Todo-Eintrag mit neuen Daten erzeugen.
	 */
	const updatedLi = createTodoListItem(updatedTodo, handlers);
	
	/**
	 * Alten Todo-Eintrag durch neuen ersetzen.
	 */
	dashboardSelectors.todoRecordUl.replaceChild(updatedLi, existingLi);
}

/**
 * Erzeugen einer Default-Nachricht fuer leere Todo-Liste.
 *
 * @returns {void}
 */
export function renderEmptyMessage(){
	/**
	 * Todo-Liste bereinigen.
	 */
	dashboardSelectors.todoRecordUl.innerHTML = "";
	
	/**
	 * Ueberschrift aktualisieren.
	 */
	dashboardSelectors.ulHeaderH2.textContent = "No todos available";
	
	/**
	 * Default-Nachricht in Todo-Liste einfuegen.
	 */
	const emptyMessage = createEmptyTodoMessage();
	dashboardSelectors.todoRecordUl.appendChild(emptyMessage);
}