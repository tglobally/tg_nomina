<?php

namespace tglobally\tg_nomina\controllers;

use DateTime;
use gamboamartin\errores\errores;
use gamboamartin\im_registro_patronal\models\im_conf_pres_empresa;
use gamboamartin\im_registro_patronal\models\im_salario_minimo;
use gamboamartin\im_registro_patronal\models\im_uma;
use gamboamartin\nomina\models\em_empleado;
use gamboamartin\nomina\models\nom_conf_empleado;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\system\actions;
use tglobally\template_tg\html;
use PDO;
use stdClass;
use tglobally\tg_nomina\models\em_cuenta_bancaria;
use tglobally\tg_nomina\models\tg_conf_provision;
use tglobally\tg_nomina\models\tg_provision;
use tglobally\tg_nomina\models\tg_tipo_provision;

class controlador_nom_conf_empleado extends \gamboamartin\nomina\controllers\controlador_nom_conf_empleado
{
    public string $link_asigna_configuracion = '';
    public string $link_asigna_configuracion_bd = '';

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass())
    {
        $html_base = new html();
        parent::__construct(link: $link, html: $html_base);

        $this->seccion_titulo = "Configuraciones Empleado";
        $this->titulo_pagina = "Nomina - Conf. Empleado";
        $this->titulo_accion = "Listado de Configuraciones";

        $hd = "index.php?seccion=nom_conf_empleado&accion=asigna_configuracion&session_id=$this->session_id";
        $this->link_asigna_configuracion = $hd;

        $hd = "index.php?seccion=nom_conf_empleado&accion=asigna_configuracion_bd&session_id=$this->session_id";
        $this->link_asigna_configuracion_bd = $hd;

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta, "alta masiva" => $this->link_asigna_configuracion));
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

    public function asigna_configuracion(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        $this->titulo_accion = "Alta Masiva de Configuraciones";

        $r_alta = $this->init_alta();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar alta', data: $r_alta, header: $header, ws: $ws);
        }

        $keys_selects = $this->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }
        $keys_selects['em_empleado_id']->con_registros = false;

        $inputs = $this->inputs(keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al obtener inputs', data: $inputs, header: $header, ws: $ws);
        }

        return $r_alta;
    }

    public function asigna_configuracion_bd(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        $this->link->beginTransaction();

        $siguiente_view = $this->inicializa_transaccion();
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(
                mensaje: 'Error al inicializar', data: $siguiente_view, header: $header, ws: $ws);
        }

        foreach ($_POST['empleados'] as $empleado){

            $filtro['em_empleado.id'] = $empleado;
            $cuenta_bancaria = (new em_cuenta_bancaria($this->link))->filtro_and(filtro: $filtro);
            if (errores::$error) {
                $this->link->rollBack();
                return $this->retorno_error(
                    mensaje: 'Error al filtrar cuenta bancaria', data: $cuenta_bancaria, header: $header, ws: $ws);
            }

            if ($cuenta_bancaria->n_registros <= 0){
                $this->link->rollBack();
                $error = $this->errores->error(mensaje: "Error el empleado: $empleado no tiene una cuenta bancaria relacionada",
                    data: $cuenta_bancaria);
                print_r($error);
                die('Error');
            }

            $registro["em_empleado_id"] =  $empleado;
            $registro["em_cuenta_bancaria_id"] =  $cuenta_bancaria->registros[0]['em_cuenta_bancaria_id'];
            $registro["nom_conf_nomina_id"] =  $_POST["nom_conf_nomina_id"];
            $registro["descripcion"] =  $_POST["descripcion"];
            $registro["codigo"] =  $empleado.$registro["em_cuenta_bancaria_id"]." - ".(new nom_conf_empleado($this->link))->get_codigo_aleatorio();
            $alta = (new nom_conf_empleado($this->link))->alta_registro(registro: $registro);
            if (errores::$error) {
                $this->link->rollBack();
                return $this->retorno_error(
                    mensaje: 'Error al inicializar', data: $alta, header: $header, ws: $ws);
            }

        }

        $this->link->commit();
        $link = "./index.php?seccion=nom_conf_empleado&accion=lista&registro_id=" . $this->registro_id;
        $link .= "&session_id=$this->session_id";
        header('Location:' . $link);
        exit();
    }

    protected function campos_view(): array
    {
        $keys = new stdClass();
        $keys->inputs = array('codigo', 'descripcion');
        $keys->selects = array();

        $init_data = array();
        $init_data['em_empleado'] = "gamboamartin\\empleado";
        $init_data['em_cuenta_bancaria'] = "gamboamartin\\empleado";
        $init_data['nom_conf_nomina'] = "gamboamartin\\nomina";
        $init_data['com_sucursal'] = "gamboamartin\\comercial";

        $campos_view = $this->campos_view_base(init_data: $init_data, keys: $keys);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar campo view', data: $campos_view);
        }

        return $campos_view;
    }

    private function clean_post(): array
    {
        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }
        return $_POST;
    }

    private function inicializa_transaccion(): array|string
    {
        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view);
        }

        $limpia = $this->clean_post();
        if (errores::$error) {

            return $this->errores->error(mensaje: 'Error al limpiar post', data: $limpia);
        }

        return $siguiente_view;
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

    public function init_selects_inputs(): array
    {
        $keys_selects = parent::init_selects_inputs();
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "com_sucursal_id", label: "Cliente",
            cols: 6);
        return $keys_selects;
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