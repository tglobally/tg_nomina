<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */

namespace tglobally\tg_nomina\controllers;

use base\controller\controler;
use base\orm\inicializacion;
use base\orm\modelo;
use config\generales;
use DateTime;
use gamboamartin\cat_sat\models\cat_sat_isn;
use gamboamartin\comercial\models\com_email_cte;
use gamboamartin\comercial\models\com_sucursal;
use gamboamartin\direccion_postal\models\dp_calle_pertenece;
use gamboamartin\empleado\models\em_registro_patronal;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_factura;
use gamboamartin\facturacion\models\fc_receptor_email;
use gamboamartin\nomina\models\nom_nomina_documento;
use gamboamartin\notificaciones\mail\_mail;
use gamboamartin\notificaciones\models\not_emisor;
use gamboamartin\organigrama\models\org_departamento;
use html\tg_conf_comision_html;
use tglobally\tg_nomina\models\_email;
use gamboamartin\facturacion\models\fc_cfdi_sellado;
use gamboamartin\nomina\models\calcula_nomina;
use gamboamartin\nomina\models\em_empleado;
use gamboamartin\nomina\models\nom_conf_empleado;
use gamboamartin\nomina\models\nom_incidencia;
use gamboamartin\nomina\models\nom_par_deduccion;
use gamboamartin\nomina\models\nom_par_otro_pago;
use gamboamartin\nomina\models\nom_par_percepcion;
use gamboamartin\nomina\models\nom_percepcion;
use gamboamartin\nomina\models\nom_periodo;
use gamboamartin\plugins\exportador;
use gamboamartin\system\_ctl_base;
use gamboamartin\system\actions;
use gamboamartin\system\links_menu;
use gamboamartin\template\html;
use html\tg_manifiesto_html;
use gamboamartin\documento\models\doc_documento;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Exception;
use tglobally\tg_nomina\models\em_cuenta_bancaria;
use tglobally\tg_nomina\models\nom_nomina;
use tglobally\tg_nomina\models\tg_conf_comision;
use tglobally\tg_nomina\models\tg_manifiesto;
use tglobally\tg_nomina\models\tg_manifiesto_periodo;
use tglobally\tg_nomina\models\tg_provision;

use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use stdClass;
use ZipArchive;

class controlador_tg_conf_comision extends _ctl_base
{


    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new tg_conf_comision(link: $link);
        $html_ = new tg_conf_comision_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);

        $datatables = $this->init_datatable();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar datatable', data: $datatables);
            print_r($error);
            die('Error');
        }

        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }

        $configuraciones = $this->init_configuraciones();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar configuraciones', data: $configuraciones);
            print_r($error);
            die('Error');
        }
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta = $this->init_alta();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar alta', data: $r_alta, header: $header, ws: $ws);
        }

        $keys_selects = $this->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }
        $this->row_upd->fecha_inicio = date('Y-m-d');
        $this->row_upd->fecha_fin = date('Y-m-d');
        $this->row_upd->porcentaje = 0;

        $inputs = $this->inputs(keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al obtener inputs', data: $inputs, header: $header, ws: $ws);
        }

        return $r_alta;
    }

    private function init_configuraciones(): controler
    {
        $this->seccion_titulo = 'Configuracion Comision';
        $this->titulo_lista = 'Registro Conf. Comisiones';
        $this->titulo_pagina = "Nominas - Conf. Comisiones";
        $this->titulo_accion = "Conf. Comisiones";

        $this->lista_get_data = true;

        return $this;
    }

    protected function campos_view(): array
    {
        $keys = new stdClass();
        $keys->inputs = array('descripcion', 'porcentaje');
        $keys->fechas = array('fecha_inicio', 'fecha_fin');
        $keys->selects = array();

        $init_data = array();
        $init_data['com_sucursal'] = "gamboamartin\\comercial";

        $campos_view = $this->campos_view_base(init_data: $init_data, keys: $keys);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar campo view', data: $campos_view);
        }

        return $campos_view;
    }

    private function init_datatable(): stdClass
    {
        $columns["tg_conf_comision_id"]["titulo"] = "Id";
        $columns["com_sucursal_descripcion"]["titulo"] = "Cliente";
        $columns["tg_conf_comision_porcentaje"]["titulo"] = "Porcentaje";
        $columns["tg_conf_comision_fecha_inicio"]["titulo"] = "Fecha Inicio";
        $columns["tg_conf_comision_fecha_fin"]["titulo"] = "Fecha Fin";

        $filtro = array("tg_conf_comision.id", "com_sucursal.descripcion", "tg_conf_comision.porcentaje",
            "tg_conf_comision.fecha_inicio", "tg_conf_comision.fecha_fin");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;
        $datatables->menu_active = true;

        return $datatables;
    }

    /**
     * Integra los selects
     * @param array $keys_selects Key de selcta integrar
     * @param string $key key a validar
     * @param string $label Etiqueta a mostrar
     * @param int $id_selected selected
     * @param int $cols cols css
     * @param bool $con_registros Intrega valores
     * @param array $filtro Filtro de datos
     * @return array
     */
    private function init_selects(array $keys_selects, string $key, string $label, int $id_selected = -1, int $cols = 6,
                                  bool  $con_registros = true, array $filtro = array()): array
    {
        $keys_selects = $this->key_select(cols: $cols, con_registros: $con_registros, filtro: $filtro, key: $key,
            keys_selects: $keys_selects, id_selected: $id_selected, label: $label);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        return $keys_selects;
    }

    public function init_selects_inputs(): array
    {
        return $this->init_selects(keys_selects: array(), key: "com_sucursal_id", label: "Cliente", cols: 12);
    }

    protected function key_selects_txt(array $keys_selects): array
    {
        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 8, key: 'descripcion',
            keys_selects: $keys_selects, place_holder: 'Descripción');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'fecha_inicio',
            keys_selects: $keys_selects, place_holder: 'Fecha Inicio');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'fecha_fin',
            keys_selects: $keys_selects, place_holder: 'Fecha Fin');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 4, key: 'porcentaje',
            keys_selects: $keys_selects, place_holder: 'Porcentaje');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        return $keys_selects;
    }

    public function modifica(bool $header, bool $ws = false): array|stdClass
    {
        $r_modifica = $this->init_modifica();
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al generar salida de template', data: $r_modifica, header: $header, ws: $ws);
        }

        $keys_selects = $this->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }

        $keys_selects['com_sucursal_id']->id_selected = $this->registro['com_sucursal_id'];

        $base = $this->base_upd(keys_selects: $keys_selects, params: array(), params_ajustados: array());
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al integrar base', data: $base, header: $header, ws: $ws);
        }
        $this->titulo_accion = "Modifica Manifiesto";

        return $r_modifica;
    }
}
