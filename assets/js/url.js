const baseURL = (include_params = false) => {
  if (include_params){
    return window.location.origin + window.location.pathname;
  }

  return window.location.origin;
} 

/*
    Obtiene el parametro de una URL

    Ej:
    
    getParam('http://woo2.lan/wp-admin/admin.php?page=mutawp-store&tab=search&q=divi', 'q')
    
    o

    getParam('q')
*/
function getParam(param, url = null) {
	if (url === null){
		url = window.location.href;
	}

  var urlObj = new URL(url);
  var searchParams = urlObj.searchParams;
  var qValue = searchParams.get(param);
  return qValue;
}

/*
  History API

  Ej:

  setQueryParamsIntoHistoryAPI({ parametro1: 'valor1', parametro2: 'valor2' })

  */
const setQueryParamsIntoHistoryAPI = (params, state = {}, push_or_replace = 'push') => {
  if (push_or_replace != 'push' && push_or_replace != 'replace'){
    throw "Invalid parameter";
  }

  // Obtén la URL actual incluyendo los slugs pero sin los query params
  const urlBase = window.location.origin + window.location.pathname;

  // Crea un nuevo objeto URLSearchParams usando los parámetros recibidos
  const queryParams = new URLSearchParams(params);

  // Obtiene el string de parámetros
  const queryString = queryParams.toString();

  // Combina la URL base con los nuevos query params
  const nuevaURL = `${urlBase}?${queryString}`;

  if (push_or_replace == 'push'){
    history.pushState(state, '', nuevaURL);
  } else {
    history.replaceState(state, '', nuevaURL);
  }
};
