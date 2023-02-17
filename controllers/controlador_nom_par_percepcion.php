<?php
namespace tglobally\tg_nomina\controllers;
use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_par_percepcion;

class controlador_nom_par_percepcion extends \gamboamartin\nomina\controllers\controlador_nom_par_percepcion {

    public function modifica_ajax(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        if (!isset($_POST['datos'])){
            return $this->retorno_error(mensaje: 'Error no existe el parametro datos', data: $_POST, header: $header,
                ws: $ws);
        }

        $respuesta = array();

        foreach ($_POST['datos'] as $key => $valor){
            $registro['importe_gravado'] = $valor['importe_gravado'];
            $registro['importe_exento'] = $valor['importe_exento'];

            $respuesta = (new nom_par_percepcion($this->link))->modifica_bd(registro: $registro,id: $key);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al modificar percepcion', data: $respuesta, header: $header,
                    ws: $ws);
            }
        }

        echo json_encode($respuesta, JSON_PRETTY_PRINT);
        exit();
    }
}
