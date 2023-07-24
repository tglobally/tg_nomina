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
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\tg_manifiesto_periodo_html;
use tglobally\tg_nomina\models\tg_manifiesto_periodo;
use PDO;
use stdClass;

class controlador_tg_manifiesto_periodo extends system
{
    public array|stdClass $keys_selects = array();

    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new tg_manifiesto_periodo(link: $link);
        $html_ = new tg_manifiesto_periodo_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);

        $datatables = $this->init_datatable();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar datatable', data: $datatables);
            print_r($error);
            die('Error');
        }

        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $this->seccion_titulo = 'Periodos Manifiestos';
        $this->titulo_pagina = "Nominas - Periodos Manifiestos";
        $this->titulo_accion = "Periodos Manifiestos";

        $this->asignar_propiedad(identificador:'tg_manifiesto_id', propiedades: ["label" => "Manifiesto"]);
        $this->asignar_propiedad(identificador:'nom_periodo_id', propiedades: ["label" => "Periodo Nomina"]);

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta = parent::alta(header: false);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $r_alta, header: $header, ws: $ws);
        }

        $inputs = $this->genera_inputs(keys_selects: $this->keys_selects);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

    private function init_datatable(): stdClass
    {
        $columns["tg_manifiesto_periodo_id"]["titulo"] = "Id";
        $columns["tg_manifiesto_descripcion"]["titulo"] = "Manifiesto";
        $columns["nom_periodo_fecha_inicial_pago"]["titulo"] = "Fecha Inicial";
        $columns["nom_periodo_fecha_final_pago"]["titulo"] = "Fecha Final";

        $filtro = array("tg_manifiesto_periodo.id", "tg_manifiesto.descripcion", "nom_periodo.fecha_inicial_pago",
            "nom_periodo.fecha_final_pago");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;
        $datatables->menu_active = true;

        return $datatables;
    }

    public function asignar_propiedad(string $identificador, array $propiedades): array|stdClass
    {
        if (!array_key_exists($identificador, $this->keys_selects)) {
            $this->keys_selects[$identificador] = new stdClass();
        }

        foreach ($propiedades as $key => $value) {
            $this->keys_selects[$identificador]->$key = $value;
        }

        return $this->keys_selects;
    }

    private function base(): array|stdClass
    {
        $r_modifica =  parent::modifica(header: false,ws:  false);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
        }

        $this->asignar_propiedad(identificador:'tg_manifiesto_id',
            propiedades: ["id_selected"=>$this->row_upd->tg_manifiesto_id]);
        $this->asignar_propiedad(identificador:'nom_periodo_id',
            propiedades: ["id_selected"=>$this->row_upd->nom_periodo_id]);

        $inputs = $this->genera_inputs(keys_selects:  $this->keys_selects);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar inputs',data:  $inputs);
        }

        $data = new stdClass();
        $data->template = $r_modifica;
        $data->inputs = $inputs;

        return $data;
    }

    public function modifica(bool $header, bool $ws = false): array|stdClass
    {
        $base = $this->base();
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar datos',data:  $base,
                header: $header,ws:$ws);
        }

        return $base->template;
    }

}
