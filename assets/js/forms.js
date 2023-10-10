
/*
    Los elementos del formulario seran recogidos en un objeto

    Si use_id es true, se usa su id y si es false, el name

    De especificarse un prefijo, puede eliminarlo de cada nombre de campo

    Ej:

    getFormData(this, false, 'col-')
*/
const getFormData = (formElem, use_id = true, prefix = null) => {
    const jsonData = {};

    formElem.find(':input').each((_, input) => {
        const name = use_id ? input.id : input.name;

        if (prefix != null && name.startsWith(prefix)) {
            name = name.substr(prefix.length);
        }

        if (input.type === 'select-one') {
            const selectedOption = formElem.find(`[name="${input.name}"] option:selected`);
            jsonData[name] = selectedOption.attr('id');
        } else {
            jsonData[name] = input.value;
        }
    });

    return jsonData;
};

/*
    Los elementos del formulario poseen cierta clase de css,
    seran recogidos en el objeto

    Ej:

    getObjFromElems('col2save')

    De especificarse un prefijo, puede eliminarlo de cada nombre de campo

    Ej:

    getObjFromElems('col2save', 'col-')

    Si use_name es true, buscara los elementos por name y no por id
*/
const getFormDataByClassName = (elem_class, use_id = true, prefix = null) => {
    let obj = {};

    $('.'+elem_class).each((ix, el) => {
        let field  = use_id ? el.id : el.name;

        if (prefix != null && field.startsWith(prefix)){
            field = field.substr(prefix.length);
        }

        obj[field] = el.value;
    })

    return obj;
}

/*
    Rellena un formulario 

    Ej:

        fillForm(obj, 'col-')
        fillForm(data.data, 'col-', { readonly: true })

    Funciona tambien usando como datasource un storage

    Ej:

        fillForm(fromStorage().form.contact)


    Nota: tuvo que implementarse con JS vanilla porque jQuery
    falla para setear un OPTION de un SELECT !!! 
*/
function fillForm(data_obj, prefix = null) {
    if (typeof data_obj !== 'object') {
        return;
    }

    for (const [key, value] of Object.entries(data_obj)) {
        const selector = '#' + (prefix == null ? '' : prefix) + key;
        const inputElem = document.querySelector(selector);

        if (!inputElem) {
            continue; // Skip if the element is not found
        }

        if (inputElem.tagName === 'INPUT' || inputElem.tagName === 'TEXTAREA') {
            inputElem.value = value;
        } else if (inputElem.tagName === 'SELECT') {
            const optionToSelect = inputElem.querySelector(`option[id="${value}"]`);

            if (optionToSelect) {
                const option_val = optionToSelect.value;

                const options = inputElem.querySelectorAll('option');
                options.forEach((option) => {
                    option.selected = false;
                });

                inputElem.value = option_val;
                optionToSelect.selected = true;
                inputElem.dispatchEvent(new Event('change')); // Trigger change event for SELECT
            } else {
                console.warn(`Option with ID "${value}" not found in SELECT with selector "${selector}".`);
            }
        } else {
            console.warn(`Element with selector "${selector}" is not an input or select element.`);
        }
    }
}

/*
    Ej:

    setAttr(viewData.fields, 'col-', { readonly: false })
*/
const setAttr = (ids,  prefix = null, attributes = {}) => {
    for (let id of ids) {
        const selector = $('#' + (prefix == null ? '' : prefix) + id)

        for (const a in attributes) {
            selector.attr(a, attributes[a]); 
        }        
    }
}
