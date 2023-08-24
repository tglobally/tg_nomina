<?php
namespace tglobally\tg_nomina\models;

use base\orm\_modelo_parent;
use gamboamartin\cat_sat\models\cat_sat_isn;
use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_conf_comision;
use gamboamartin\nomina\models\nom_par_percepcion;
use gamboamartin\organigrama\models\org_empresa;
use PDO;
use stdClass;

class tg_provision extends _modelo_parent {

    public function __construct(PDO $link){
        $tabla = 'tg_provision';
        $columnas = array($tabla=>false, 'tg_tipo_provision'=>$tabla, 'nom_nomina' => $tabla,
            "nom_percepcion" => 'tg_tipo_provision');
        $campos_obligatorios = array('descripcion','codigo');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        if(!isset($this->registro['codigo'])){

            $this->registro['codigo'] =  $this->get_codigo_aleatorio();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo aleatorio',data:  $this->registro);
            }

            if (isset($this->registro['rfc'])){
                $this->registro['codigo'] = $this->registro['rfc'];
            }
        }

        $r_alta_bd = parent::alta_bd($keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta provision',data:  $r_alta_bd);
        }

        return $r_alta_bd;
    }

    public function maqueta_excel_provisiones(int $nom_nomina_id){
        $filtro = array();
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $provisiones = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener provisiones de nomina',data:  $provisiones);
        }

        $registro = (new nom_nomina(link: $this->link))->registro(registro_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener codigo de empleado',data:  $registro);
        }

        $empresa = (new org_empresa($this->link))->registro(registro_id: $registro['org_empresa_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener empresa',data:  $empresa);
        }

        $isn = (new cat_sat_isn($this->link))->filtro_and(filtro: array("cat_sat_isn.dp_estado_id" => $empresa['dp_estado_id']));
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener isn',data:  $isn);
        }

        $fecha = date("Y/m/d");

        $filtro = array();
        $filtro['tg_conf_comision.com_sucursal_id']  = $registro['fc_factura_com_sucursal_id'];
        $filtro_especial[0][$fecha]['operador'] = '>=';
        $filtro_especial[0][$fecha]['valor'] = 'tg_conf_comision.fecha_inicio';
        $filtro_especial[0][$fecha]['comparacion'] = 'AND';
        $filtro_especial[0][$fecha]['valor_es_campo'] = true;

        $filtro_especial[1][$fecha]['operador'] = '<=';
        $filtro_especial[1][$fecha]['valor'] = 'tg_conf_comision.fecha_fin';
        $filtro_especial[1][$fecha]['comparacion'] = 'AND';
        $filtro_especial[1][$fecha]['valor_es_campo'] = true;
        $conf = (new tg_conf_comision(link: $this->link))->filtro_and(filtro: $filtro,filtro_especial: $filtro_especial);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener configuraciones de comision',data:  $conf);
        }

        if($conf->n_registros <= 0 ){
            return $this->error->error(mensaje: "Error no existe una conf. valida para el cliente: ".$registro['fc_factura_com_sucursal_id'],
                data:  $conf);
        }

        $datos = array();

        $datos['id_remunerado'] = $registro['em_empleado_codigo'];
        $datos['nombre_completo'] = $registro['em_empleado_nombre'].' ';
        $datos['nombre_completo'] .= $registro['em_empleado_ap'].' ';
        $datos['nombre_completo'] .= $registro['em_empleado_am'];
        $datos['departamento'] = $registro['org_departamento_descripcion'];
        $datos['registro_patronal'] = $registro['em_registro_patronal_descripcion'];
        $datos['ubicacion'] = $empresa['dp_estado_descripcion'];


        $suma_base_gravable = (new nom_nomina(link: $this->link))->total_percepciones_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de percepciones',
                data: $registro);
        }

        $suma_base_gravable += (new nom_nomina(link: $this->link))->total_otros_pagos_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de otros pagos',
                data: $registro);
        }

        $suma_imss = (new nom_nomina(link: $this->link))->obten_sumatoria_imss(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de imss',
                data: $registro);
        }

        $suma_infonavit = (new nom_nomina(link: $this->link))->obten_infonavit(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener infonavit',
                data: $registro);
        }

        $suma_rcv = (new nom_nomina(link: $this->link))->obten_rcv(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de rcv',
                data: $registro);
        }

        $porcentaje = $isn->registros[0]['cat_sat_isn_porcentaje']=== 0 ?  100 : $isn->registros[0]['cat_sat_isn_porcentaje'];
        $porcentaje /= 100;

        $datos['imss'] = $suma_imss;
        $datos['rcv'] = $suma_rcv;
        $datos['infonavit'] = $suma_infonavit;
        $datos['isn'] = $suma_base_gravable * $porcentaje;
        $datos['isn_adicional'] = $datos['isn'] * $porcentaje;

        $datos['total_impuesto'] = $datos['imss'] +  $datos['rcv'] + $datos['infonavit'] + $datos['isn'] +
            $datos['isn_adicional'] ;

        $datos['prima_vacacional'] = 0;
        $datos['vacaciones'] = 0;
        $datos['aguinaldo'] = 0;
        $datos['prima_antiguedad'] = 0;

        $total = 0;
        foreach ($provisiones->registros as $provision) {
            foreach ($datos as $desc_prov => $dato){
                $descripcion_prov = $provision['tg_tipo_provision_descripcion'];

                if($descripcion_prov === $desc_prov){
                    $datos[$descripcion_prov] = $provision['tg_provision_monto'];
                    $total += $datos[$descripcion_prov];
                }
            }
        }
        $datos['total_provicionado'] = $total;

        $suma_percepcion = (new nom_nomina(link: $this->link))->total_percepciones_monto(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de percepciones',
                data: $registro);
        }

        $subsidio = (new nom_nomina(link: $this->link))->total_otros_pagos_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de otros pagos',
                data: $registro);
        }

        $datos['suma_percepcion'] = $suma_percepcion + $datos['total_provicionado'] + $datos['total_impuesto'] - $subsidio;

        /*$filtro['nom_nomina.id']  = $nom_nomina_id;
        $r_nom_par_percepcion = (new nom_conf_comision($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }*/

        $porcentaje = 100/$conf->registros[0]['tg_conf_comision_porcentaje'];

        $datos['factor_de_servicio'] = $suma_percepcion * $porcentaje;
        $datos['subtotal'] = $datos['suma_percepcion'] + $datos['factor_de_servicio'];
        $datos['iva'] = $datos['subtotal'] * .16;
        $datos['total'] = $datos['subtotal'] + $datos['iva'];

        return $datos;
    }

}