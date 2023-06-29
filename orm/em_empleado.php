<?php

namespace tglobally\tg_nomina\models;

use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_conf_empleado;
use stdClass;

class em_empleado extends \tglobally\tg_empleado\models\em_empleado {


    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        $nom_conf_empleado['nom_conf_nomina_id'] = $this->registro['nom_conf_nomina_id'];
        $registros_em_cuenta_bancaria['bn_sucursal_id'] = $this->registro['bn_sucursal_id'];
        $registros_em_cuenta_bancaria['num_cuenta'] = $this->registro['num_cuenta'];
        $registros_em_cuenta_bancaria['clabe'] = $this->registro['clabe'];

        unset($this->registro['nom_conf_nomina_id'], $this->registro['bn_sucursal_id'],
            $this->registro['num_cuenta'], $this->registro['clabe']);

        $alta_bd = parent::alta_bd($keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta empleado',data: $alta_bd);
        }

        $registros_em_cuenta_bancaria['descripcion'] = "Alta cuenta empleado " . $alta_bd->registro_id;
        $registros_em_cuenta_bancaria['em_empleado_id'] = $alta_bd->registro_id;

        $alta_cuenta_bancaria = (new em_cuenta_bancaria($this->link))->alta_registro(registro: $registros_em_cuenta_bancaria);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error ingresar cuenta bancaria',data: $alta_cuenta_bancaria);
        }

        $nom_conf_empleado['descripcion'] = "Alta conf. empleado " . $alta_cuenta_bancaria->registro_id;
        $nom_conf_empleado['em_cuenta_bancaria_id'] = $alta_cuenta_bancaria->registro_id;

        $alta_nom_conf_empleado = (new nom_conf_empleado($this->link))->alta_registro(registro: $nom_conf_empleado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error ingresar conf. empleadp ',data: $alta_nom_conf_empleado);
        }

        return $alta_bd;
    }

}