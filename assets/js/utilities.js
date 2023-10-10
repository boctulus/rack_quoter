/*
    @author Pablo Bozzolo
*/

if (typeof $ == 'undefined' && typeof jQuery != 'undefined'){
    $=jQuery
}
 
const ucfirst = s => (s && s[0].toUpperCase() + s.slice(1)) || ""


const setNotification = (msg) => {
    if (Array.isArray(msg)){    
        let block_elems = [];

        msg.forEach((el) => {
            block_elems.push(`<li>${el}</li>`)
        })

        msg = '<ul style="list-style: none; margin: 0; padding: 0;">' + block_elems.join("\r\n") + '</ul>'
    }

    $('#modal_notifications').html(msg)
}

const clearNotifications = () => {
    $('#modal_notifications').html()
}

/*
    Antes llamada decodeProp

    Trabaja con var_encode() de PHP
*/
const var_decode = (id) => {
    const el = document.getElementById(id + '-encoded');

    if (el == null){
        throw `Propery ${id} not found`
    }

    const val = el.value;

    if (val == null){
        throw `Value of ${id} is empty?`
    }

    const bin = atob(val);

    if (bin.startsWith('--array--')){
        return JSON.parse(bin.substring(9));
    }

    return bin;
}
