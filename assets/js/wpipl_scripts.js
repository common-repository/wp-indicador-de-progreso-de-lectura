//console.log(document.documentElement.scrollHeight); // Altura total de la pantalla
//console.log(document.documentElement.clientHeight); // Altura del view port
const h1 = document.documentElement.scrollHeight; // Altura total de la pantalla
const h2 = document.documentElement.clientHeight; // Altura del view port
const scrollUnit = (h1 - h2) / 100;
const rootSytles = document.documentElement.style; // Recuperar los estilos de root(incluendo las variables)
addEventListener('scroll', function(){
  // scrollY Posicion Y en la pantalla
  // scrollY / scrollUnit Unidades de scroll desplazadas);
  // console.log( Math.round(scrollY / scrollUnit));
  // En la variable width, que es la que pinta la barra de seguimiento
  // le añadimos las unidades de scroll desplazadas
  rootSytles.setProperty('--width', Math.round(scrollY / scrollUnit) );

  rootSytles.setProperty('--bar-color', wpipl_parametros.wpipl_color );



});

// Creamos el elemento, que tendrá la barra de progreso
var x = document.createElement("span");
x.setAttribute("id", "wpipl-progress");
document.body.appendChild(x);
