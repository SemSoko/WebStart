/**
 * Enthaelt alle DOM-Elemente fuer das Dashboard.
 * @type {Object.<string, HTMLElement>}
 */ 
export const dashboardSelectors = {
	/**
	 * Eine Wilkommensnachricht als Ueberschrift.
	 * @type {HTMLHeadingElement}
	 */
	welcomeMessageH1: document.getElementById("userWelcome"),
	/**
	 * Eintraege der Todoliste, wenn vorhanden.
	 * @type {HTMLUListElement}
	 */
	todoRecordUl: document.getElementById("todoRecord"),
	/**
	 * Formular zum Hinzufuegen von Todos.
	 * @type {HTMLFormElement}
	 */
	addTodoForm: document.getElementById("addTodoForm"),
	/**
	 * Infobox, Statusausgabe nach Todohinzufuegen.
	 * @type {HTMLParagraphElement}
	 */
	messageAddTodo: document.getElementById("messageAddTodo"),
	/**
	 * Ueberschrift der Todos.
	 * @type {HTMLHeadingElement}
	 */
	ulHeaderH2: document.getElementById("ulHeader"),
	/**
	 * Eingabefeld fuer den Titel neuer Todos.
	 * @type {HTMLInputElement}
	 */
	newTodoTitle: document.getElementById("title")
};