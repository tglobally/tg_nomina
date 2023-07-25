<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use PDO;
use stdClass;
use tglobally\template_tg\html;

class controlador_nom_conf_factura extends \gamboamartin\nomina\controllers\controlador_nom_conf_factura {

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){
        $html_base = new html();
        parent::__construct( link: $link, html: $html_base);

        $this->seccion_titulo = 'Conf. Factura';
        $this->titulo_pagina = "Nominas - Conf. Factura";
        $this->titulo_accion = "Conf. Factura";

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }
    }
}
