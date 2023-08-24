<?php
namespace tglobally\tg_nomina\models;

use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_conf_empleado;
use tglobally\tg_nomina\models\nom_nomina;
use stdClass;

class nom_periodo extends \gamboamartin\nomina\models\nom_periodo {

    public function alta_empleado_periodo(array $empleado, array $nom_periodo): array|stdClass
    {
        $filtro['em_empleado.id'] = $empleado['em_empleado_id'];
        $nom_conf_empleado = (new nom_conf_empleado($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nom_conf_empleado', data: $nom_conf_empleado);
        }

        $nom_conf_empleado_reg['nom_conf_empleado_id'] = 1;
        $nom_conf_empleado_reg['em_cuenta_bancaria_id'] = 1;
        if($nom_conf_empleado->n_registros > 0){
            $nom_conf_empleado_reg = $nom_conf_empleado->registros[0];
        }

        $nomina_empleado = $this->genera_registro_nomina_empleado(em_empleado:$empleado, nom_periodo: $nom_periodo,
            nom_conf_empleado: $nom_conf_empleado_reg);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al maquetar nomina del empleado', data: $nomina_empleado);
        }

        $alta_empleado = $this->alta_nomina_empleado($nomina_empleado);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta la nomina del empleado', data: $alta_empleado);
        }

        return $alta_empleado;
    }

    private function alta_nomina_empleado(mixed $em_empleado) : array|stdClass{
        $modelo = new nom_nomina(link: $this->link);
        $modelo->registro = $em_empleado;

        $r_alta_bd = $modelo->alta_bd();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar nomina', data: $r_alta_bd);
        }
        return $r_alta_bd;
    }

    private function genera_registro_nomina_empleado(mixed $em_empleado, mixed $nom_periodo, mixed $nom_conf_empleado) : array{
        $keys = array('em_registro_patronal_id','em_empleado_id');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys ,registro:  $em_empleado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar em_empleado', data: $valida);
        }

        $keys = array('nom_periodo_fecha_pago','nom_periodo_cat_sat_periodicidad_pago_nom_id',
            'nom_periodo_fecha_inicial_pago','nom_periodo_fecha_final_pago','cat_sat_periodicidad_pago_nom_n_dias',
            'nom_periodo_id');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys ,registro:  $nom_periodo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nom_periodo', data: $valida);
        }

        $keys = array('nom_conf_empleado_id','em_cuenta_bancaria_id');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys ,registro:  $nom_conf_empleado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nom_conf_empleado', data: $valida);
        }


        $registros['em_registro_patronal_id'] = $em_empleado['em_registro_patronal_id'];
        $registros['em_empleado_id'] = $em_empleado['em_empleado_id'];
        $registros['nom_conf_empleado_id'] = $nom_conf_empleado['nom_conf_empleado_id'];
        $registros['em_cuenta_bancaria_id'] = $nom_conf_empleado['em_cuenta_bancaria_id'];
        $registros['folio'] = rand();
        $registros['fecha'] = $nom_periodo['nom_periodo_fecha_pago'];
        $registros['cat_sat_tipo_nomina_id'] = 1;
        $registros['cat_sat_periodicidad_pago_nom_id'] = $nom_periodo['nom_periodo_cat_sat_periodicidad_pago_nom_id'];
        $registros['fecha_pago'] =$nom_periodo['nom_periodo_fecha_pago'];
        $registros['fecha_inicial_pago'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];
        $registros['fecha_final_pago'] = $nom_periodo['nom_periodo_fecha_final_pago'];
        $registros['num_dias_pagados'] = $nom_periodo['cat_sat_periodicidad_pago_nom_n_dias'];
        $registros['nom_periodo_id'] = $nom_periodo['nom_periodo_id'];
        $registros['descuento'] = 0;

        return $registros;
    }

}
