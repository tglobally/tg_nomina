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

class controlador_nom_rel_empleado_sucursal extends \gamboamartin\nomina\controllers\controlador_nom_rel_empleado_sucursal
{

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass())
    {
        $html_base = new \tglobally\template_tg\html();
        parent::__construct(link: $link, html: $html_base);

        $this->seccion_titulo = "Relación Cliente Empleado";
        $this->titulo_pagina = "Nomina - Relación Cliente Empleado";
        $this->titulo_accion = "Listado de Relaciones";

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $this->titulo_accion = "Alta Relación";

        return parent::alta($header, $ws);
    }

    public function alta_bd(bool $header, bool $ws = false): array|stdClass
    {
        $_POST['descripcion_select'] = $_POST['descripcion'];

        return parent::alta_bd($header, $ws);
    }

    public function init_datatable(): stdClass
    {
        $columns["nom_rel_empleado_sucursal_id"]["titulo"] = "Id";
        $columns["em_empleado_nombre"]["titulo"] = "Empleado";
        $columns["em_empleado_nombre"]["campos"] = array("em_empleado_ap", "em_empleado_am");
        $columns["com_sucursal_descripcion"]["titulo"] = "Cliente";

        $filtro = array("nom_rel_empleado_sucursal.id", "em_empleado.nombre", "em_empleado.ap", "em_empleado.am",
            "com_sucursal.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;
        $datatables->menu_active = true;

        return $datatables;
    }

    public function init_selects_inputs(): array
    {
        $keys_selects = $this->init_selects(keys_selects: array(), key: "em_empleado_id", label: "Empleado", cols: 12);
        return $this->init_selects(keys_selects: $keys_selects, key: "com_sucursal_id", label: "Cliente", cols: 12);
    }

    public function modifica(bool $header, bool $ws = false): array|stdClass
    {
        $this->titulo_accion = "Modifica Relación";
        return parent::modifica($header, $ws);
    }

    public function modifica_bd(bool $header, bool $ws): array|stdClass
    {
        $_POST['descripcion_select'] = $_POST['descripcion'];

        return parent::modifica_bd($header, $ws);
    }

}
