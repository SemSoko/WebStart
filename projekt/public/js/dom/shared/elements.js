//	Erzeugt ein HTML-Element mit Textinhalt
export function createTextElement(tagName, text){
	const el = document.createElement(tagName);
	el.textContent = text;
	
	return el;
}