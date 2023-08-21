<?php
namespace tglobally\tg_nomina\models;

use base\orm\_modelo_parent;

use gamboamartin\comercial\models\com_sucursal;
use gamboamartin\empleado\models\em_registro_patronal;
use gamboamartin\errores\errores;

use gamboamartin\facturacion\models\fc_csd;
use gamboamartin\nomina\models\nom_periodo;
use gamboamartin\organigrama\models\org_sucursal;
use PDO;
use stdClass;

class tg_manifiesto extends _modelo_parent{

    public function __construct(PDO $link){
        $tabla = 'tg_manifiesto';
        $columnas = array($tabla=>false, 'tg_tipo_servicio' =>$tabla,'tg_sucursal_alianza'=>$tabla,'fc_csd'=>$tabla,
            'org_sucursal'=>$tabla, 'org_empresa'=>'org_sucursal','tg_agrupador'=>$tabla,
            'com_sucursal'=>'tg_sucursal_alianza', 'com_cliente'=>'com_sucursal',
            'tg_cte_alianza'=>'tg_sucursal_alianza', 'nom_conf_nomina'=>'tg_tipo_servicio');
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis',
            'fc_csd_id','tg_tipo_servicio_id','fecha_envio','fecha_pago');

        $campos_view['com_sucursal_id']['type'] = 'selects';
        $campos_view['com_sucursal_id']['model'] = (new com_sucursal($link));
        $campos_view['tg_cte_alianza_id']['type'] = 'selects';
        $campos_view['tg_cte_alianza_id']['model'] = (new tg_cte_alianza($link));
        $campos_view['tg_tipo_servicio_id']['type'] = 'selects';
        $campos_view['tg_tipo_servicio_id']['model'] = (new tg_tipo_servicio($link));
        $campos_view['org_sucursal_id']['type'] = 'selects';
        $campos_view['org_sucursal_id']['model'] = (new org_sucursal($link));
        $campos_view['tg_agrupador_id']['type'] = 'selects';
        $campos_view['tg_agrupador_id']['model'] = (new tg_agrupador($link));
        $campos_view['fecha_envio']['type'] = 'dates';
        $campos_view['fecha_pago']['type'] = 'dates';
        $campos_view['fecha_inicial_pago']['type'] = 'dates';
        $campos_view['fecha_final_pago']['type'] = 'dates';



        $columnas_extra['tg_manifiesto_n_nominas'] =
            "IFNULL ((SELECT COUNT(*) FROM  nom_nomina 
            INNER JOIN tg_manifiesto_periodo ON tg_manifiesto_periodo.tg_manifiesto_id = tg_manifiesto.id
            INNER JOIN nom_periodo ON nom_nomina.nom_periodo_id = tg_manifiesto_periodo.nom_periodo_id
            AND nom_nomina.nom_periodo_id = nom_periodo.id), 0)";

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view:  $campos_view,columnas_extra: $columnas_extra);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        $filtro_csd['org_sucursal.id'] = $this->registro['org_sucursal_id'];
        $fc_csd = (new fc_csd($this->link))->filtro_and(filtro: $filtro_csd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro empresa',data: $fc_csd);
        }

        if($fc_csd->n_registros < 1){
            return $this->error->error(mensaje: 'Error no existe registro de fc_csd relacionado',
                data: $fc_csd);
        }

        $fc_csd = $fc_csd->registros[0];

        $tg_sucursal_alianza = $this->obten_sucursal_alianza(com_sucursal_id: $this->registro['com_sucursal_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro sucursal alianza',data: $tg_sucursal_alianza);
        }

        $this->registro['tg_sucursal_alianza_id'] = $tg_sucursal_alianza['tg_sucursal_alianza_id'];

        if (!isset($this->registro['codigo'])) {
            $filtro_cod['tg_manifiesto.org_sucursal_id'] = $this->registro['org_sucursal_id'];
            $filtro_cod['tg_manifiesto.tg_sucursal_alianza_id'] = $this->registro['tg_sucursal_alianza_id'];

            $ultimo_registro_man = $this->filtro_and(filtro: $filtro_cod,order: array($this->tabla.'.id'=>'DESC'));
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener ultimo registro manifiesto',
                    data: $ultimo_registro_man);
            }

            $consecutivo = 1;
            $this->registro['codigo'] = $fc_csd['org_empresa_codigo'].$tg_sucursal_alianza['com_cliente_codigo'];
            $this->registro['codigo'] .= $consecutivo;

            if((int)$ultimo_registro_man->n_registros > 0){
                $buscar = array($fc_csd['org_empresa_codigo'], $tg_sucursal_alianza['com_cliente_codigo']);
                $consecutivo = str_replace($buscar,"",$ultimo_registro_man->registros[0][$this->tabla.'_codigo']);
                $consecutivo = $consecutivo + 1;

                $this->registro['codigo'] = $fc_csd['org_empresa_codigo'].$tg_sucursal_alianza['com_cliente_codigo'];
                $this->registro['codigo'] .= $consecutivo;
            }
        }

        if (!isset($this->registro['descripcion'])) {
            $this->registro['descripcion'] = $fc_csd['org_empresa_rfc'].' ';
                $this->registro['descripcion'] .= $this->registro['codigo'];
            }

            if (!isset($this->registro['descripcion_select'])) {
                $this->registro['descripcion_select'] = $this->registro['codigo'].' ';
            $this->registro['descripcion_select'] .= $tg_sucursal_alianza['com_cliente_rfc'].' ';
            $this->registro['descripcion_select'] .= $fc_csd['org_empresa_rfc'];
        }

        if (!isset($this->registro['codigo_bis'])) {
            $this->registro['codigo_bis'] = $this->registro['codigo'];
        }

        if (!isset($this->registro['alias'])) {
            $alias = $this->registro['codigo'].' ';
            $alias .= $tg_sucursal_alianza['com_cliente_rfc'].' ';
            $alias .= $fc_csd['org_empresa_rfc'];

            $this->registro['alias'] = strtoupper($alias);
        }

        if(!isset($this->registro['fc_csd_id']) || $this->registro['fc_csd_id'] === ''){
            $this->registro['fc_csd_id'] = $fc_csd['fc_csd_id'];
        }

        if(isset($this->registro['com_sucursal_id'])){
            unset($this->registro['com_sucursal_id']);
        }
        if(isset($this->registro['tg_cte_alianza_id'])){
            unset($this->registro['tg_cte_alianza_id']);
        }

        $this->registro['fecha_envio'] = date("Y/m/d");

        $r_alta_bd = parent::alta_bd(keys_integra_ds: $keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta manifiesto',data: $r_alta_bd);
        }

        $tg_tipo_servicio = (new tg_tipo_servicio($this->link))->registro(
            registro_id: $this->registro['tg_tipo_servicio_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener tipo de servicio',data: $tg_tipo_servicio);
        }

        $filtro_im['fc_csd.id'] = $this->registro['fc_csd_id'];
        $em_registro_patronal = (new em_registro_patronal($this->link))->filtro_and(filtro: $filtro_im);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error obtener registro patronal',data:  $em_registro_patronal);
        }

        if($em_registro_patronal->n_registros < 1){
            return $this->error->error(mensaje: 'Error no existe registro patronal relacionado',
                data:  $em_registro_patronal);
        }

        $registro_periodo['codigo'] = $this->registro['codigo'];
        $registro_periodo['descripcion'] = $this->registro['descripcion'];
        $registro_periodo['fecha_pago'] = $this->registro['fecha_pago'];
        $registro_periodo['fecha_inicial_pago'] = $this->registro['fecha_inicial_pago'];
        $registro_periodo['fecha_final_pago'] = $this->registro['fecha_final_pago'];
        $registro_periodo['cat_sat_periodicidad_pago_nom_id'] = $tg_tipo_servicio['cat_sat_periodicidad_pago_nom_id'];
        $registro_periodo['im_registro_patronal_id'] = $em_registro_patronal->registros[0]['em_registro_patronal_id'];
        $registro_periodo['em_registro_patronal_id'] = $em_registro_patronal->registros[0]['em_registro_patronal_id'];
        $registro_periodo['nom_tipo_periodo_id'] = 1;
        $registro_periodo['cat_sat_tipo_nomina_id'] = $tg_tipo_servicio['cat_sat_tipo_nomina_id'];

        $r_nom_periodo = (new nom_periodo($this->link))->alta_registro(registro: $registro_periodo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta periodo',data:  $r_nom_periodo);
        }

        $registro_man['codigo'] = $this->registro['codigo'];
        $registro_man['descripcion'] = $this->registro['descripcion'];
        $registro_man['tg_manifiesto_id'] = $r_alta_bd->registro_id;
        $registro_man['nom_periodo_id'] = $r_nom_periodo->registro_id;
        $r_tg_manifiesto_periodo = (new tg_manifiesto_periodo($this->link))->alta_registro(registro:$registro_man);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta manifiesto_periodo',data:  $r_tg_manifiesto_periodo);
        }

        return $r_alta_bd;
    }


    public function obten_sucursal_alianza(int $com_sucursal_id){
        $filtro['com_sucursal.id'] = $com_sucursal_id;
        $tg_sucursal_alianza = (new tg_sucursal_alianza($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener configuracion tg_sucursal_alianza',
                data: $tg_sucursal_alianza);
        }

        if($tg_sucursal_alianza->n_registros < 1){
            return $this->error->error(mensaje: 'Error no existe alianza',
                data: $tg_sucursal_alianza);
        }

        return $tg_sucursal_alianza->registros[0];
    }

    public function modifica_bd(array $registro, int $id, bool $reactiva = false, array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        $tg_sucursal_alianza = $this->obten_sucursal_alianza(com_sucursal_id: $registro['com_sucursal_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro empresa',data: $tg_sucursal_alianza);
        }
        $this->registro['tg_sucursal_alianza_id'] = $tg_sucursal_alianza['tg_sucursal_alianza_id'];

        if(isset($registro['com_sucursal_id'])){
            unset($registro['com_sucursal_id']);
        }
        if(isset($registro['tg_cte_alianza_id'])){
            unset($registro['tg_cte_alianza_id']);
        }

        $r_modifica_bd = parent::modifica_bd($registro, $id, $reactiva, $keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar manifiesto',data: $r_modifica_bd);
        }

        return $r_modifica_bd;
    }

    public function maqueta_encabezado_excel(array $registros_xls){
        $r_manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $this->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al manifiesto',data:  $r_manifiesto);
        }

        $r_fc_csd = (new fc_csd($this->link))->registro(registro_id: $r_manifiesto['tg_manifiesto_fc_csd_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al registro de empresa',data:  $r_fc_csd);
        }

        $r_tg_sucursal_alianza = (new tg_sucursal_alianza($this->link))->registro(
            registro_id: $r_manifiesto['tg_manifiesto_tg_sucursal_alianza_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al registro de cliente',data:  $r_tg_sucursal_alianza);
        }

        $r_tg_manifiesto_periodo = (new tg_manifiesto_periodo($this->link))->get_periodos_manifiesto(
            tg_manifiesto_id:  $this->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener manifiesto periodo',data:  $r_tg_manifiesto_periodo);
        }

        return $registros_xls;
    }
}