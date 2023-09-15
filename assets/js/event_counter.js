/*
    Funcion contadora de "eventos" que almacena el valor en localStorage

    Si han pasado mas de {resetThresholdInSeconds} desde que el contador
    fuera inicializado,... vuelve a cero y comienza a contar de nuevo

    @param eventCounter int tiempo en segundos
    @return int cantidad de eventos
*/
const eventCounter = (resetThresholdInSeconds) => {
    // Comprobar la última vez que se inicializó el contador en localStorage
    const lastInitializedTime = localStorage.getItem('initializedTime');

    // Obtener la hora actual
    const currentTime = new Date().getTime();

    // Si no se ha inicializado o ha pasado más tiempo del umbral, reiniciar el contador
    if (!lastInitializedTime || (currentTime - lastInitializedTime > resetThresholdInSeconds * 1000)) {
        localStorage.setItem('event_counter', '0'); // Reiniciar el contador a cero
        localStorage.setItem('initializedTime', currentTime); // Actualizar el tiempo de inicialización
    }

    // Obtener el valor actual del contador
    let event_counter = parseInt(localStorage.getItem('event_counter')) || 0;

    // Incrementar el contador en 1
    event_counter++;

    // Guardar el nuevo valor del contador en localStorage
    localStorage.setItem('event_counter', event_counter);

    // Mostrar el valor del contador en la consola
    // console.log(`El contador es: ${event_counter}`);
    
    return event_counter;
};

const getEventCounter = () => {
    return localStorage.getItem('event_counter')
}