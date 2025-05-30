export const dashboardSelectors = {
	// Wilkommensnachricht
	welcomeMessageH1: document.getElementById("userWelcome"),
	// Todoeintraege der Todoliste, wenn Eintraege vorhanden
	todoRecordUl: document.getElementById("todoRecord"),
	// Formular - Hinzufuegen von Todos
	addTodoForm: document.getElementById("addTodoForm"),
	// Infobox - Statusausgabe nachdem ein Todo hinzugefuegt wurde
	messageAddTodo: document.getElementById("messageAddTodo"),
	// Ueberschrift zu den Todos
	ulHeaderH2: document.getElementById("ulHeader"),
	// Input - Eingabefeld, in welches der Todotitel eingegeben wird
	newTodoTitle: document.getElementById("title")
};