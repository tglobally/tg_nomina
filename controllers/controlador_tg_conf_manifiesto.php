<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\tg_nomina\controllers;

use gamboamartin\nomina\html\nom_clasificacion_html;
use gamboamartin\tg_nomina\html\cob_cliente_html;
use gamboamartin\tg_nomina\html\cob_concepto_html;
use gamboamartin\tg_nomina\html\cob_deuda_html;
use gamboamartin\tg_nomina\html\cob_pago_html;
use gamboamartin\tg_nomina\models\cob_deuda;
use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;
use gamboamartin\system\links_menu;

use gamboamartin\template\html;

use html\bn_cuenta_html;
use html\fc_csd_html;
use html\tg_agrupador_html;
use PDO;
use stdClass;

class controlador_tg_conf_manifiesto extends _ctl_base {

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){

        $modelo = new tg_conf_manifiesto(link: $link);
        $html_ = new tg_conf_manifiesto_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id:$this->registro_id);


        $datatables = new stdClass();
        $datatables->columns = array();
        $datatables->columns['tg_conf_manifiesto_id']['titulo'] = 'Id';
        $datatables->columns['tg_conf_manifiesto_codigo']['titulo'] = 'Cod';
        $datatables->columns['tg_conf_manifiesto_descripcion']['titulo'] = 'Deuda';
        $datatables->columns['tg_conf_manifiesto_n_pagos']['titulo'] = 'N Pagos';

        $datatables->filtro = array();
        $datatables->filtro[] = 'tg_conf_manifiesto.id';
        $datatables->filtro[] = 'tg_conf_manifiesto.codigo';
        $datatables->filtro[] = 'tg_conf_manifiesto.descripcion';



        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link,
            datatables: $datatables, paths_conf: $paths_conf);

        $this->titulo_lista = 'Conf manifiesto';

        $this->lista_get_data = true;
    }


    public function alta(bool $header, bool $ws = false): array|string
    {

        $r_alta = $this->init_alta();
        if(errores::$error){
            return $this->retorno_error(
                mensaje: 'Error al inicializar alta',data:  $r_alta, header: $header,ws:  $ws);
        }

        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'tg_agrupador_id',
            keys_selects: array(), id_selected: -1, label: 'Agrupador');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }


        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'nom_clasificación_id',
            keys_selects: $keys_selects, id_selected: -1, label: 'Clasificacion');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }

        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'fc_csd_id',
            keys_selects: $keys_selects, id_selected: -1, label: 'CSD');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }


        $keys_selects['descripcion'] = new stdClass();
        $keys_selects['descripcion']->cols = 6;

        $keys_selects['fecha_vencimiento'] = new stdClass();
        $keys_selects['fecha_vencimiento']->cols = 6;


        $inputs = $this->inputs(keys_selects: $keys_selects);
        if(errores::$error){
            return $this->retorno_error(
                mensaje: 'Error al obtener inputs',data:  $inputs, header: $header,ws:  $ws);
        }



        return $r_alta;
    }

    protected function campos_view(): array
    {
        $keys = new stdClass();
        $keys->inputs = array('codigo','descripcion','monto');
        $keys->selects = array();
        $keys->fechas = array();

        $init_data = array();
        $init_data['tg_agrupador'] = "gamboamartin\\tg_nomina";
        $init_data['nom_clasificación'] = "gamboamartin\\nomina";
        $init_data['fc_csd'] = "gamboamartin\\facturacion";
        $campos_view = $this->campos_view_base(init_data: $init_data,keys:  $keys);

        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
        }


        return $campos_view;
    }

    protected function inputs_children(stdClass $registro): stdClass|array
    {
        $select_tg_agrupador_id = (new tg_agrupador_html(html: $this->html_base))->select_tg_agrupador_id(
            cols:6,con_registros: true,id_selected:  -1,link:  $this->link);

        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al obtener select_tg_agrupador_id',data:  $select_tg_agrupador_id);
        }
        $select_nom_clasificación_id = (new nom_clasificacion_html(html: $this->html_base))->select_nom_clasificacion_id(
            cols:12,con_registros: true,id_selected:  -1,link:  $this->link);

        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al obtener $elect_nom_clasificación_id',data:  $select_nom_clasificación_id);
        }

        $select_fc_csd_id = (new fc_csd_html(html: $this->html_base))->select_fc_csd_id(
            cols:6,con_registros: true,id_selected:  -1,link:  $this->link);

        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al obtener select_cob_concepto_id',data:  $select_fc_csd_id);
        }


        $this->inputs = new stdClass();
        $this->inputs->select = new stdClass();
        $this->inputs->select->tg_agrupador_id = $select_tg_agrupador_id;
        $this->inputs->select->nom_clasificación_id = $select_nom_clasificación_id;
        $this->inputs->select->fc_csd_id = $select_fc_csd_id;


        return $this->inputs;
    }


    protected function key_selects_txt(array $keys_selects): array
    {

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'codigo', keys_selects: $keys_selects, place_holder: 'Cod');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects);
        }
        return $keys_selects;
    }

    public function modifica(
        bool $header, bool $ws = false): array|stdClass
    {
        $r_modifica = $this->init_modifica(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(
                mensaje: 'Error al generar salida de template',data:  $r_modifica,header: $header,ws: $ws);
        }

        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'tg_agrupador_id',
            keys_selects: array(), id_selected: $this->registro['tg_agrupador_id'], label: 'Agrupador');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }

        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'nom_clasificación_id',
            keys_selects: $keys_selects, id_selected: $this->registro['nom_clasificación_id'], label: 'Clasificacion');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }

        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'fc_csd_id',
            keys_selects: $keys_selects, id_selected: $this->registro['fc_csd_id'], label: 'Facturacion');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }

        $keys_selects['descripcion'] = new stdClass();
        $keys_selects['descripcion']->cols = 6;

        $keys_selects['codigo'] = new stdClass();
        $keys_selects['codigo']->disabled = true;

        $base = $this->base_upd(keys_selects: $keys_selects, params: array(),params_ajustados: array());
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al integrar base',data:  $base, header: $header,ws:  $ws);
        }




        return $r_modifica;
    }

}
