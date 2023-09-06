<?php

namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use PDO;
use stdClass;
use tglobally\template_tg\html;

class controlador_nom_conf_nomina extends \gamboamartin\nomina\controllers\controlador_nom_conf_nomina
{

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass())
    {
        $html_base = new html();
        parent::__construct(link: $link, html: $html_base);

        $this->seccion_titulo = "Configuraciones Nomina";
        $this->titulo_pagina = "Nomina - Conf. Nomina";
        $this->titulo_accion = "Listado de Configuraciones";

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }
    }

    public function init_datatable(): stdClass
    {
        $columns["nom_conf_nomina_id"]["titulo"] = "Id";
        $columns["nom_conf_nomina_descripcion"]["titulo"] = "Descripción ";
        $columns["cat_sat_periodicidad_pago_nom_descripcion"]["titulo"] = "Periodicidad Pago";
        $columns["cat_sat_tipo_nomina_descripcion"]["titulo"] = "Tipo Nomina";

        $filtro = array("nom_conf_nomina.id", "nom_conf_nomina.descripcion",
            "cat_sat_periodicidad_pago_nom.descripcion", "cat_sat_tipo_nomina.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;
        $datatables->menu_active = true;

        return $datatables;
    }
}
