
/*
    Cronometro

    Ej:
        const cronometer = new Cronometer("display")      

        cronometer.setTimeoutCallback(() => {
            onExecuted()
        });

        document.getElementById("btn-restart").addEventListener("click", () => {          
            // ...
            cronometer.start("00:35:00")  
        });  

        cronometer.start();


    # Iniciar
    
    Se puede iniciar tomando el valor de el INPUT

        cronometer.start()
    
    O bien pasarle el tiempo
    
        cronometer.start('00:35:00')

    Se recomienda no ejectar el crono hasta que no se haya renderizado la pagina

        document.addEventListener("DOMContentLoaded", function() {
            cronometer.start('00:35:00')
        });

    # Detener

        cronometer.stop();

    # Re-iniciar

        cronometer.start('00:35:00')

    O sea, para re-iniciar es necesario pasar el valor de inicio.

    Y si quisieramos asociar el restart a un boton:

        document.getElementById("btn-restart").addEventListener("click", () => {          
            // ...
            cronometer.start("00:35:00")  
        });  
*/

class Cronometer {
    setInitialTimeCallback(fn){
        this.tiempoInicialCallback = fn
    }

    constructor(displayId = null) {
        this.currentCronoInterval = null;
        this.setDisplayId(displayId)
    }

    setDisplayId(id){
        this.displayId = id;       
    }

    /*
        Ej:

        cronometer.setRestartCallback(() => {
            // ...
            cronometer.start("00:35:00")
        });
    */
    setTimeoutCallback(fn){
        if (!(fn instanceof Function)) {
            throw 'fn is not a valid callback';
        }

        this.timeoutCallback = fn;       
    }

    stop() {
        clearInterval(this.currentCronoInterval);
    }

    start(tiempoInicial = null) {
        const display  = document.getElementById(this.displayId);    
        const ini_time = display.value
        const tiempo   = (tiempoInicial || ini_time).split(":");

        // Usado para renderizar el tiempo inicial del contador de ser necesario
        if (tiempoInicial = null){
            display.value = ini_time
        }        

        this.stop();
        
        let horas    = parseInt(tiempo[0]);
        let minutos  = parseInt(tiempo[1]);
        let segundos = parseInt(tiempo[2]) + 1; //sumo uno para que se alcance a visualizar

        this.currentCronoInterval = setInterval(() => {
            if (segundos > 0) {
                segundos--;
            } else {
                if (minutos > 0) {
                    minutos--;
                    segundos = 59;
                } else {
                    if (horas > 0) {
                        horas--;
                        minutos = 59;
                        segundos = 59;
                    } else {
                        clearInterval(this.currentCronoInterval);
                    }
                }
            }

            const nuevoTiempo = `${horas.toString().padStart(2, "0")}:${minutos.toString().padStart(2, "0")}:${segundos.toString().padStart(2, "0")}`;
            display.value = nuevoTiempo;

            if (horas === 0 && minutos === 0 && segundos === 0) {
                clearInterval(this.currentCronoInterval);
                // Aquí puedes agregar acciones adicionales cuando el contador llegue a cero.

                if (typeof this.timeoutCallback != 'undefined'){
                    this.timeoutCallback()
                } else {
                    alert("¡Tiempo agotado!");
                }
                
            }
        }, 1000);
    }
}



