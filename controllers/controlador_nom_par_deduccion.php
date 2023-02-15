<?php
namespace tglobally\tg_nomina\controllers;
use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_par_deduccion;

class controlador_nom_par_deduccion extends \gamboamartin\nomina\controllers\controlador_nom_par_deduccion {

    public function modifica_ajax(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        $registro['importe_gravado'] = $_POST['importe_gravado'];
        $registro['importe_exento'] = $_POST['importe_exento'];

        $respuesta = (new nom_par_deduccion($this->link))->modifica_bd(registro: $registro,id: $this->registro_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al modificar deduccion', data: $respuesta, header: $header,
                ws: $ws);
        }

        print_r($respuesta->mensaje);
        exit();
    }
}
