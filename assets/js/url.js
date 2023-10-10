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
const getParam = (param, url = null) => {
	if (url === null){
		url = window.location.href;
	}

  var urlObj = new URL(url);
  var searchParams = urlObj.searchParams;
  var qValue = searchParams.get(param);
  return qValue;
}

/*
  Obtiene cadena de queries previamente sanitizada
  para evitar devolver null por ejemplo
*/
const getQueryString = (obj) => {
  const sanitizedObj = {};

  // Realiza las conversiones
  for (const key in obj) {
    if (obj[key] === null) {
      sanitizedObj[key] = '';
    } else if (obj[key] === false) {
      sanitizedObj[key] = 'false';
    } else if (obj[key] === true) {
      sanitizedObj[key] = 'true';
    } else {
      sanitizedObj[key] = obj[key];
    }
  }

  return new URLSearchParams(sanitizedObj).toString();
};

const nonEmptyValues = (obj) => {
  return Object.values(obj).every(value => value !== null && value !== "" && value !== undefined) &&
         Object.keys(obj).every(key => obj[key] !== undefined);
};


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


/*
  History API

  A diferencia de setQueryParamsIntoHistoryAPI(), esta funcion mantendra cualquier query param existente.
*/
const mergeQueryParamsIntoHistoryAPI = (params, state = {}, push_or_replace = 'push') => {
  if (push_or_replace !== 'push' && push_or_replace !== 'replace') {
    throw "Invalid parameter";
  }

  // Obtén la URL actual incluyendo los slugs pero sin los query params
  const urlBase = window.location.origin + window.location.pathname;

  // Obtiene los query params actuales
  const queryParams = new URLSearchParams(window.location.search);

  // Agrega o sustituye los nuevos parámetros
  for (const key in params) {
    queryParams.set(key, params[key]);
  }

  // Obtiene el string de parámetros actualizado
  const queryString = queryParams.toString();

  // Combina la URL base con los nuevos query params
  const nuevaURL = `${urlBase}?${queryString}`;

  if (push_or_replace === 'push') {
    history.pushState(state, '', nuevaURL);
  } else {
    history.replaceState(state, '', nuevaURL);
  }
};
