<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use PDO;
use stdClass;
use tglobally\template_tg\html;

class controlador_nom_clasificacion extends \gamboamartin\nomina\controllers\controlador_nom_clasificacion {

    public array $sidebar = array();

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){
        $html_base = new html();
        parent::__construct( link: $link, html: $html_base);
        $this->titulo_lista = 'Clasificación';

        $this->seccion_titulo = 'Clasificación';
        $this->titulo_pagina = "Nominas - Clasificación";
        $this->titulo_accion = "Clasificación";

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }

    }

    protected function init_datatable(): stdClass
    {
        $columns["nom_clasificacion_id"]["titulo"] = "Id";
        $columns["nom_clasificacion_codigo"]["titulo"] = "Cod";
        $columns["nom_clasificacion_descripcion"]["titulo"] = "Observaciones";

        $filtro = array("nom_clasificacion.id", "nom_clasificacion.codigo", "nom_clasificacion.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;
        $datatables->menu_active = true;

        return $datatables;
    }
}
