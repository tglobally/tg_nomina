<?php

namespace tglobally\tg_nomina\controllers;

use DateTime;
use gamboamartin\errores\errores;
use gamboamartin\im_registro_patronal\models\im_conf_pres_empresa;
use gamboamartin\im_registro_patronal\models\im_salario_minimo;
use gamboamartin\im_registro_patronal\models\im_uma;
use gamboamartin\nomina\models\em_empleado;
use gamboamartin\nomina\models\nom_nomina;
use tglobally\template_tg\html;
use PDO;
use stdClass;
use tglobally\tg_nomina\models\tg_conf_provision;
use tglobally\tg_nomina\models\tg_provision;
use tglobally\tg_nomina\models\tg_tipo_provision;

class controlador_nom_conf_empleado extends \gamboamartin\nomina\controllers\controlador_nom_conf_empleado
{
    public function __construct(PDO $link, stdClass $paths_conf = new stdClass())
    {
        $html_base = new html();
        parent::__construct(link: $link, html: $html_base);

        $this->seccion_titulo = "Configuraciones Empleado";
        $this->titulo_pagina = "Nomina - Conf. Empleado";
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

    public function alta_bd(bool $header, bool $ws = false): array|stdClass
    {
        $_POST['descripcion_select'] = $_POST['descripcion'];

        return parent::alta_bd($header, $ws);
    }

    protected function init_datatable(): stdClass
    {
        $columns["nom_conf_empleado_id"]["titulo"] = "Id";
        $columns["em_empleado_nombre"]["titulo"] = "Empleado";
        $columns["em_empleado_nombre"]["campos"] = array("em_empleado_ap", "em_empleado_am");
        $columns["em_cuenta_bancaria_num_cuenta"]["titulo"] = "Num. Cuenta";
        $columns["nom_conf_nomina_descripcion"]["titulo"] = "Conf. Nomina";

        $filtro = array("nom_conf_empleado.id", "em_empleado.nombre", "em_empleado.ap", "em_empleado.am",
            "em_cuenta_bancaria.num_cuenta", "nom_conf_nomina.descripcion");

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

    public function modifica_bd(bool $header, bool $ws): array|stdClass
    {
        $_POST['descripcion_select'] = $_POST['descripcion'];

        return parent::modifica_bd($header, $ws);
    }

}