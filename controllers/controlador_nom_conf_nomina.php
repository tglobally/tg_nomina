<?php

namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use PDO;
use stdClass;
use tglobally\template_tg\html;

class controlador_nom_conf_nomina extends \gamboamartin\nomina\controllers\controlador_nom_conf_nomina
{
    public array $estado_aplicaciones;

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

    public function alta(bool $header, bool $ws = false): array|string
    {
        $this->titulo_accion = "Alta Configuración";

        return parent::alta($header, $ws);
    }

    public function alta_bd(bool $header, bool $ws = false): array|stdClass
    {
        $aplicaciones = $this->maqueta_aplicaciones(datos: $_POST['aplicaciones']);

        unset($_POST['aplicaciones']);
        unset($_POST['btn_action_next']);

        $_POST = array_merge($_POST, $aplicaciones);
        $_POST['descripcion_select'] = $_POST['descripcion'];

        return parent::alta_bd($header, $ws);
    }

    public function init_datatable(): stdClass
    {
        $columns["nom_conf_nomina_id"]["titulo"] = "Id";
        $columns["nom_conf_nomina_descripcion"]["titulo"] = "Descripción ";
        $columns["nom_conf_factura_descripcion"]["titulo"] = "Conf. Factura";
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

    public function init_selects_inputs(): array
    {
        $keys_selects = $this->init_selects(keys_selects: array(), key: "nom_conf_factura_id", label: "Conf. Factura");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "cat_sat_periodicidad_pago_nom_id",
            label: "Periodicidad Pago");
        return $this->init_selects(keys_selects: $keys_selects, key: "cat_sat_tipo_nomina_id", label: "Tipo Nomina", cols: 12);
    }

    private function maqueta_aplicaciones(array $datos): array
    {
        $aplicaciones = array('aplica_septimo_dia', 'aplica_despensa', 'aplica_prima_dominical',
            'aplica_dia_festivo_laborado', 'aplica_dia_descanso', 'aplica_desgaste', 'aplica_nomina_pura');

        $salida = array();

        foreach ($aplicaciones as $aplicacion) {
            $salida[$aplicacion] = "inactivo";
            if (in_array($aplicacion, $datos)) {
                $salida[$aplicacion] = "activo";
            }
        }

        return $salida;
    }

    public function modifica(bool $header, bool $ws = false): array|stdClass
    {
        $this->titulo_accion = "Modifica Configuración";

        $registro = $this->modelo->registro(registro_id: $this->registro_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener registro', data: $registro, header: $header, ws: $ws);
        }

        $this->estado_aplicaciones = $registro;

        return parent::modifica($header, $ws);
    }

    public function modifica_bd(bool $header, bool $ws): array|stdClass
    {
        $aplicaciones = $this->maqueta_aplicaciones(datos: $_POST['aplicaciones']);

        unset($_POST['aplicaciones']);
        unset($_POST['btn_action_next']);

        $_POST = array_merge($_POST, $aplicaciones);
        $_POST['descripcion_select'] = $_POST['descripcion'];

        return parent::modifica_bd($header, $ws);
    }

}
