/**
 * Liefert ein HTMLInputElement zur Eingabe des Todo-Titels.
 *
 * @callback GetInputCallback
 * @returns {HTMLInputElement}
 */

/**
 * Zeigt eine Nachricht im Dashboard an.
 *
 * @callback ShowMessageCallback
 * @param {string} message - Die anzuzeigende Nachricht.
 */

/**
 * Fuegt ein neues Todo hinzu.
 *
 * @callback AddTodoCallback
 * @param {string} title - Der Titel des neuen Todos.
 * @returns {Promise<{success: boolean, message?: string}>}
 */

/**
 * Uebernimmt das Event-Handling beim Hinzufuegen eines neuen Todos.
 *
 * @param {{
 *    onAddTodo: AddTodoCallback,
 *    getInput: GetInputCallback,
 *    showMessage: ShowMessageCallback
 * }} callbacks - Alle noetigen Funktionen zur Formularverarbeitung
 */
export function registerDashboardEvents({
	/**
	 * Callback-Funktionen zur Interaktion mit der Anwendung
	 */
	onAddTodo,
	getInput,
	showMessage
}){
	/**
	 * TODO: querySelector durch dashboardSelectors.addTodoForm ersetzen.
	 * Die Referenz auf das Formular ist bereits zentral definiert und
	 * muss auch hier genutzt werden.
	 * Beispiel:
	 * // Import von Selektoren
     * import {dashboardSelectors} from "./dom/selectors.js"
	 * const form = dashboardSelectors.addTodoForm;
	 */
	 
	/**
	 * Formular aus dashboard.html auswaehlen
	 */
	const form = document.querySelector("#addTodoForm");
	
	/**
	 * Todo-Titel einholen
	 */
	const input = getInput();
	
	/**
	 * Lauscht auf Formular-Aktionen in dashboard.html
	 */
	form.addEventListener("submit", async (e) => {
		e.preventDefault();
		
		/**
		 * Titel des Todos verarbeiten
		 */
		const title = input.value.trim();
		
		if(!title){
			showMessage("Bitte einen Titel eingeben.");
			return;
		}
		
		/**
		 * Neues Todo eintragen
		 */
		const result = await onAddTodo(title);
		
		if(result?.success){
			showMessage("Successfully added new todo");
			input.value = "";
		}else{
			showMessage(result?.message || "Error: Todo not added");
		}
	});
}