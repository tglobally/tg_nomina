<?php

namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use gamboamartin\nomina\models\em_empleado;
use tglobally\template_tg\html;
use PDO;
use stdClass;
use tglobally\tg_nomina\models\tg_provision;

class controlador_nom_conf_empleado extends \gamboamartin\nomina\controllers\controlador_nom_conf_empleado
{
    public string $link_nom_conf_empleado_integra_provision = '';
    public string $link_nom_conf_empleado_integra_provision_alta_bd = '';

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass())
    {
        $html_base = new html();
        parent::__construct(link: $link, html: $html_base);

        $init_links = $this->init_links();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar links', data: $init_links);
            print_r($error);
            die('Error');
        }

        $sidebar = $this->init_sidebar();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar sidebar', data: $sidebar);
            print_r($error);
            die('Error');
        }
    }

    protected function init_links(): array|string
    {
        $links = $this->obj_link->genera_links(controler: $this);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar links', data: $links);
            print_r($error);
            exit;
        }

        $link = $this->obj_link->get_link(seccion: "nom_conf_empleado", accion: "integra_provision");
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al recuperar link abono_alta_bd', data: $link);
            print_r($error);
            exit;
        }
        $this->link_nom_conf_empleado_integra_provision = $link;

        $link = $this->obj_link->get_link(seccion: "nom_conf_empleado", accion: "integra_provision_alta_bd");
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al recuperar link abono_alta_bd', data: $link);
            print_r($error);
            exit;
        }
        $this->link_nom_conf_empleado_integra_provision_alta_bd = $link;

        return $link;
    }

    private function init_sidebar(): stdClass|array
    {
        $menu_items = new stdClass();

        $menu_items->lista = $this->menu_item(menu_item_titulo: "Inicio", link: $this->link_lista);
        $menu_items->alta = $this->menu_item(menu_item_titulo: "Alta", link: $this->link_alta);
        $menu_items->modifica = $this->menu_item(menu_item_titulo: "Modifica", link: $this->link_modifica);
        $menu_items->provision = $this->menu_item(menu_item_titulo: "Integra Provision", link: $this->link_nom_conf_empleado_integra_provision);

        $menu_items->lista['menu_seccion_active'] = true;
        $menu_items->lista['menu_lateral_active'] = true;
        $menu_items->alta['menu_seccion_active'] = true;
        $menu_items->alta['menu_lateral_active'] = true;
        $menu_items->modifica['menu_lateral_active'] = true;
        $menu_items->provision['menu_seccion_active'] = true;
        $menu_items->provision['menu_lateral_active'] = true;

        $this->sidebar['lista']['titulo'] = "Conf. Empleados";
        $this->sidebar['lista']['menu'] = array($menu_items->alta);

        $this->sidebar['alta']['titulo'] = "Conf. Empleados";
        $this->sidebar['alta']['menu'] = array($menu_items->alta);

        $this->sidebar['modifica']['titulo'] = "Conf. Empleados";
        $this->sidebar['modifica']['menu'] = array($menu_items->modifica, $menu_items->provision);

        $this->sidebar['integra_provision']['titulo'] = "Empleados";
        $this->sidebar['integra_provision']['menu'] = array($menu_items->provision);

        return $menu_items;
    }

    public function integra_provision(bool $header, bool $ws = false): array|stdClass |string
    {
        $provision = new controlador_tg_provision($this->link);

        $r_template = $provision->init_alta();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener template', data: $r_template);
        }

        $keys_selects = $provision->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }

        $inputs = $provision->inputs(keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener inputs', data: $inputs);
        }

        $this->inputs = $inputs;

        return $r_template;
    }

    public function integra_provision_alta_bd(bool $header, bool $ws = false): array|stdClass |string
    {
        $registros_tg_provision['tg_tipo_provision_id'] = $_POST['tg_tipo_provision_id'];
        $registros_tg_provision['nom_nomina_id'] = $_POST['nom_nomina_id'];
        $registros_tg_provision['descripcion'] = $_POST['descripcion'];
        $registros_tg_provision['monto'] = $_POST['monto'];

        $this->link->beginTransaction();

        $tg_provision = (new tg_provision($this->link))->alta_registro(registro: $registros_tg_provision);
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al dar de alta provision', data: $tg_provision,header: $header,
                ws: $ws);
        }

        $this->link->commit();

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $tg_provision, siguiente_view: "integra_provision",
                ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($tg_provision, JSON_THROW_ON_ERROR);
            exit;
        }
        $tg_provision->siguiente_view = "integra_provision";

        return $tg_provision;
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
}