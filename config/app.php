<?php

/*
| Laravel combina este archivo con su configuración por defecto,
| así que aquí solo va lo que cambia.
|
| D-01 (v1.1): la versión 1.0 usaba UTC y mostraba las horas con 4 horas de
| diferencia respecto a Bolivia. Con un plazo de atención de 4 horas eso
| no se puede permitir.
*/

return [

    'timezone' => env('APP_TIMEZONE', 'America/La_Paz'),

];
