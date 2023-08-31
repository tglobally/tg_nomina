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

class controlador_com_sucursal extends \tglobally\tg_cliente\controllers\controlador_com_sucursal
{

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass())
    {
        $html_base = new \tglobally\template_tg\html();
        parent::__construct(link: $link, paths_conf: $paths_conf);
        $this->titulo_lista = 'Cliente - Sucursal';
        $this->seccion_titulo = "Cliente - Sucursal";
        $this->titulo_accion = "Cliente - Sucursal";

        $this->datatables[0] ['columnDefs'][count($this->datatables[0]['columnDefs'])-1]->type = "menu";

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }
    }

}
