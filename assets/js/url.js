
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