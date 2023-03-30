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

        $tg_tipo_provision = (new tg_tipo_provision($this->link))->registro(registro_id: $_POST['tg_tipo_provision_id']);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener tipo provision', data: $tg_tipo_provision,
                header: $header, ws: $ws);
        }

        $nom_nomina = (new nom_nomina($this->link))->registro(registro_id: $_POST['nom_nomina_id']);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener nomina', data: $nom_nomina,
                header: $header, ws: $ws);
        }

        $extra_join["im_detalle_conf_prestaciones"]['key'] = "im_conf_prestaciones_id";
        $extra_join["im_detalle_conf_prestaciones"]['enlace'] = "im_conf_pres_empresa";
        $extra_join["im_detalle_conf_prestaciones"]['key_enlace'] = "im_conf_prestaciones_id";
        $extra_join["im_detalle_conf_prestaciones"]['renombre'] = "im_detalle_conf_prestaciones";

        $filtro['org_empresa_id'] = $nom_nomina['org_empresa_id'];
        $conf_pres_empresa = (new im_conf_pres_empresa($this->link))->filtro_and(extra_join: $extra_join, filtro: $filtro,
            limit: 1);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener nomina', data: $conf_pres_empresa,
                header: $header, ws: $ws);
        }

        if ($conf_pres_empresa->n_registros <= 0) {
            return $this->retorno_error(mensaje: 'Error no existe una conf. im_conf_pres_empresa para la empresa',
                data: $conf_pres_empresa, header: $header, ws: $ws);
        }

        $monto = $_POST['monto'];

        if (strcasecmp($tg_tipo_provision['tg_tipo_provision_descripcion'], 'VACACIONES') == 0){
            $monto = $conf_pres_empresa->registros[0]['im_detalle_conf_prestaciones_n_dias_vacaciones'] * $nom_nomina['em_empleado_salario_diario'] / 365;
        } else if (strcasecmp($tg_tipo_provision['tg_tipo_provision_descripcion'], 'GRATIFICACIÓN ANUAL (AGUINALDO)') == 0){
            $monto = 999;
        }else if (strcasecmp($tg_tipo_provision['tg_tipo_provision_descripcion'], 'PRIMA VACACIONAL') == 0){
            $monto = $nom_nomina['em_empleado_salario_diario'] * $conf_pres_empresa->registros[0]['im_detalle_conf_prestaciones_n_dias_vacaciones'] * 0.25 / 365;
        }else if (strcasecmp($tg_tipo_provision['tg_tipo_provision_descripcion'], 'PRIMA DE ANTIGÜEDAD') == 0){

            $monto = $this->calcula_prima_antiguedad(fecha_inicio_rel_laboral: $nom_nomina['em_empleado_fecha_inicio_rel_laboral'],configuracion: true);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al calcular prima antiguedad', data: $monto,
                    header: $header, ws: $ws);
            }
        }

        $registros_tg_conf_provision['tg_tipo_provision_id'] = $_POST['tg_tipo_provision_id'];
        $registros_tg_conf_provision['nom_conf_empleado_id'] = $this->registro_id;
        $registros_tg_conf_provision['descripcion'] = $_POST['descripcion'];
        $registros_tg_conf_provision['fecha_inicio'] = date("y-m-d");
        $registros_tg_conf_provision['fecha_fin'] = date( "y-m-d", strtotime('last day of December', time()));
        $registros_tg_conf_provision['monto'] = $monto;

        $tg_conf_provision = (new tg_conf_provision($this->link))->alta_registro(registro: $registros_tg_conf_provision);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al dar de alta conf provision', data: $tg_conf_provision,
                header: $header, ws: $ws);
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

    public function calcula_anios_laborados(string $fecha_inicio_rel_laboral): int{
        $fechaInicio = new DateTime($fecha_inicio_rel_laboral);
        $fecha_actual = date("Y-m-d");
        $fechaFin = new DateTime(date($fecha_actual));
        $intervalo = $fechaInicio->diff($fechaFin);

        return $intervalo->y;
    }

    public function calcula_prima_antiguedad(string $fecha_inicio_rel_laboral, bool $configuracion): float|array{
        $years = $this->calcula_anios_laborados(fecha_inicio_rel_laboral: $fecha_inicio_rel_laboral);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al calcular años laborados', data: $years);
        }

        $fecha_actual = date("Y-m-d");

        $valor_calculo = 1.0;

        if ($configuracion){
            $filtro_especial[0][$fecha_inicio_rel_laboral]['operador'] = '>=';
            $filtro_especial[0][$fecha_inicio_rel_laboral]['valor'] = 'im_salario_minimo.fecha_inicio';
            $filtro_especial[0][$fecha_inicio_rel_laboral]['comparacion'] = 'AND';
            $filtro_especial[0][$fecha_inicio_rel_laboral]['valor_es_campo'] = true;

            $filtro_especial[1][$fecha_actual]['operador'] = '<=';
            $filtro_especial[1][$fecha_actual]['valor'] = 'im_salario_minimo.fecha_fin';
            $filtro_especial[1][$fecha_actual]['comparacion'] = 'AND';
            $filtro_especial[1][$fecha_actual]['valor_es_campo'] = true;
            $im_salario_minimo = (new im_salario_minimo($this->link))->filtro_and(filtro_especial:  $filtro_especial, limit: 1);
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al obtener salario minimo', data: $im_salario_minimo);
            }

            if ($im_salario_minimo->n_registros > 0) {
                $valor_calculo = $im_salario_minimo->registros[0]['im_salario_minimo_monto'];
            }
        }else {
            $filtro_especial[0][$fecha_inicio_rel_laboral]['operador'] = '>=';
            $filtro_especial[0][$fecha_inicio_rel_laboral]['valor'] = 'im_salario_minimo.fecha_inicio';
            $filtro_especial[0][$fecha_inicio_rel_laboral]['comparacion'] = 'AND';
            $filtro_especial[0][$fecha_inicio_rel_laboral]['valor_es_campo'] = true;

            $filtro_especial[1][$fecha_actual]['operador'] = '<=';
            $filtro_especial[1][$fecha_actual]['valor'] = 'im_salario_minimo.fecha_fin';
            $filtro_especial[1][$fecha_actual]['comparacion'] = 'AND';
            $filtro_especial[1][$fecha_actual]['valor_es_campo'] = true;
            $im_uma = (new im_uma($this->link))->filtro_and(filtro_especial:  $filtro_especial, limit: 1);
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al obtener uma', data: $im_uma);
            }

            if ($im_uma->n_registros > 0) {
                $valor_calculo = $im_uma->registros[0]['im_uma_monto'];
            }
        }

        $monto = $years * 12;
        $monto *= $valor_calculo * 2;
        $monto /= 365;

        return $monto;
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