/**
 * HTML-Element mit Textinhalt erzeugen
 * @param {string} tagName - Zu erstellendes HTML-Tag.
 * @param {string} text - Inhalt des HTML-Elements.
 * @returns {HTMLElement} Das erzeugte DOM-Element.
 */
export function createTextElement(tagName, text){
	const el = document.createElement(tagName);
	el.textContent = text;
	
	return el;
}