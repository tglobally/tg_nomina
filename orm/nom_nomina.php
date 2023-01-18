<?php
namespace tglobally\tg_nomina\models;

use gamboamartin\errores\errores;
use stdClass;

class nom_nomina extends \gamboamartin\nomina\models\nom_nomina {

    public function alta_bd(): array|stdClass
    {
        $alta = parent::alta_bd();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar nomina', data: $alta);
        }

        $acciones = $this->conf_provisiones_acciones(em_empleado_id: $this->registro['em_empleado_id'],
            nom_nomina_id: $alta->registro_id, fecha: $this->registro['fecha_pago']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al ejecutar acciones de conf. de provisiones', data: $acciones);
        }

        return $alta;
    }

    public function conf_provisiones_acciones(int $em_empleado_id, int $nom_nomina_id, string $fecha): array|stdClass
    {
        $data = $this->get_tg_conf_provisiones(em_empleado_id: $em_empleado_id, fecha: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener conf. de provisiones del empleado', data: $data);
        }

        foreach ($data->registros as $configuracion){

            $datos = $this->maqueta_data_provision(tg_conf_provision: $configuracion,nom_nomina_id: $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al maquetar datos de conf. de provisiones del empleado',
                    data: $datos);
            }

            $alta_provision = (new tg_provision($this->link))->alta_registro(registro: $datos);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al dar de alta conf. de provisiones del empleado',
                    data: $alta_provision);
            }

        }

        return $data;
    }

    public function get_tg_conf_provisiones(int $em_empleado_id, string $fecha): array|stdClass
    {
        if($em_empleado_id <= 0){
            return $this->error->error(mensaje: 'Error $em_empleado_id debe ser mayor a 0', data: $em_empleado_id);
        }

        $filtro['em_empleado.id'] = $this->registro['em_empleado_id'];
        $filtro_especial[0][$fecha]['operador'] = '>=';
        $filtro_especial[0][$fecha]['valor'] = 'tg_conf_provision.fecha_inicio';
        $filtro_especial[0][$fecha]['comparacion'] = 'AND';
        $filtro_especial[0][$fecha]['valor_es_campo'] = true;

        $filtro_especial[1][$fecha]['operador'] = '<=';
        $filtro_especial[1][$fecha]['valor'] = 'tg_conf_provision.fecha_fin';
        $filtro_especial[1][$fecha]['comparacion'] = 'AND';
        $filtro_especial[1][$fecha]['valor_es_campo'] = true;

        $conf = (new tg_conf_provision($this->link))->filtro_and(filtro: $filtro, filtro_especial: $filtro_especial);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al filtrar conf. de provisiones del empleado', data: $conf);
        }

        return $conf;
    }

    public function maqueta_data_provision(array $tg_conf_provision, int $nom_nomina_id): array|stdClass
    {
        $data = array();
        $data['codigo'] = $tg_conf_provision['tg_conf_provision_codigo'].$nom_nomina_id;
        $data['descripcion'] = $tg_conf_provision['tg_conf_provision_descripcion'].$nom_nomina_id;
        $data['tg_tipo_provision_id'] = $tg_conf_provision['tg_tipo_provision_id'];
        $data['nom_nomina_id'] = $nom_nomina_id;
        $data['monto'] = $tg_conf_provision['tg_conf_provision_monto'];

        return $data;
    }

}