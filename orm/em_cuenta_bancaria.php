<?php
namespace tglobally\tg_nomina\models;

use gamboamartin\errores\errores;
use stdClass;

class em_cuenta_bancaria extends \gamboamartin\empleado\models\em_cuenta_bancaria {

    public function maqueta_excel_pagos(array $data_general): array{

        $datos = array();
        $datos['id_rem'] = $data_general['id_rem'];
        $datos['nombre_completo'] = $data_general['nombre_completo'];
        $datos['neto_a_pagar'] = $data_general['neto_a_pagar'];
        $datos['cuenta'] = "'".$data_general['cuenta'];
        $datos['clabe'] = "'".$data_general['clabe'];
        $datos['banco'] = $data_general['banco'];
        $datos['subsidio'] = $data_general['subsidio'];

        return $datos;
    }

}
