export function registerDashboardEvents({
	onAddTodo,
	getInput,
	showMessage
}){
	const form = document.querySelector("#addTodoForm");
	const input = getInput();
	
	form.addEventListener("submit", async (e) => {
		e.preventDefault();
		
		const title = input.value.trim();
		
		if(!title){
			showMessage("Bitte einen Titel eingeben.");
			return;
		}
		
		const result = await onAddTodo(title);
		
		if(result?.success){
			showMessage("Successfully added new todo");
			input.value = "";
		}else{
			showMessage(result?.message || "Error: Todo not added");
		}
	});
}