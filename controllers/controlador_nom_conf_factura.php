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

        $this->seccion_titulo = "Configuraciones Factura";
        $this->titulo_pagina = "Nomina - Conf. Factura";
        $this->titulo_accion = "Listado de Configuraciones";

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $this->titulo_accion = "Alta Configuración";

        return parent::alta($header, $ws);
    }

    public function init_datatable(): stdClass
    {
        $columns["nom_conf_factura_id"]["titulo"] = "Id";
        $columns["cat_sat_forma_pago_descripcion"]["titulo"] = "Forma Pago";
        $columns["cat_sat_metodo_pago_descripcion"]["titulo"] = "Método Pago";
        $columns["cat_sat_moneda_descripcion"]["titulo"] = "Moneda";
        $columns["com_tipo_cambio_descripcion"]["titulo"] = "Tipo Cambio";
        $columns["cat_sat_uso_cfdi_descripcion"]["titulo"] = "Uso CFDI";
        $columns["cat_sat_tipo_de_comprobante_descripcion"]["titulo"] = "Tipo Comprobante";
        $columns["com_producto_descripcion"]["titulo"] = "Producto";

        $filtro = array("nom_conf_factura.id","cat_sat_forma_pago.descripcion",
            "cat_sat_metodo_pago.descripcion","cat_sat_moneda.descripcion","com_tipo_cambio.descripcion",
            "cat_sat_uso_cfdi.descripcion","cat_sat_tipo_de_comprobante.descripcion","com_producto.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;
        $datatables->menu_active = true;

        return $datatables;
    }

    public function modifica(bool $header, bool $ws = false): array|stdClass
    {
        $this->titulo_accion = "Modifica Configuración";
        return parent::modifica($header, $ws);
    }
}
