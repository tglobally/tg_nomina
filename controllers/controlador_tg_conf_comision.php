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


    }

    protected function campos_view(): array
    {
        $keys = new stdClass();
        $keys->inputs = array('descripcion', 'importe_gravado', 'importe_exento', 'razón social ',
            'nss', 'curp', 'rfc', 'folio', 'salario_diario', 'salario_diario_integrado', 'subtotal',
            'descuento', 'total', 'num_dias_pagados');
        $keys->fechas = array('fecha_envio', 'fecha_pago', 'fecha_inicial_pago', 'fecha_final_pago', 'fecha', 'fecha_inicio_rel_laboral');
        $keys->selects = array();

        $init_data = array();
        $init_data['com_sucursal'] = "gamboamartin\\comercial";
        $init_data['tg_cte_alianza'] = "tglobally\\tg_nomina";
        $init_data['fc_csd'] = "gamboamartin\\facturacion";
        $init_data['tg_tipo_servicio'] = "tglobally\\tg_nomina";
        $init_data['nom_nomina'] = "gamboamartin\\nomina";
        $init_data['nom_percepcion'] = "gamboamartin\\nomina";
        $init_data['nom_deduccion'] = "gamboamartin\\nomina";
        $init_data['nom_otro_pago'] = "gamboamartin\\nomina";
        $init_data['org_empresa'] = "gamboamartin\\organigrama";
        $init_data['org_sucursal'] = "gamboamartin\\organigrama";
        $init_data['em_registro_patronal'] = "gamboamartin\\empleado";
        $init_data['nom_periodo'] = "gamboamartin\\nomina";
        $init_data['em_empleado'] = "gamboamartin\\empleado";
        $init_data['org_puesto'] = "gamboamartin\\organigrama";
        $init_data['nom_conf_empleado'] = "gamboamartin\\nomina";
        $init_data['em_cuenta_bancaria'] = "gamboamartin\\empleado";
        $init_data['cat_sat_tipo_nomina'] = "gamboamartin\\cat_sat";
        $init_data['cat_sat_periodicidad_pago_nom'] = "gamboamartin\\cat_sat";

        $campos_view = $this->campos_view_base(init_data: $init_data, keys: $keys);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar campo view', data: $campos_view);
        }

        return $campos_view;
    }



    private function init_datatable(): stdClass
    {
        $columns["tg_manifiesto_id"]["titulo"] = "Id";
        $columns["com_sucursal_descripcion"]["titulo"] = "Sucursal";
        $columns["tg_manifiesto_fecha_envio"]["titulo"] = "Fecha Envío";
        $columns["tg_manifiesto_fecha_pago"]["titulo"] = "Fecha Pago";
        $columns["tg_manifiesto_fecha_inicial_pago"]["titulo"] = "Fecha Incial Pago";
        $columns["tg_manifiesto_fecha_final_pago"]["titulo"] = "Fecha Final Pago";
        $columns["tg_manifiesto_n_nominas"]["titulo"] = "Nóminas ";

        $filtro = array("tg_manifiesto.id", "com_sucursal.descripcion", "tg_manifiesto.fecha_pago");

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
        $keys_selects = $this->init_selects(keys_selects: array(), key: "tg_cte_alianza_id", label: "Alianza");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "com_sucursal_id", label: "Cliente");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "fc_csd_id", label: "CSD", cols: 6);
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "org_sucursal_id", label: "Empresa", cols: 6);
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "nom_percepcion_id", label: "Percepción ", cols: 12);
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "nom_deduccion_id", label: "Deducción  ", cols: 12);
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "nom_otro_pago_id", label: "Otro Pago ", cols: 12);

        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "em_registro_patronal_id", label: "Registro Patronal");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "nom_periodo_id", label: "Periodo ");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "em_empleado_id", label: "Empleado ", cols: 12);
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "org_puesto_id", label: "Puesto ");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "nom_conf_empleado_id", label: "Conf Empleado ", cols: 12);
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "em_cuenta_bancaria_id", label: "Cuenta Bancaria ", cols: 12);
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "cat_sat_tipo_nomina_id", label: "Tipo nomina ");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "cat_sat_periodicidad_pago_nom_id", label: "Perioricidad pago ");

        return $this->init_selects(keys_selects: $keys_selects, key: "tg_tipo_servicio_id", label: "Tipo Servicio");
    }

    protected function key_selects_txt(array $keys_selects): array
    {
        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 12, key: 'descripcion',
            keys_selects: $keys_selects, place_holder: 'Descripción');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'fecha_envio',
            keys_selects: $keys_selects, place_holder: 'Fecha Envío');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'fecha_pago',
            keys_selects: $keys_selects, place_holder: 'Fecha Pago');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'fecha_inicial_pago',
            keys_selects: $keys_selects, place_holder: 'Fecha Incial Pago');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'fecha_final_pago',
            keys_selects: $keys_selects, place_holder: 'Fecha Final');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'importe_gravado',
            keys_selects: $keys_selects, place_holder: 'Importe Gravado');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'importe_exento',
            keys_selects: $keys_selects, place_holder: 'Importe Exento');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }


        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'rfc',
            keys_selects: $keys_selects, place_holder: 'Rfc');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'nss',
            keys_selects: $keys_selects, place_holder: 'Nss');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'curp',
            keys_selects: $keys_selects, place_holder: 'Curp');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'folio',
            keys_selects: $keys_selects, place_holder: 'Folio');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'fecha',
            keys_selects: $keys_selects, place_holder: 'Fecha');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'fecha_inicio_rel_laboral',
            keys_selects: $keys_selects, place_holder: 'Fecha inicio relacion laboral');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'num_dias_pagados',
            keys_selects: $keys_selects, place_holder: 'Nº dias pagados');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'salario_diario',
            keys_selects: $keys_selects, place_holder: 'Salario diario');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'salario_diario_integrado',
            keys_selects: $keys_selects, place_holder: 'Salario diario integrado');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'subtotal',
            keys_selects: $keys_selects, place_holder: 'Subtotal');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'descuento',
            keys_selects: $keys_selects, place_holder: 'Descuento');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'total',
            keys_selects: $keys_selects, place_holder: 'Total');
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

        $keys_selects['tg_tipo_servicio_id']->id_selected = $this->registro['tg_tipo_servicio_id'];
        $keys_selects['com_sucursal_id']->id_selected = $this->registro['com_sucursal_id'];
        $keys_selects['org_sucursal_id']->id_selected = $this->registro['org_sucursal_id'];
        $keys_selects['tg_cte_alianza_id']->id_selected = $this->registro['tg_cte_alianza_id'];

        $base = $this->base_upd(keys_selects: $keys_selects, params: array(), params_ajustados: array());
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al integrar base', data: $base, header: $header, ws: $ws);
        }
        $this->titulo_accion = "Modifica Manifiesto";

        return $r_modifica;
    }
}
