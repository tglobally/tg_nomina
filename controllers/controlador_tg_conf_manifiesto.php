<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace tglobally\tg_nomina\controllers;
use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;
use gamboamartin\system\links_menu;

use gamboamartin\template\html;
use html\tg_conf_manifiesto_html;
use PDO;
use stdClass;
use tglobally\tg_nomina\models\tg_conf_manifiesto;

class controlador_tg_conf_manifiesto extends _ctl_base {
    public array $sidebar = array();

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){

        $modelo = new tg_conf_manifiesto(link: $link);
        $html_ = new tg_conf_manifiesto_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id:$this->registro_id);

        $datatables = $this->init_datatable();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar datatable', data: $datatables);
            print_r($error);
            die('Error');
        }

        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $this->seccion_titulo = 'Conf. Manifiesto';
        $this->titulo_pagina = "Nominas - Conf. Manifiesto";
        $this->titulo_accion = "Conf. Manifiesto";

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }

        $this->lista_get_data = true;

    }

    public function alta(bool $header, bool $ws = false): array|string
    {

        $r_alta = $this->init_alta();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar alta', data: $r_alta, header: $header, ws: $ws);
        }

        $keys_selects = $this->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }

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
        $keys->inputs = array('id','codigo','descripcion');
        $keys->selects = array();
        $keys->fechas = array();

        $init_data = array();
        $init_data['fc_csd'] = "gamboamartin\\facturacion";
        $init_data['tg_agrupador'] = "tglobally\\tg_nomina";
        $init_data['nom_clasificacion'] = "gamboamartin\\nomina";

        $campos_view = $this->campos_view_base(init_data: $init_data, keys: $keys);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar campo view', data: $campos_view);
        }

        return $campos_view;
    }

    private function init_datatable(): stdClass
    {
        $columns["tg_conf_manifiesto_id"]["titulo"] = "Id";
        $columns["tg_conf_manifiesto_codigo"]["titulo"] = "Cod";
        $columns["tg_conf_manifiesto_descripcion"]["titulo"] = "Observaciones";

        $filtro = array("tg_conf_manifiesto.id", "tg_conf_manifiesto.codigo", "tg_conf_manifiesto.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;
        $datatables->menu_active = true;

        return $datatables;
    }

    private function init_selects(array $keys_selects, string $key, string $label, int $id_selected = -1, int $cols = 6,
                                  bool  $con_registros = true, array $filtro = array()): array
    {
        $keys_selects = $this->key_select(cols: $cols, con_registros: $con_registros, filtro: $filtro, key: $key,
            keys_selects: $keys_selects, id_selected: $id_selected, label: $label);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        return $keys_selects;
    }

    public function init_selects_inputs(): array{

        $keys_selects = $this->init_selects(keys_selects: array(), key: "fc_csd_id", label: "CSD",
            cols: 12);
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "tg_agrupador_id",
            label: "Agrupador", cols: 12);
        return $this->init_selects(keys_selects: $keys_selects, key: "nom_clasificacion_id",
            label: "Clasificacion", cols: 12);

    }

    protected function inputs_children(stdClass $registro): stdClass|array
    {
        $this->inputs = new stdClass();
        $this->inputs->select = new stdClass();
        return $this->inputs;
    }


    protected function key_selects_txt(array $keys_selects): array
    {

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 12,key: 'id', keys_selects:$keys_selects, place_holder: 'Id');
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'codigo', keys_selects: $keys_selects, place_holder: 'Cod');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6,key: 'descripcion', keys_selects:$keys_selects, place_holder: 'Observaciones');
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects);
        }

        return $keys_selects;
    }

    public function menu_item(string $menu_item_titulo, string $link, bool $menu_seccion_active = false, bool $menu_lateral_active = false): array
    {
        $menu_item = array();
        $menu_item['menu_item'] = $menu_item_titulo;
        $menu_item['menu_seccion_active'] = $menu_seccion_active;
        $menu_item['link'] = $link;
        $menu_item['menu_lateral_active'] = $menu_lateral_active;

        return $menu_item;
    }

    public function modifica(bool $header, bool $ws = false): array|stdClass
    {
        $r_modifica = $this->init_modifica();
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al generar salida de template', data: $r_modifica, header: $header, ws: $ws);
        }

        $keys_selects = $this->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }

        $keys_selects['fc_csd_id']->id_selected = $this->registro['tg_conf_manifiesto_fc_csd_id'];
        $keys_selects['tg_agrupador_id']->id_selected = $this->registro['tg_conf_manifiesto_tg_agrupador_id'];
        $keys_selects['nom_clasificacion_id']->id_selected = $this->registro['tg_conf_manifiesto_nom_clasificacion_id'];

        $base = $this->base_upd(keys_selects: $keys_selects, params: array(), params_ajustados: array());
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al integrar base', data: $base, header: $header, ws: $ws);
        }

        return $r_modifica;
    }



}
