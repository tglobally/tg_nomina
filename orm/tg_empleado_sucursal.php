<?php

namespace tglobally\tg_nomina\models;

use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_rel_empleado_sucursal;
use stdClass;

class tg_empleado_sucursal extends \tglobally\tg_empleado\models\tg_empleado_sucursal
{

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        $r_alta_bd = parent::alta_bd($keys_integra_ds);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta sucursal empleado', data: $r_alta_bd);
        }

        $alta_nom = (new nom_rel_empleado_sucursal($this->link))->alta_registro($this->registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta sucursal empleado', data: $alta_nom);
        }

        return $r_alta_bd;
    }
}