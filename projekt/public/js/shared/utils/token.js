/**
 * Ermittelt den lokal gesetzten JWT-Token
 *
 * @returns {string|null} JWT-Token der aktuellen Sitzung oder null, wenn keiner gespeichert ist.
 */
export function getToken(){
	return localStorage.getItem("jwt_token");
}