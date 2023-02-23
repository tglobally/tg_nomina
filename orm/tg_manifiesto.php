<?php
namespace tglobally\tg_nomina\models;

use base\orm\_modelo_parent;
use base\orm\modelo;

use gamboamartin\comercial\models\com_sucursal;
use gamboamartin\empleado\models\em_registro_patronal;
use gamboamartin\errores\errores;

use gamboamartin\facturacion\models\fc_csd;
use gamboamartin\im_registro_patronal\models\im_registro_patronal;
use gamboamartin\nomina\models\nom_periodo;
use PDO;
use stdClass;

class tg_manifiesto extends _modelo_parent{

    public function __construct(PDO $link){
        $tabla = 'tg_manifiesto';
        $columnas = array($tabla=>false, 'fc_csd'=>$tabla,'tg_tipo_servicio' =>$tabla,'tg_sucursal_alianza'=>$tabla,
            'com_sucursal'=>'tg_sucursal_alianza','tg_cte_alianza'=>'tg_sucursal_alianza');
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis',
            'fc_csd_id','tg_tipo_servicio_id','fecha_envio','fecha_pago');

        $campos_view['com_sucursal_id']['type'] = 'selects';
        $campos_view['com_sucursal_id']['model'] = (new com_sucursal($link));
        $campos_view['tg_cte_alianza_id']['type'] = 'selects';
        $campos_view['tg_cte_alianza_id']['model'] = (new tg_cte_alianza($link));
        $campos_view['fc_csd_id']['type'] = 'selects';
        $campos_view['fc_csd_id']['model'] = (new fc_csd($link));
        $campos_view['tg_tipo_servicio_id']['type'] = 'selects';
        $campos_view['tg_tipo_servicio_id']['model'] = (new tg_tipo_servicio($link));
        $campos_view['fecha_envio']['type'] = 'dates';
        $campos_view['fecha_pago']['type'] = 'dates';
        $campos_view['fecha_inicial_pago']['type'] = 'dates';
        $campos_view['fecha_final_pago']['type'] = 'dates';

        $columnas_extra['tg_manifiesto_n_nominas'] = "(SELECT COUNT(*) FROM nom_nomina WHERE nom_periodo_id = tg_manifiesto_id)";

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view:  $campos_view ,columnas_extra: $columnas_extra);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        $fc_csd = (new fc_csd($this->link))->registro(registro_id: $this->registro['fc_csd_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro empresa',data: $fc_csd);
        }

        $tg_sucursal_alianza = $this->obten_sucursal_alianza(com_sucursal_id: $this->registro['com_sucursal_id'],
            tg_cte_alianza_id: $this->registro['tg_cte_alianza_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro sucursal alianza',data: $tg_sucursal_alianza);
        }

        $this->registro['tg_sucursal_alianza_id'] = $tg_sucursal_alianza['tg_sucursal_alianza_id'];

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

        if(isset($this->registro['com_sucursal_id'])){
            unset($this->registro['com_sucursal_id']);
        }
        if(isset($this->registro['tg_cte_alianza_id'])){
            unset($this->registro['tg_cte_alianza_id']);
        }

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
        $im_registro_patronal = (new im_registro_patronal($this->link))->filtro_and(filtro: $filtro_im);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error obtener registro patronal',data:  $im_registro_patronal);
        }

        $filtro_im['fc_csd.id'] = $this->registro['fc_csd_id'];
        $em_registro_patronal = (new em_registro_patronal($this->link))->filtro_and(filtro: $filtro_im);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error obtener registro patronal',data:  $em_registro_patronal);
        }

        if($im_registro_patronal->n_registros < 1){
            return $this->error->error(mensaje: 'Error no existe registro patronal relacionado',
                data:  $im_registro_patronal);
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
        $registro_periodo['im_registro_patronal_id'] = $im_registro_patronal->registros[0]['im_registro_patronal_id'];
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

        if(isset($this->registro['com_sucursal_id']) && $this->registro['com_sucursal_id']!==''){
            $filtro_emp_bis['com_sucursal.id'] = $this->registro['com_sucursal_id'];
            $em_registro_patronal_bis = (new em_registro_patronal($this->link))->filtro_and(filtro: $filtro_emp_bis);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener registro patronal bis',
                    data:  $em_registro_patronal_bis);
            }

            $registro_periodo_bis['codigo'] = $this->registro['codigo'].'_bis';
            $registro_periodo_bis['descripcion'] = $this->registro['descripcion'].'_bis';
            $registro_periodo_bis['fecha_pago'] = $this->registro['fecha_pago'];
            $registro_periodo_bis['fecha_inicial_pago'] = $this->registro['fecha_inicial_pago'];
            $registro_periodo_bis['fecha_final_pago'] = $this->registro['fecha_final_pago'];
            $registro_periodo_bis['cat_sat_periodicidad_pago_nom_id'] = $tg_tipo_servicio['cat_sat_periodicidad_pago_nom_id'];
            $registro_periodo_bis['im_registro_patronal_id'] = $em_registro_patronal_bis->registros[0]['em_registro_patronal_id'];
            $registro_periodo_bis['em_registro_patronal_id'] = $em_registro_patronal_bis->registros[0]['em_registro_patronal_id'];
            $registro_periodo_bis['nom_tipo_periodo_id'] = 1;
            $registro_periodo_bis['cat_sat_tipo_nomina_id'] = $tg_tipo_servicio['cat_sat_tipo_nomina_id'];

            $r_nom_periodo_bis = (new nom_periodo($this->link))->alta_registro(registro: $registro_periodo_bis);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al dar de alta periodo',data:  $r_nom_periodo);
            }

            $registro_man['codigo'] =$this->registro['codigo'].'_bis';
            $registro_man['descripcion'] = $this->registro['descripcion'].'_bis';
            $registro_man['tg_manifiesto_id'] = $r_alta_bd->registro_id;
            $registro_man['nom_periodo_id'] = $r_nom_periodo_bis->registro_id;
            $r_tg_manifiesto_periodo = (new tg_manifiesto_periodo($this->link))->alta_registro(registro:$registro_man);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al dar de alta manifiesto_periodo',data:  $r_tg_manifiesto_periodo);
            }
        }

        return $r_alta_bd;
    }


    public function obten_sucursal_alianza(int $com_sucursal_id, int $tg_cte_alianza_id){
        $filtro['com_sucursal.id'] = $com_sucursal_id;
        $filtro['tg_cte_alianza.id'] = $tg_cte_alianza_id;
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
        $tg_sucursal_alianza = $this->obten_sucursal_alianza(com_sucursal_id: $registro['com_sucursal_id'],
            tg_cte_alianza_id: $registro['tg_cte_alianza_id']);
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