<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\tg_agrupador_html;
use tglobally\tg_nomina\models\tg_agrupador;
use PDO;
use stdClass;

class controlador_tg_agrupador extends system {

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new tg_agrupador(link: $link);
        $html_ = new tg_agrupador_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);

        $datatables = $this->init_datatable();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar datatable', data: $datatables);
            print_r($error);
            die('Error');
        }

        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $this->seccion_titulo = 'Agrupador';
        $this->titulo_pagina = "Nominas - Agrupador";
        $this->titulo_accion = "Agrupador";

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }
    }

    private function init_datatable(): stdClass
    {
        $columns["tg_agrupador_id"]["titulo"] = "Id";
        $columns["tg_agrupador_descripcion"]["titulo"] = "Agrupador";

        $filtro = array("tg__agrupador.id", "tg__agrupador.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;
        $datatables->menu_active = true;

        return $datatables;
    }

}
