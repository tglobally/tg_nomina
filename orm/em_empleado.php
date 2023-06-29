<?php

namespace tglobally\tg_nomina\models;

use gamboamartin\errores\errores;
use PDO;
use stdClass;

class em_empleado extends \tglobally\tg_empleado\models\em_empleado {


    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        $registros_conf_nomina['nom_conf_nomina_id'] = $this->registro['nom_conf_nomina_id'];
        unset($this->registro['nom_conf_nomina_id']);

        $alta_bd = parent::alta_bd($keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta empleado',data: $alta_bd);
        }

        return $alta_bd;
    }

}