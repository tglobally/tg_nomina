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
use config\generales;
use gamboamartin\empleado\models\em_empleado;
use gamboamartin\empleado\models\em_registro_patronal;
use gamboamartin\errores\errores;
use gamboamartin\im_registro_patronal\models\im_registro_patronal;
use gamboamartin\nomina\models\calcula_nomina;
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
use IntlDateFormatter;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Exception;
use tglobally\tg_empleado\controllers\Reporte_Template;
use tglobally\tg_nomina\models\em_cuenta_bancaria;
use tglobally\tg_nomina\models\nom_nomina;
use tglobally\tg_nomina\models\tg_manifiesto;
use tglobally\tg_nomina\models\tg_manifiesto_periodo;
use tglobally\tg_nomina\models\tg_provision;

use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use stdClass;
use Throwable;
use ZipArchive;

class controlador_tg_manifiesto extends _ctl_base
{
    public controlador_tg_manifiesto_periodo $controlador_tg_manifiesto_periodo;

    public string $link_tg_manifiesto_periodo_alta_bd = '';
    public string $link_tg_manifiesto_ver_nominas = '';
    public string $link_tg_manifiesto_nominas = '';
    public string $link_tg_manifiesto_agregar_percepcion = '';
    public string $link_tg_manifiesto_agregar_percepcion_bd = '';
    public string $link_tg_manifiesto_agregar_deduccion = '';
    public string $link_tg_manifiesto_agregar_deduccion_bd = '';
    public string $link_tg_manifiesto_agregar_otro_pago = '';
    public string $link_tg_manifiesto_agregar_otro_pago_bd = '';
    public string $link_tg_manifiesto_elimina_percepciones = '';
    public string $link_tg_manifiesto_descarga_pdf = '';
    public string $link_tg_manifiesto_descarga_comprimido = '';

    public array $nominas_seleccionadas = array();

    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new tg_manifiesto(link: $link);
        $html_ = new tg_manifiesto_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);

        $datatables = $this->init_datatable();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar datatable', data: $datatables);
            print_r($error);
            die('Error');
        }

        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $configuraciones = $this->init_configuraciones();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar configuraciones', data: $configuraciones);
            print_r($error);
            die('Error');
        }

        $init_controladores = $this->init_controladores(paths_conf: $paths_conf);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar controladores', data: $init_controladores);
            print_r($error);
            die('Error');
        }

        $init_links = $this->init_links();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar links', data: $init_links);
            print_r($error);
            die('Error');
        }
    }

    public function agregar_deduccion(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        if (!isset($_POST['agregar_deduccion'])){
            return $this->retorno_error(mensaje: 'Error no existe agregar_deduccion', data: $_POST, header: $header,
                ws: $ws);
        }

        if ($_POST['agregar_deduccion'] === ""){
            return $this->retorno_error(mensaje: 'Error no ha seleccionado una nomina', data: $_POST, header: $header,
                ws: $ws);
        }

        $this->nominas_seleccionadas = explode(",",$_POST['agregar_deduccion']);

        $r_alta = $this->init_alta();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar alta', data: $r_alta, header: $header, ws: $ws);
        }

        $this->row_upd->importe_gravado = 0;
        $this->row_upd->importe_exento = 0;

        $keys_selects = $this->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }

        $inputs = $this->inputs(keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al obtener inputs', data: $inputs, header: $header, ws: $ws);
        }

        return $r_alta;
    }

    public function agregar_deduccion_bd(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        if (!isset($_POST['agregar_deduccion'])){
            return $this->retorno_error(mensaje: 'Error no existe agregar_deduccion', data: $_POST, header: $header,
                ws: $ws);
        }

        $this->nominas_seleccionadas = explode(",",$_POST['agregar_deduccion']);

        if (count($this->nominas_seleccionadas) === 0){
            return $this->retorno_error(mensaje: 'Error no ha seleccionado una nomina', data: $_POST, header: $header,
                ws: $ws);
        }

        foreach ($this->nominas_seleccionadas as $nomina){

            $registro['nom_nomina_id'] = $nomina;
            $registro['nom_deduccion_id'] = $_POST['nom_deduccion_id'];
            $registro['importe_gravado'] = $_POST['importe_gravado'];
            $registro['importe_exento'] = $_POST['importe_exento'];
            $registro['descripcion'] = $_POST['descripcion'];
            $resultado = (new nom_par_deduccion($this->link))->alta_registro(registro: $registro);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al ingresar deduccion para la nomina', data: $resultado,
                    header: $header, ws: $ws);
            }
        }

        header('Location:' . $this->link_tg_manifiesto_nominas);
        exit;
    }

    public function agregar_otro_pago(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        if (!isset($_POST['agregar_otro_pago'])){
            return $this->retorno_error(mensaje: 'Error no existe agregar_otro_pago', data: $_POST, header: $header,
                ws: $ws);
        }

        if ($_POST['agregar_otro_pago'] === ""){
            return $this->retorno_error(mensaje: 'Error no ha seleccionado una nomina', data: $_POST, header: $header,
                ws: $ws);
        }

        $this->nominas_seleccionadas = explode(",",$_POST['agregar_otro_pago']);

        $r_alta = $this->init_alta();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar alta', data: $r_alta, header: $header, ws: $ws);
        }

        $this->row_upd->importe_gravado = 0;
        $this->row_upd->importe_exento = 0;

        $keys_selects = $this->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }

        $inputs = $this->inputs(keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al obtener inputs', data: $inputs, header: $header, ws: $ws);
        }

        return $r_alta;
    }

    public function agregar_otro_pago_bd(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        if (!isset($_POST['agregar_otro_pago'])){
            return $this->retorno_error(mensaje: 'Error no existe agregar_otro_pago', data: $_POST, header: $header,
                ws: $ws);
        }

        $this->nominas_seleccionadas = explode(",",$_POST['agregar_otro_pago']);

        if (count($this->nominas_seleccionadas) === 0){
            return $this->retorno_error(mensaje: 'Error no ha seleccionado una nomina', data: $_POST, header: $header,
                ws: $ws);
        }

        foreach ($this->nominas_seleccionadas as $nomina){

            $registro['nom_nomina_id'] = $nomina;
            $registro['nom_otro_pago_id'] = $_POST['nom_otro_pago_id'];
            $registro['importe_gravado'] = $_POST['importe_gravado'];
            $registro['importe_exento'] = $_POST['importe_exento'];
            $registro['descripcion'] = $_POST['descripcion'];
            $resultado = (new nom_par_otro_pago($this->link))->alta_registro(registro: $registro);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al ingresar otro pago para la nomina', data: $resultado,
                    header: $header, ws: $ws);
            }
        }

        header('Location:' . $this->link_tg_manifiesto_nominas);
        exit;
    }

    public function agregar_percepcion(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        if (!isset($_POST['agregar_percepcion'])){
            return $this->retorno_error(mensaje: 'Error no existe agregar_percepcion', data: $_POST, header: $header,
                ws: $ws);
        }

        if ($_POST['agregar_percepcion'] === ""){
            return $this->retorno_error(mensaje: 'Error no ha seleccionado una nomina', data: $_POST, header: $header,
                ws: $ws);
        }

        $this->nominas_seleccionadas = explode(",",$_POST['agregar_percepcion']);

        $r_alta = $this->init_alta();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar alta', data: $r_alta, header: $header, ws: $ws);
        }

        $this->row_upd->importe_gravado = 0;
        $this->row_upd->importe_exento = 0;

        $keys_selects = $this->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }

        $inputs = $this->inputs(keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al obtener inputs', data: $inputs, header: $header, ws: $ws);
        }

        return $r_alta;
    }

    public function agregar_percepcion_bd(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        if (!isset($_POST['agregar_percepcion'])){
            return $this->retorno_error(mensaje: 'Error no existe agregar_percepcion', data: $_POST, header: $header,
                ws: $ws);
        }



        $this->nominas_seleccionadas = explode(",",$_POST['agregar_percepcion']);

        if (count($this->nominas_seleccionadas) === 0){
            return $this->retorno_error(mensaje: 'Error no ha seleccionado una nomina', data: $_POST, header: $header,
                ws: $ws);
        }

        foreach ($this->nominas_seleccionadas as $nomina){

            $registro['nom_nomina_id'] = $nomina;
            $registro['nom_percepcion_id'] = $_POST['nom_percepcion_id'];
            $registro['importe_gravado'] = $_POST['importe_gravado'];
            $registro['importe_exento'] = $_POST['importe_exento'];
            $registro['descripcion'] = $_POST['descripcion'];
            $resultado = (new nom_par_percepcion($this->link))->alta_registro(registro: $registro);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al ingresar percepcion para la nomina', data: $resultado,
                    header: $header, ws: $ws);
            }
        }

        header('Location:' . $this->link_tg_manifiesto_nominas);
        exit;
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
        $this->row_upd->fecha_envio = date('Y-m-d');
        $this->row_upd->fecha_pago = date('Y-m-d');
        $this->row_upd->fecha_inicial_pago = date('Y-m-d');
        $this->row_upd->fecha_final_pago = date('Y-m-d');

        $inputs = $this->inputs(keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al obtener inputs', data: $inputs, header: $header, ws: $ws);
        }

        return $r_alta;
    }

    protected function campos_view(): array
    {
        $keys = new stdClass();
        $keys->inputs = array('descripcion', 'importe_gravado', 'importe_exento','razón social ');
        $keys->fechas = array('fecha_envio', 'fecha_pago', 'fecha_inicial_pago', 'fecha_final_pago');
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

        $campos_view = $this->campos_view_base(init_data: $init_data, keys: $keys);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar campo view', data: $campos_view);
        }

        return $campos_view;
    }

    public function descarga_pdf(bool $header, bool $ws = false){
        if (!isset($_POST['descarga_pdf'])){
            return $this->retorno_error(mensaje: 'Error no existe descargar_pdf', data: $_POST, header: $header,
                ws: $ws);
        }

        if ($_POST['descarga_pdf'] === ""){
            return $this->retorno_error(mensaje: 'Error no ha seleccionado una nomina', data: $_POST, header: $header,
                ws: $ws);
        }

        $this->nominas_seleccionadas = explode(",",$_POST['descarga_pdf']);

        $temporales = (new generales())->path_base . "archivos/tmp/";
        $pdf = new Mpdf(['tempDir' => $temporales]);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al crear pdf',data: $pdf, header: $header, ws: $ws);
        }

        foreach ($this->nominas_seleccionadas as $nomina) {
            $r_pdf = (new nom_nomina($this->link))->crea_pdf_recibo_nomina(nom_nomina_id: $nomina, pdf: $pdf);
        }

        $nombre_archivo = "Nominas por periodo";
        $pdf->Output($nombre_archivo.'.pdf','D');

        exit;
    }

    public function descarga_comprimido(bool $header, bool $ws = false){
        if (!isset($_POST['descarga_comprimido'])){
            return $this->retorno_error(mensaje: 'Error no existe descargar_comprimido', data: $_POST, header: $header,
                ws: $ws);
        }

        if ($_POST['descarga_comprimido'] === ""){
            return $this->retorno_error(mensaje: 'Error no ha seleccionado una nomina', data: $_POST, header: $header,
                ws: $ws);
        }

        $id_nominas = array_map('intval', explode(',', $_POST['descarga_comprimido']));

        $zip = new ZipArchive();
        $nombreZip = 'Recibos por periodo.zip';
        $zip->open($nombreZip, ZipArchive::CREATE);

        foreach ($id_nominas as $nom_nomina_id){
            $temporales = (new generales())->path_base . "archivos/tmp/";
            $pdf = new Mpdf(['tempDir' => $temporales]);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al crear pdf', data: $pdf,
                    header: $header, ws: $ws);
            }

            $nom_nomina = (new nom_nomina($this->link))->registro(registro_id: $nom_nomina_id);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al obtener nomina', data: $nom_nomina,
                    header: $header, ws: $ws);
            }

            $r_pdf = (new nom_nomina($this->link))->crea_pdf_recibo_nomina(nom_nomina_id: $nom_nomina['nom_nomina_id'] ,pdf: $pdf);
            $archivo = $pdf->Output('','S');
            $zip->addFromString($nom_nomina['nom_nomina_descripcion'].'.pdf', $archivo);
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $nombreZip);
        header('Content-Length: ' . filesize($nombreZip));
        readfile($nombreZip);

        exit;
    }


    public function elimina_percepciones(bool $header, bool $ws = false): array|stdClass
    {
        if (!isset($_POST['percepciones_eliminar'])){
            return $this->retorno_error(mensaje: 'Error no existe percepciones_eliminar', data: $_POST, header: $header,
                ws: $ws);
        }

        $nominas_seleccionadas = explode(",",$_POST['percepciones_eliminar']);

        foreach ($nominas_seleccionadas as $nomina){
            $filtro["nom_nomina_id"] = $nomina;
            $resultado = (new nom_par_percepcion($this->link))->elimina_con_filtro_and(filtro: $filtro);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al eliminar percepcion de la nomina', data: $resultado,
                    header: $header, ws: $ws);
            }
        }

        $link = "./index.php?seccion=tg_manifiesto&accion=ve_nominas&registro_id=".$this->registro_id;
        $link.="&session_id=$this->session_id";
        header('Location:' . $link);
        exit;
    }



    private function init_configuraciones(): controler
    {
        $this->seccion_titulo = 'Manifiestos';
        $this->titulo_lista = 'Registro de Manifiestos';

        $this->lista_get_data = true;

        return $this;
    }

    private function init_controladores(stdClass $paths_conf): controler
    {
        $this->controlador_tg_manifiesto_periodo = new controlador_tg_manifiesto_periodo(link: $this->link,
            paths_conf: $paths_conf);

        return $this;
    }

    private function init_links(): array|string
    {
        $this->link_tg_manifiesto_periodo_alta_bd = $this->obj_link->link_alta_bd(link: $this->link,
            seccion: 'tg_manifiesto_periodo');
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_periodo_alta_bd);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_ver_nominas = $this->obj_link->link_con_id(accion: "ver_nominas",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_ver_nominas);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_nominas = $this->obj_link->link_con_id(accion: "nominas",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_nominas);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_agregar_percepcion = $this->obj_link->link_con_id(accion: "agregar_percepcion",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_agregar_percepcion);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_agregar_percepcion_bd = $this->obj_link->link_con_id(accion: "agregar_percepcion_bd",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_agregar_percepcion_bd);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_agregar_deduccion = $this->obj_link->link_con_id(accion: "agregar_deduccion",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_agregar_deduccion);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_agregar_deduccion_bd = $this->obj_link->link_con_id(accion: "agregar_deduccion_bd",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_agregar_deduccion_bd);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_agregar_otro_pago = $this->obj_link->link_con_id(accion: "agregar_otro_pago",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_agregar_otro_pago);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_agregar_otro_pago_bd = $this->obj_link->link_con_id(accion: "agregar_otro_pago_bd",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_agregar_otro_pago_bd);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_elimina_percepciones = $this->obj_link->link_con_id(accion: "elimina_percepciones",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_elimina_percepciones);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_descarga_pdf = $this->obj_link->link_con_id(accion: "descarga_pdf",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_descarga_pdf);
            print_r($error);
            exit;
        }

        $this->link_tg_manifiesto_descarga_comprimido = $this->obj_link->link_con_id(accion: "descarga_comprimido",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_tg_manifiesto_descarga_comprimido);
            print_r($error);
            exit;
        }

        return $this->link_tg_manifiesto_periodo_alta_bd;
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

        $filtro = array("tg_manifiesto.id","com_sucursal.descripcion","tg_manifiesto.fecha_envio",
            "tg_manifiesto.fecha_pago", "tg_manifiesto.fecha_inicial_pago","tg_manifiesto.fecha_final_pago");

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
     * @param int $id_selected  selected
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

        return $r_modifica;
    }






    public stdClass $periodos;
    public int $tg_manifiesto_periodo_id = -1;
    public array $nominas = array();



    private function data_nomina_btn(array $nomina): array
    {
        $btn_elimina = $this->html_base->button_href(accion: 'elimina_nomina_bd', etiqueta: 'Elimina',
            registro_id: $nomina['nom_nomina_id'], seccion: 'tg_manifiesto', style: 'danger');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_elimina);
        }
        $nomina['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion: 'modifica', etiqueta: 'Modifica',
            registro_id: $nomina['nom_nomina_id'], seccion: 'nom_nomina', style: 'warning');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_modifica);
        }
        $nomina['link_modifica'] = $btn_modifica;

        return $nomina;
    }

    private function data_periodo_btn(array $periodo): array
    {
        $params['tg_manifiesto_periodo_id'] = $periodo['tg_manifiesto_periodo_id'];

        $btn_elimina = $this->html_base->button_href(accion: 'periodo_elimina_bd', etiqueta: 'Elimina',
            registro_id: $this->registro_id, seccion: 'tg_manifiesto', style: 'danger',params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_elimina);
        }
        $periodo['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion: 'periodo_modifica', etiqueta: 'Modifica',
            registro_id: $this->registro_id, seccion: 'tg_manifiesto', style: 'warning',params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_modifica);
        }
        $periodo['link_modifica'] = $btn_modifica;

        return $periodo;
    }

    public function descarga_nomina(bool $header, bool $ws = false): array|stdClass
    {
        $manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener manifiesto',data:  $manifiesto,
                header: $header,ws:$ws);
        }

        $nominas = (new tg_manifiesto_periodo($this->link))->nominas_by_manifiesto(tg_manifiesto_id: $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener nominas del periodo',data:  $nominas,
                header: $header,ws:$ws);
        }

        $conceptos = (new nom_nomina($this->link))->obten_conceptos_nominas(nominas: $nominas);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener nominas del periodo',data:  $conceptos,
                header: $header,ws:$ws);
        }

        $exportador = (new exportador_eliminar(num_hojas: 3));
        $registros_xls = array();
        $registros_provisiones = array();

        foreach ($nominas as $nomina){
            $row = (new nom_nomina($this->link))->maqueta_registros_excel(nom_nomina_id: $nomina['nom_nomina_id'],
                conceptos_nomina: $conceptos);
            if(errores::$error){
                return $this->retorno_error(mensaje: 'Error al maquetar datos de la nomina',data:  $row,
                    header: $header,ws:$ws);
            }

            $provisiones = (new tg_provision($this->link))->maqueta_excel_provisiones(
                nom_nomina_id: $nomina['nom_nomina_id']);
            if(errores::$error){
                return $this->retorno_error(mensaje: 'Error al maquetar provisiones de la nomina',data:  $provisiones,
                    header: $header,ws:$ws);
            }

            $pagos = (new em_cuenta_bancaria($this->link))->maqueta_excel_pagos(data_general: $row);
            if(errores::$error){
                return $this->retorno_error(mensaje: 'Error al maquetar pagos de la nomina',data:  $pagos,
                    header: $header,ws:$ws);
            }

            $registros_xls[] = $row;
            $registros_provisiones[] = $provisiones;
            $registros_pagos[] = $pagos;
        }

        $keys = array();
        $keys_provisiones = array();
        $keys_pagos = array();

        foreach (array_keys($registros_xls[0]) as $key) {
            $keys[$key] = strtoupper(str_replace('_', ' ', $key));
        }

        foreach (array_keys($registros_provisiones[0]) as $key) {
            $keys_provisiones[$key] = strtoupper(str_replace('_', ' ', $key));
        }

        foreach (array_keys($registros_pagos[0]) as $key) {
            $keys_pagos[$key] = strtoupper(str_replace('_', ' ', $key));
        }

        $registros = array();
        $registros_provisiones_excel = array();
        $registros_pagos_excel = array();

        foreach ($registros_xls as $row) {
            $registros[] = array_combine(preg_replace(array_map(function($s){return "/^$s$/";},
                array_keys($keys)),$keys, array_keys($row)), $row);
        }

        foreach ($registros_provisiones as $row) {
            $registros_provisiones_excel[] = array_combine(preg_replace(array_map(function($s){return "/^$s$/";},
                array_keys($keys_provisiones)),$keys_provisiones, array_keys($row)), $row);
        }

        foreach ($registros_pagos as $row) {
            $registros_pagos_excel[] = array_combine(preg_replace(array_map(function($s){return "/^$s$/";},
                array_keys($keys_pagos)),$keys_pagos, array_keys($row)), $row);
        }

        $keys_hojas =  array();
        $keys_hojas['nominas'] = new stdClass();
        $keys_hojas['nominas']->keys = $keys;
        $keys_hojas['nominas']->registros = $registros;
        $keys_hojas['provisionado'] = new stdClass();
        $keys_hojas['provisionado']->keys = $keys_provisiones;
        $keys_hojas['provisionado']->registros = $registros_provisiones_excel;
        $keys_hojas['pagos'] = new stdClass();
        $keys_hojas['pagos']->keys = $keys_pagos;
        $keys_hojas['pagos']->registros = $registros_pagos_excel;

        $xls = $exportador->genera_xls(header: $header,name: $manifiesto["tg_manifiesto_descripcion"],
            nombre_hojas: array("nominas", "provisionado", "pagos"), keys_hojas: $keys_hojas,
            path_base: $this->path_base);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar xls',data:  $xls, header: $header,
                ws:$ws);
        }

        /* $resultado = $exportador->listado_base_xls(header: $header, name: $this->seccion, keys:  $keys,
             path_base: $this->path_base,registros:  $registros,totales:  array());
         if(errores::$error){
             $error =  $this->errores->error('Error al generar xls',$resultado);
             if(!$header){
                 return $error;
             }
             print_r($error);
             die('Error');
         }*/


        exit;
        //return $this->nominas;
    }
    /*public function descarga_nomina(bool $header, bool $ws = false): array|stdClass
    {
        $manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener manifiesto',data:  $manifiesto,
                header: $header,ws:$ws);
        }

        $nominas = (new tg_manifiesto_periodo($this->link))->nominas_by_manifiesto(tg_manifiesto_id: $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener nominas del periodo',data:  $nominas,
                header: $header,ws:$ws);
        }

        $conceptos = (new nom_nomina($this->link))->obten_conceptos_nominas(nominas: $nominas);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener nominas del periodo',data:  $conceptos,
                header: $header,ws:$ws);
        }

        $registros_tabla_1 = array();
        $registros_tabla_2 = array();

        foreach ($nominas as $nomina) {

            $registro_tabla_1 = [$nomina['em_empleado_id'], $nomina['em_empleado_nss'], $nomina['em_empleado_nombre_completo']];

            $row = (new nom_nomina($this->link))->maqueta_registros_excel(nom_nomina_id: $nomina['nom_nomina_id'],
                conceptos_nomina: $conceptos);
            if(errores::$error){
                return $this->retorno_error(mensaje: 'Error al maquetar datos de la nomina',data:  $row,
                    header: $header,ws:$ws);
            }


            $registro_tabla_2 = [
                $nomina['cat_sat_periodicidad_pago_nom_n_dias'],
                $nomina['em_empleado_fecha_inicio_rel_laboral'],
                $nomina['em_empleado_fecha_inicio_rel_laboral'],
                $nomina['em_empleado_fecha_inicio_rel_laboral'],
                $nomina['dp_estado_descripcion'],
                $nomina['em_empleado_salario_diario'],
                $nomina['em_empleado_salario_diario'],
                $nomina['em_empleado_salario_diario_integrado'],
                $nomina['cat_sat_periodicidad_pago_nom_n_dias'] * $nomina['em_empleado_salario_diario'],
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                "-",
                $row['infonavit'],
                "-",
                "-",
                "-",
                "-",
                "-",
                '-'
            ];
            $registros_tabla_1[] = $registro_tabla_1;
            $registros_tabla_2[] = $registro_tabla_2;
        }

        $totales = $this->suma_totales(registros: $nominas, campo_sumar: array('em_empleado_salario_diario_integrado'));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener totales', data: $totales);
            print_r($error);
            die('Error');
        }

        $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        $fecha_inicio = $formatter->format(strtotime($manifiesto['tg_manifiesto_fecha_inicial_pago']));
        $fecha_final = $formatter->format(strtotime($manifiesto['tg_manifiesto_fecha_final_pago']));

        $periodo = "$fecha_inicio - $fecha_final";

        $tabla_1['detalles'] = [
            ["titulo" => 'EMPRESA:', 'valor' => $manifiesto['org_sucursal_descripcion']],
            ["titulo" => 'CLIENTE:', 'valor' => $manifiesto['com_sucursal_descripcion']],
            ["titulo" => 'PERIODO:', 'valor' => $periodo]
        ];
        $tabla_1['headers'] = ['ID REM', 'NSS', 'NOMBRE COMPLETO'];
        $tabla_1['data'] = $registros_tabla_1;
        $tabla_1['startRow'] = 4;
        $tabla_1['startColumn'] = "A";

        $tabla_2['detalles'] = [
            ["titulo" => 'FOLIO:', 'valor' => $manifiesto['tg_manifiesto_codigo']],
            ["titulo" => 'FECHA EMISION:', 'valor' => date("Y-m-d")],
            ["titulo" => ' ', 'valor' => " "]
        ];
        $tabla_2['headers'] = ['DIAS LABORADOS', 'FECHA INGRESO', 'FECHA ANTIGÜEDAD',
            'REGISTRO PATRONAL', 'UBICACIÓN', 'SD', 'FI', 'SDI', 'SUELDO', 'SUBSIDIO', 'COMPENSACIÓN', 'DEVOLUCION INFONAVIT',
            'PRESTACION DE LEY', ' PROVISIONAL', 'PRIMA DOMINICAL', 'PRESTACION DE LEY', ' PROVISIONALES', 'GRATIFICACION',
            'DESTAJO', 'PRESTACION DE LEY', ' PROVISIONAL', 'SEPTIMO DIA', 'DIA FESTIVO', 'GRAVADO', 'EXENTO', 'GRAVADAS',
            'EXENTAS', 'SUMA PERCEPCION', 'BASE GRAVABLE', 'RETENCION ISR', 'RETENCION IMSS', 'INFONAVIT', 'FONACOT',
            'PENSION ALIMENTICIA', 'OTROS DESCUENTOS', 'DESCUENTO COMEDOR', 'SUMA DEDUCCION', 'NETO A PAGAR'];
        $tabla_2['data'] = $registros_tabla_2;
        $tabla_2['startRow'] = 4;
        $tabla_2['startColumn'] = "D";
        $tabla_2['totales'] = [
            ["columna" => 'L', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'M', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'N', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'O', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'P', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'Q', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'R', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'S', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'T', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'U', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'V', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'W', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'X', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'Y', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'Z', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AA', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AB', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AC', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AD', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AE', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AF', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AG', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AH', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AI', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AJ', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AK', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AL', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AM', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AN', 'valor' => $totales->em_empleado_salario_diario_integrado],
            ["columna" => 'AO', 'valor' => $totales->em_empleado_salario_diario_integrado]
        ];



        $data["RAYA IMSS"] = [$tabla_1, $tabla_2];

        $name = $manifiesto['org_sucursal_descripcion']."_REPORTE MANIFIESTO";

        $resultado = (new exportador())->exportar_template(header: $header, path_base: $this->path_base, name: $name,
            data: $data, styles: \tglobally\tg_nomina\controllers\Reporte_Template::REPORTE_GENERAL);
        if (errores::$error) {
            $error = $this->errores->error('Error al generar xls', $resultado);
            if (!$header) {
                return $error;
            }
            print_r($error);
            die('Error');
        }

        header('Location:' . $this->link_lista);
        exit;
    }*/

    public function descarga_recibo_manifiesto(bool $header, bool $ws = false){
        $filtro['tg_manifiesto_periodo.tg_manifiesto_id'] = $this->registro_id;
        $manifiesto_periodo = (new tg_manifiesto_periodo($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener nominas', data: $manifiesto_periodo);
        }

        $nom_periodo_id = $manifiesto_periodo->registros[0]['nom_periodo_id']; /** Id del periodo */

        $filtro_nomina['nom_nomina.nom_periodo_id'] = $nom_periodo_id;
        $nominas = (new nom_nomina($this->link))->filtro_and(filtro: $filtro_nomina);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener nominas', data: $nominas);
        }

        $r_nomina = (new nom_nomina($this->link))->descarga_recibo_nomina_foreach(nom_nominas: $nominas);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener recibo de nomina', data: $r_nomina);
            print_r($error);
            die('Error');
        }
        exit;
    }

    public function descarga_recibo_manifiesto_zip(bool $header, bool $ws = false){
        $filtro['tg_manifiesto_periodo.tg_manifiesto_id'] = $this->registro_id;
        $manifiesto_periodo = (new tg_manifiesto_periodo($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener nominas', data: $manifiesto_periodo);
        }

        $nom_periodo_id = $manifiesto_periodo->registros[0]['nom_periodo_id']; /** Id del periodo */

        $filtro_nomina['nom_nomina.nom_periodo_id'] = $nom_periodo_id;
        $nominas = (new nom_nomina($this->link))->filtro_and(filtro: $filtro_nomina);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener nominas', data: $nominas);
        }

        $r_nomina = (new nom_nomina($this->link))->descarga_recibo_nomina_zip(nom_nominas: $nominas);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener recibo de nomina', data: $r_nomina);
            print_r($error);
            die('Error');
        }
        exit;
    }

    private function suma_totales(array $registros, array $campo_sumar): stdClass
    {
        $totales = new stdClass();

        foreach ($campo_sumar as $campo) {
            $totales->$campo = 0.0;
        }

        foreach ($registros as $registro) {
            foreach ($campo_sumar as $campo) {
                $valor = $registro[$campo];
                $totales->$campo += $valor;
            }
        }

        return $totales;
    }

    /**
     * @throws \JsonException
     */
    public function lee_archivo(bool $header, bool $ws = false)
    {
        $tg_manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $this->registro_id);
        if (errores::$error) {
            $error =  $this->errores->error(mensaje: 'Error al obtener manifiesto', data: $tg_manifiesto);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $doc_documento_modelo = new doc_documento($this->link);
        $doc_documento_modelo->registro['descripcion'] = $tg_manifiesto['tg_manifiesto_descripcion'];
        $doc_documento_modelo->registro['descripcion_select'] = $tg_manifiesto['tg_manifiesto_descripcion'];
        $doc_documento_modelo->registro['doc_tipo_documento_id'] = 1;
        $doc_documento = $doc_documento_modelo->alta_bd(file: $_FILES['archivo']);
        if (errores::$error) {
            $error =  $this->errores->error(mensaje: 'Error al dar de alta el documento', data: $doc_documento);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $empleados_excel = $this->obten_empleados_excel(ruta_absoluta: $doc_documento->registro['doc_documento_ruta_absoluta']);
        if (errores::$error) {
            $error =  $this->errores->error(mensaje: 'Error obtener empleados',data:  $empleados_excel);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $em_registro_patronal = $this->obten_registro_patronal(tg_manifiesto_id: $this->registro_id);
        if (errores::$error) {
            $error =  $this->errores->error(mensaje: 'Error obtener registro patronal',data:  $em_registro_patronal);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $em_registro_patronal_id = $em_registro_patronal['em_registro_patronal_id'];
        $empleados = array();
        foreach ($empleados_excel as $empleado_excel){
            $filtro['em_registro_patronal.id'] = $em_registro_patronal_id;
            $filtro['em_empleado.nombre'] = $empleado_excel->nombre;
            $filtro['em_empleado.ap'] = $empleado_excel->ap;
            $filtro['em_empleado.am'] = $empleado_excel->am;

            $registro = (new em_empleado($this->link))->filtro_and(filtro: $filtro);
            if (errores::$error) {
                $error =  $this->errores->error(mensaje: 'Error al al obtener registro de empleado', data: $registro);
                if(!$header){
                    return $error;
                }
                print_r($error);
                die('Error');
            }
            if ($registro->n_registros === 0) {
                $error =  $this->errores->error(mensaje: 'Error se encontro el empleado '.$empleado_excel->nombre.' '.
                    $empleado_excel->ap.' '.$empleado_excel->am, data: $registro);
                if(!$header){
                    return $error;
                }
                print_r($error);
                die('Error');
            }
            if($registro->n_registros > 0){
                $empleados[] = $registro->registros[0];
            }
        }

        $filtro_per['tg_manifiesto.id'] = $this->registro_id;
        $nom_periodos = (new tg_manifiesto_periodo($this->link))->filtro_and(filtro: $filtro_per);
        if (errores::$error) {
            $error =  $this->errores->error(mensaje: 'Error al al obtener periodos', data: $nom_periodos);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        foreach ($nom_periodos->registros  as $nom_periodo) {
            $empleados_res = array();
            foreach ($empleados as $empleado) {
                $filtro_em['em_empleado.id'] = $empleado['em_empleado_id'];
                $filtro_em['nom_conf_nomina.id'] = $tg_manifiesto['nom_conf_nomina_id'];
                $conf_empleado = (new nom_conf_empleado($this->link))->filtro_and(filtro: $filtro_em);
                if (errores::$error) {
                    $error =  $this->errores->error(mensaje: 'Error al obtener configuracion de empleado',
                        data: $conf_empleado);
                    if(!$header){
                        return $error;
                    }
                    print_r($error);
                    die('Error');
                }

                if (isset($conf_empleado->registros[0])) {
                    $empleados_res[] = $conf_empleado->registros[0];
                }
            }

            foreach ($empleados_res as $empleado) {
                foreach ($empleados_excel as $empleado_excel) {
                    if(trim($empleado_excel->nombre) === trim($empleado['em_empleado_nombre']) &&
                        trim($empleado_excel->ap) === trim($empleado['em_empleado_ap']) &&
                        trim($empleado_excel->am) === trim($empleado['em_empleado_am'])) {

                        if((float)$empleado_excel->monto_sueldo > 0) {

                            $dias_asistidos = $empleado_excel->monto_sueldo / $empleado['em_empleado_salario_diario'];

                            $dias_restantes = $empleado['cat_sat_periodicidad_pago_nom_n_dias'];
                            if($empleado['nom_conf_nomina_aplica_septimo_dia'] === 'activo'){
                                $res = $empleado['cat_sat_periodicidad_pago_nom_n_dias'] / 7;
                                $dias_restantes -= round($res);
                            }

                            $dias_faltas = $dias_restantes - $dias_asistidos;

                            $registro_inc['nom_tipo_incidencia_id'] = 1;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $dias_faltas;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }

                        if ((int)$empleado_excel->faltas > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 1;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->faltas;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                        if ((int)$empleado_excel->prima_dominical > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 2;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->prima_dominical;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                        if ((int)$empleado_excel->dias_festivos_laborados > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 3;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->dias_festivos_laborados;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                        if ((int)$empleado_excel->incapacidades > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 4;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->incapacidades;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                        if ((int)$empleado_excel->vacaciones > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 5;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->vacaciones;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                        if ((int)$empleado_excel->dias_descanso_laborado > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 6;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->dias_descanso_laborado;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                    }
                }

                $alta_empleado = (new nom_periodo($this->link))->alta_empleado_periodo(empleado: $empleado,
                    nom_periodo: $nom_periodo);
                if (errores::$error) {
                    $error =  $this->errores->error(mensaje: 'Error al dar de alta la nomina del empleado',
                        data: $alta_empleado);
                    if(!$header){
                        return $error;
                    }
                    print_r($error);
                    die('Error');
                }

                foreach ($empleados_excel as $empleado_excel) {
                    if (trim($empleado_excel->nombre) === trim($empleado['em_empleado_nombre']) &&
                        trim($empleado_excel->ap) === trim($empleado['em_empleado_ap']) &&
                        trim($empleado_excel->am) === trim($empleado['em_empleado_am'])) {
                        if ($empleado_excel->compensacion > 0) {
                            $nom_percepcion = (new nom_percepcion($this->link))->get_aplica_compensacion();
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error insertar conceptos', data: $nom_percepcion);
                            }

                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = $nom_percepcion['nom_percepcion_id'];
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->compensacion;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }

                        if ($empleado_excel->prima_vacacional > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 12;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->prima_vacacional;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }

                        if ($empleado_excel->despensa > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 4;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->despensa;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->horas_extras_dobles > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 13;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->horas_extras_dobles;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->horas_extras_triples > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 19;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->horas_extras_triples;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->gratificacion_especial > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 14;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->gratificacion_especial;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->premio_puntualidad > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 15;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->premio_puntualidad;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->premio_asistencia > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 16;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->premio_asistencia;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->ayuda_transporte > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 17;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->ayuda_transporte;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->gratificacion > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 18;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->gratificacion;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->seguro_vida > 0) {
                            $nom_par_deduccion_sep = array();
                            $nom_par_deduccion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_deduccion_sep['nom_deduccion_id'] = 5;
                            $nom_par_deduccion_sep['importe_gravado'] = $empleado_excel->seguro_vida;

                            $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $nom_par_deduccion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar deduccion default', data: $r_alta_nom_par_deduccion);
                            }
                        }
                        if ($empleado_excel->descuentos > 0) {
                            $nom_par_deduccion_sep = array();
                            $nom_par_deduccion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_deduccion_sep['nom_deduccion_id'] = 6;
                            $nom_par_deduccion_sep['importe_gravado'] = $empleado_excel->descuentos;

                            $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $nom_par_deduccion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar deduccion default', data: $r_alta_nom_par_deduccion);
                            }
                        }

                        if ($empleado_excel->monto_neto > 0) {

                            $nom_nomina = (new nom_nomina($this->link))->registro(registro_id: $alta_empleado->registro_id);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error obtener nomina',
                                    data: $nom_nomina);
                            }

                            $cat_sat_periodicidad_pago_nom_id = $empleado['cat_sat_periodicidad_pago_nom_id'];
                            $em_salario_diario = $empleado['em_empleado_salario_diario'];
                            $em_empleado_salario_diario_integrado = $empleado['em_empleado_salario_diario_integrado'];
                            $nom_nomina_fecha_final_pago = $nom_periodo['nom_periodo_fecha_final_pago'];
                            $nom_nomina_num_dias_pagados = $alta_empleado->registro['cat_sat_periodicidad_pago_nom_n_dias'];
                            $total_gravado = $empleado_excel->monto_neto;
                            $resultado = (new calcula_nomina())->nomina_neto(
                                cat_sat_periodicidad_pago_nom_id: $cat_sat_periodicidad_pago_nom_id,
                                em_salario_diario: $em_salario_diario,
                                em_empleado_salario_diario_integrado: $em_empleado_salario_diario_integrado,
                                link: $this->link, nom_nomina_fecha_final_pago: $nom_nomina_fecha_final_pago,
                                nom_nomina_num_dias_pagados: $nom_nomina_num_dias_pagados,
                                total_neto: $total_gravado);

                            $resultado_calculado = $resultado - $nom_nomina['nom_nomina_total_percepcion_gravado'];


                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 10;
                            $nom_par_percepcion_sep['importe_gravado'] = $resultado_calculado;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }

                        }
                    }
                }

            }
        }

        $link = "./index.php?seccion=tg_manifiesto&accion=lista&registro_id=".$this->registro_id;
        $link.="&session_id=$this->session_id";
        header('Location:' . $link);
        exit;
    }



    public function obten_columna_faltas(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'FALTAS') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_prima_dominical(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'DÍAS DE PRIMA DOMINICAL') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_dias_festivos_laborados(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'FESTIVO LABORADO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_dias_descanso_laborado(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'DESCANSO LABORADO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_compensacion(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'COMPENSACION') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }
    public function obten_columna_monto_neto(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'MONTO NETO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_monto_sueldo(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'MONTO SUELDO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_despensa(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'DESPENSA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_horas_extras_dobles(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'HORAS EXTRAS DOBLES') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }
    public function obten_columna_horas_extras_triples(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'HORAS EXTRAS TRIPLES') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_gratificacion_especial(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'GRATIFICACION ESPECIAL') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_premio_puntualidad(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'PREMIO PUNTUALIDAD') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_premio_asistencia(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'PREMIO ASISTENCIA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_ayuda_transporte(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'AYUDA TRANSPORTE') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_gratificacion(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'GRATIFICACION') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_seguro_vida(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'SEGURO DE VIDA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_descuentos(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'DESCUENTOS') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_incapacidades(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'INCAPACIDAD') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_vacaciones(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'DÍAS DE VACACIONES') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_prima_vacacional(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'PRIMA VACACIONAL') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     */
    public function obten_empleados_excel(string $ruta_absoluta){
        $documento = IOFactory::load($ruta_absoluta);
        $totalDeHojas = $documento->getSheetCount();
/*
        $tg_layout_id = (new tg_layout(link: $this->link))->obten_tg_layout_id(layout: 'manifiesto_nomina');
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener tg_layout_id',data:  $tg_layout_id);
        }

        $filtro_colums['tg_layout.id'] = $tg_layout_id;
        $tg_columnas = (new tg_column(link: $this->link))->filtro_and(filtro: $filtro_colums);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error no existe configuracion layout',data:  $tg_columnas);
        }

        $ubicacion_columnas = array();
        $fila_inicio = 1;
        foreach ($tg_columnas->registros as $columna){
            $columna_base = $columna['tg_column_descripcion'];
            $columna_cal = $columna['tg_column_alias'].'.'.$columna_base;

            for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
                $hojaActual = $documento->getSheet($indiceHoja);
                foreach ($hojaActual->getRowIterator() as $fila) {
                    foreach ($fila->getCellIterator() as $celda) {
                        $valorRaw = $celda->getValue();
                        if($valorRaw === $columna_base || $valorRaw === $columna_cal) {
                            $ubicacion_columnas[$columna_cal] = $celda->getColumn();
                            $fila_inicio = $celda->getRow();
                        }
                    }
                }
            }
        }

        $keys = array('IDTR.ID Trabajador','NOMB.Nombre','APAT.Paterno','AMAT.Materno');

        $filas = array();
        foreach ($ubicacion_columnas as $columna => $valor){
            foreach ($keys as $key){
                $fila_init = $fila_inicio;
                if($columna === $key){
                    $salida = false;
                    while(!$salida){
                        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
                            $fila_init++;

                            $hojaActual = $documento->getSheet($indiceHoja);
                            $coordenadas = $valor.$fila_init;
                            $celda = $hojaActual->getCell($coordenadas);

                            $valor_celda = (string)$celda->getCalculatedValue();
                            if($valor_celda !== ''){
                                $filas[] = $fila_init;
                            }else{
                                $salida = true;
                            }
                        }
                    }
                }
            }
        }

        $filas_exist = array_unique($filas);

        $prefijos =  array('DIA.','P.','D.','OP.','M.');
        $empleados = array();
        foreach ($filas_exist as $fila_exist){
            $reg = array();
            foreach ($ubicacion_columnas as $columna => $valor){
                for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
                    $hojaActual = $documento->getSheet($indiceHoja);
                    $reg[$columna] = $hojaActual->getCell($valor.$fila_exist)->getCalculatedValue();

                    foreach ($prefijos as $prefijo){
                        if (stristr($columna, $prefijo)) {
                            $reg[$columna] = trim((string)$reg[$columna]);
                            if(!is_numeric($reg[$columna]) || $reg[$columna] === ''){
                                $reg[$columna] = 0;
                            }
                        }
                    }
                }
            }
            $empleados[] = $reg;
        }*/

        $columna_faltas = $this->obten_columna_faltas(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de faltas',data:  $columna_faltas);
        }

        $columna_prima_dominical = $this->obten_columna_prima_dominical(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de prima dominical',
                data:  $columna_prima_dominical);
        }

        $columna_dias_festivos_laborados = $this->obten_columna_dias_festivos_laborados(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de dias festivos laborados',
                data:  $columna_dias_festivos_laborados);
        }

        $columna_incapacidades = $this->obten_columna_incapacidades(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de incapacidades',
                data:  $columna_incapacidades);
        }

        $columna_vacaciones = $this->obten_columna_vacaciones(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de vacaciones',
                data:  $columna_vacaciones);
        }

        $columna_dias_descanso_laborado = $this->obten_columna_dias_descanso_laborado(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de vacaciones',
                data:  $columna_dias_descanso_laborado);
        }

        $columna_compensacion = $this->obten_columna_compensacion(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de compensacion',
                data:  $columna_compensacion);
        }

        $columna_monto_neto = $this->obten_columna_monto_neto(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de monto neto',
                data:  $columna_monto_neto);
        }

        $columna_monto_sueldo = $this->obten_columna_monto_sueldo(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de monto neto',
                data:  $columna_monto_sueldo);
        }

        $columna_prima_vacacional = $this->obten_columna_prima_vacacional(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de prima vacacional',
                data:  $columna_prima_vacacional);
        }

        $columna_despensa = $this->obten_columna_despensa(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de despensa',
                data:  $columna_despensa);
        }

        $columna_horas_extras_dobles = $this->obten_columna_horas_extras_dobles(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de horas_extras_dobles',
                data:  $columna_horas_extras_dobles);
        }

        $columna_horas_extras_triples = $this->obten_columna_horas_extras_triples(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de horas_extras_triples',
                data:  $columna_horas_extras_triples);
        }

        $columna_gratificacion_especial = $this->obten_columna_gratificacion_especial(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de gratificacion_especial',
                data:  $columna_gratificacion_especial);
        }

        $columna_premio_puntualidad = $this->obten_columna_premio_puntualidad(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de premio puntualidad',
                data:  $columna_premio_puntualidad);
        }

        $columna_premio_asistencia = $this->obten_columna_premio_asistencia(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de premio_asistencia',
                data:  $columna_premio_asistencia);
        }

        $columna_ayuda_transporte = $this->obten_columna_ayuda_transporte(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de ayuda transporte',
                data:  $columna_ayuda_transporte);
        }

        $columna_gratificacion = $this->obten_columna_gratificacion(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de gratificacion',
                data:  $columna_gratificacion);
        }

        $columna_seguro_vida = $this->obten_columna_seguro_vida(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de seguro_vida',
                data:  $columna_seguro_vida);
        }

        $columna_descuentos = $this->obten_columna_descuentos(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de descuentos',
                data:  $columna_descuentos);
        }

        $empleados = array();
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            $registros = array();
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $fila = $celda->getRow();
                    $valorRaw = $celda->getValue();
                    $columna = $celda->getColumn();

                    if ($fila >= 7) {
                        if ($columna === "A" && is_numeric($valorRaw)) {
                            $reg = new stdClass();
                            $reg->fila = $fila;
                            $registros[] = $reg;
                        }
                    }
                }
            }

            foreach ($registros as $registro) {
                $reg = new stdClass();
                $reg->codigo = $hojaActual->getCell('A' . $registro->fila)->getValue();
                $reg->nombre = $hojaActual->getCell('B' . $registro->fila)->getValue();
                $reg->ap = $hojaActual->getCell('C' . $registro->fila)->getValue();
                $reg->am = $hojaActual->getCell('D' . $registro->fila)->getValue();
                $reg->faltas = 0;
                $reg->prima_dominical = 0;
                $reg->dias_festivos_laborados = 0;
                $reg->incapacidades = 0;
                $reg->vacaciones = 0;
                $reg->dias_descanso_laborado = 0;
                $reg->compensacion = 0;

                $reg->prima_vacacional = 0;
                $reg->despensa = 0;
                $reg->seguro_vida = 0;
                $reg->descuentos = 0;
                $reg->horas_extras_dobles = 0;
                $reg->horas_extras_triples = 0;
                $reg->gratificacion_especial = 0;
                $reg->premio_puntualidad = 0;
                $reg->premio_asistencia = 0;
                $reg->ayuda_transporte = 0;
                $reg->gratificacion = 0;
                $reg->monto_neto = 0;
                $reg->monto_sueldo = 0;

                if ($columna_faltas !== -1) {
                    $reg->faltas = $hojaActual->getCell($columna_faltas . $registro->fila)->getValue();
                    if (!is_numeric($reg->faltas)) {
                        $reg->faltas = 0;
                    }
                }

                if ($columna_prima_dominical !== -1) {
                    $reg->prima_dominical = $hojaActual->getCell($columna_prima_dominical . $registro->fila)->getValue();
                    if (!is_numeric($reg->prima_dominical)) {
                        $reg->prima_dominical = 0;
                    }
                }

                if ($columna_dias_festivos_laborados !== -1) {
                    $reg->dias_festivos_laborados = $hojaActual->getCell($columna_dias_festivos_laborados . $registro->fila)->getValue();
                    if (!is_numeric($reg->dias_festivos_laborados)) {
                        $reg->dias_festivos_laborados = 0;
                    }
                }

                if ($columna_incapacidades !== -1) {
                    $reg->incapacidades = $hojaActual->getCell($columna_incapacidades . $registro->fila)->getValue();
                    if (!is_numeric($reg->incapacidades)) {
                        $reg->incapacidades = 0;
                    }
                }

                if ($columna_vacaciones !== -1) {
                    $reg->vacaciones = $hojaActual->getCell($columna_vacaciones . $registro->fila)->getValue();
                    if (!is_numeric($reg->vacaciones)) {
                        $reg->vacaciones = 0;
                    }
                }
                if ($columna_dias_descanso_laborado !== -1) {
                    $reg->dias_descanso_laborado = $hojaActual->getCell($columna_dias_descanso_laborado . $registro->fila)->getValue();
                    if (!is_numeric($reg->dias_descanso_laborado)) {
                        $reg->dias_descanso_laborado = 0;
                    }
                }
                if ($columna_compensacion !== -1) {
                    $compensacion = $hojaActual->getCell($columna_compensacion . $registro->fila)->getCalculatedValue();
                    $reg->compensacion = trim((string)$compensacion);

                    if (!is_numeric($reg->compensacion)) {
                        $reg->compensacion = 0;
                    }
                }
                if ($columna_monto_neto !== -1) {
                    $monto_neto = $hojaActual->getCell($columna_monto_neto . $registro->fila)->getCalculatedValue();
                    $reg->monto_neto = trim((string)$monto_neto);

                    if (!is_numeric($reg->monto_neto)) {
                        $reg->monto_neto = 0;
                    }
                }

                if ($columna_monto_sueldo !== -1) {
                    $monto_sueldo = $hojaActual->getCell($columna_monto_sueldo . $registro->fila)->getCalculatedValue();
                    $reg->monto_sueldo = trim((string)$monto_sueldo);

                    if (!is_numeric($reg->monto_sueldo)) {
                        $reg->monto_sueldo = 0;
                    }
                }

                if ($columna_prima_vacacional !== -1) {
                    $prima_vacacional = $hojaActual->getCell($columna_prima_vacacional . $registro->fila)->getCalculatedValue();
                    $reg->prima_vacacional = trim((string)$prima_vacacional);

                    if (!is_numeric($reg->prima_vacacional)) {
                        $reg->prima_vacacional = 0;
                    }
                }
                if ($columna_despensa !== -1) {
                    $despensa = $hojaActual->getCell($columna_despensa . $registro->fila)->getCalculatedValue();
                    $reg->despensa = trim((string)$despensa);

                    if (!is_numeric($reg->despensa)) {
                        $reg->despensa = 0;
                    }
                }
                if ($columna_seguro_vida !== -1) {
                    $seguro_vida = $hojaActual->getCell($columna_seguro_vida . $registro->fila)->getCalculatedValue();
                    $reg->seguro_vida = trim((string)$seguro_vida);

                    if (!is_numeric($reg->seguro_vida)) {
                        $reg->seguro_vida = 0;
                    }
                }
                if ($columna_descuentos !== -1) {
                    $descuentos = $hojaActual->getCell($columna_descuentos . $registro->fila)->getCalculatedValue();
                    $reg->descuentos = trim((string)$descuentos);

                    if (!is_numeric($reg->descuentos)) {
                        $reg->descuentos = 0;
                    }
                }
                if ($columna_horas_extras_dobles !== -1) {
                    $horas_extras_dobles = $hojaActual->getCell($columna_horas_extras_dobles . $registro->fila)->getCalculatedValue();
                    $reg->horas_extras_dobles = trim((string)$horas_extras_dobles);

                    if (!is_numeric($reg->horas_extras_dobles)) {
                        $reg->horas_extras_dobles = 0;
                    }
                }
                if ($columna_horas_extras_triples !== -1) {
                    $horas_extras_triples = $hojaActual->getCell($columna_horas_extras_triples . $registro->fila)->getCalculatedValue();
                    $reg->horas_extras_triples = trim((string)$horas_extras_triples);

                    if (!is_numeric($reg->horas_extras_triples)) {
                        $reg->horas_extras_triples = 0;
                    }
                }
                if ($columna_gratificacion_especial !== -1) {
                    $gratificacion_especial = $hojaActual->getCell($columna_gratificacion_especial . $registro->fila)->getCalculatedValue();
                    $reg->gratificacion_especial = trim((string)$gratificacion_especial);

                    if (!is_numeric($reg->gratificacion_especial)) {
                        $reg->gratificacion_especial = 0;
                    }
                }
                if ($columna_premio_puntualidad !== -1) {
                    $premio_puntualidad = $hojaActual->getCell($columna_premio_puntualidad . $registro->fila)->getCalculatedValue();
                    $reg->premio_puntualidad = trim((string)$premio_puntualidad);

                    if (!is_numeric($reg->premio_puntualidad)) {
                        $reg->premio_puntualidad = 0;
                    }
                }
                if ($columna_premio_asistencia !== -1) {
                    $premio_asistencia = $hojaActual->getCell($columna_premio_asistencia . $registro->fila)->getCalculatedValue();
                    $reg->premio_asistencia = trim((string)$premio_asistencia);

                    if (!is_numeric($reg->premio_asistencia)) {
                        $reg->premio_asistencia = 0;
                    }
                }
                if ($columna_ayuda_transporte !== -1) {
                    $ayuda_transporte = $hojaActual->getCell($columna_ayuda_transporte . $registro->fila)->getCalculatedValue();
                    $reg->ayuda_transporte = trim((string)$ayuda_transporte);

                    if (!is_numeric($reg->ayuda_transporte)) {
                        $reg->ayuda_transporte = 0;
                    }
                }
                if ($columna_gratificacion !== -1) {
                    $gratificacion = $hojaActual->getCell($columna_gratificacion . $registro->fila)->getCalculatedValue();
                    $reg->gratificacion = trim((string)$gratificacion);

                    if (!is_numeric($reg->ayuda_transporte)) {
                        $reg->gratificacion = 0;
                    }
                }
                $empleados[] = $reg;
            }
        }

        return $empleados;
    }

    public function obten_registro_patronal(int $tg_manifiesto_id){
        $tg_manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $tg_manifiesto_id);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener manifiesto',data:  $tg_manifiesto);
        }

        $filtro_org['fc_csd.id'] = $tg_manifiesto['tg_manifiesto_fc_csd_id'];
        $im_registro_patronal = (new em_registro_patronal($this->link))->filtro_and(filtro: $filtro_org);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener registro patronal',data:  $im_registro_patronal);
        }

        return $im_registro_patronal->registros[0];
    }

    public function periodo(bool $header, bool $ws = false): array|stdClass
    {
        $alta = $this->controlador_tg_manifiesto_periodo->alta(header: false);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $alta, header: $header, ws: $ws);
        }

        $this->controlador_tg_manifiesto_periodo->asignar_propiedad(identificador: 'tg_manifiesto_id',
            propiedades: ["id_selected" => $this->registro_id, "disabled" => true,
                "filtro" => array('tg_manifiesto.id' => $this->registro_id)]);

        $this->inputs = $this->controlador_tg_manifiesto_periodo->genera_inputs(
            keys_selects:  $this->controlador_tg_manifiesto_periodo->keys_selects);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $this->inputs);
            print_r($error);
            die('Error');
        }

        $periodos = (new tg_manifiesto_periodo($this->link))->get_periodos_manifiesto(tg_manifiesto_id: $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener periodos',data:  $periodos,header: $header,ws:$ws);
        }

        foreach ($periodos->registros as $indice => $periodo) {
            $periodo = $this->data_periodo_btn(periodo: $periodo);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al asignar botones', data: $periodo, header: $header, ws: $ws);
            }
            $periodos->registros[$indice] = $periodo;
        }

        $this->periodos = $periodos;

        return $this->inputs;
    }

    public function periodo_alta_bd(bool $header, bool $ws = false): array|stdClass
    {
        $this->link->beginTransaction();

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }
        $_POST['tg_manifiesto_id'] = $this->registro_id;

        $alta = (new tg_manifiesto_periodo($this->link))->alta_registro(registro: $_POST);
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al dar de alta periodo', data: $alta,
                header: $header, ws: $ws);
        }

        $this->link->commit();

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $alta,
                siguiente_view: "periodo", ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($alta, JSON_THROW_ON_ERROR);
            exit;
        }
        $alta->siguiente_view = "periodo";

        return $alta;
    }

    public function periodo_modifica(bool $header, bool $ws = false): array|stdClass
    {
        $this->controlador_tg_manifiesto_periodo->registro_id = $this->tg_manifiesto_periodo_id;

        $modifica = $this->controlador_tg_manifiesto_periodo->modifica(header: false);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar template',data:  $modifica, header: $header,ws:$ws);
        }

        $this->inputs = $this->controlador_tg_manifiesto_periodo->genera_inputs(
            keys_selects:  $this->controlador_tg_manifiesto_periodo->keys_selects);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar inputs',data:  $this->inputs);
            print_r($error);
            die('Error');
        }

        return $this->inputs;
    }

    public function periodo_modifica_bd(bool $header, bool $ws = false): array|stdClass
    {
        $this->link->beginTransaction();

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $registros = $_POST;

        $r_modifica = (new tg_manifiesto_periodo($this->link))->modifica_bd(registro: $registros,
            id: $this->tg_manifiesto_periodo_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al modificar abono', data: $r_modifica, header: $header, ws: $ws);
        }

        $this->link->commit();

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $r_modifica,
                siguiente_view: "periodo", ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($r_modifica, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_modifica->siguiente_view = "periodo";

        return $r_modifica;
    }

    public function periodo_elimina_bd(bool $header, bool $ws = false): array|stdClass
    {
        $this->link->beginTransaction();

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $r_elimina = (new tg_manifiesto_periodo($this->link))->elimina_bd(id: $this->tg_manifiesto_periodo_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al eliminar periodo', data: $r_elimina, header: $header,
                ws: $ws);
        }

        $this->link->commit();

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $r_elimina,
                siguiente_view: "periodo", ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($r_elimina, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_elimina->siguiente_view = "periodo";

        return $r_elimina;
    }

    public function sube_manifiesto(bool $header, bool $ws = false){

}

    public function nominas(bool $header, bool $ws = false): array|stdClass
    {
        $r_tg_manifiesto_periodo = (new tg_manifiesto_periodo($this->link))
            ->get_periodos_manifiesto(tg_manifiesto_id:  $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener manifiesto periodo',data:  $r_tg_manifiesto_periodo,
                header: $header,ws:$ws);
        }

        $in = (new inicializacion())->genera_data_in(campo:'id', tabla: 'nom_periodo',
            registros: $r_tg_manifiesto_periodo->registros);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al integrar in',data:  $in, header: $header,ws:$ws);
        }

        $columns = array();
        $columns["nom_nomina_id"]["titulo"] = "Id";
        $columns["em_empleado_nombre"]["titulo"] = "Nombre";
        $columns["em_empleado_nombre"]["campos"] = array("em_empleado_ap","em_empleado_am");
        $columns["em_empleado_rfc"]["titulo"] = "Rfc";
        $columns["nom_nomina_fecha_inicial_pago"]["titulo"] = "Fecha Inicial Pago";
        $columns["nom_nomina_fecha_final_pago"]["titulo"] = "Fecha Final Pago";
        $columns["org_empresa_descripcion"]["titulo"] = "Empresa";
        $filtro = array("nom_nomina_id",  "em_empleado_nombre",    );

        $datatables = $this->datatable_init(columns: $columns, filtro: $filtro, identificador: "#nom_nomina",
            in: $in,  multi_selects: true);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al inicializar datatable',data:  $datatables,
                header: $header,ws:$ws);
        }

        return $datatables;
    }

    public function recibos_masivos(bool $header, bool $ws = false): array|stdClass
    {
        $r_tg_manifiesto_periodo = (new tg_manifiesto_periodo($this->link))
            ->get_periodos_manifiesto(tg_manifiesto_id:  $this->registro_id );
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener manifiesto periodo',data:  $r_tg_manifiesto_periodo,
                header: $header,ws:$ws);
        }

        $in = (new inicializacion())->genera_data_in(campo:'id', tabla: 'nom_periodo',
            registros: $r_tg_manifiesto_periodo->registros);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al integrar in',data:  $in, header: $header,ws:$ws);
        }

        $columns = array();
        $columns["nom_nomina_id"]["titulo"] = "Id";
        $columns["em_empleado_nombre"]["titulo"] = "Nombre";
        $columns["em_empleado_nombre"]["campos"] = array("em_empleado_ap","em_empleado_am");
        $columns["em_empleado_rfc"]["titulo"] = "Rfc";
        $columns["nom_nomina_fecha_inicial_pago"]["titulo"] = "Fecha Inicial Pago";
        $columns["nom_nomina_fecha_final_pago"]["titulo"] = "Fecha Final Pago";
        $columns["org_empresa_descripcion"]["titulo"] = "Empresa";
        $filtro = array("nom_nomina_id",  "em_empleado_nombre",);

        $datatables = $this->datatable_init(columns: $columns, filtro: $filtro, identificador: "#nom_nomina",
            in: $in,  multi_selects: true);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al inicializar datatable',data:  $datatables,
                header: $header,ws:$ws);
        }

        return $datatables;
    }

    public function ve_nominas(bool $header, bool $ws = false): array|stdClass
    {
        $nominas = (new tg_manifiesto_periodo($this->link))->nominas_by_manifiesto(tg_manifiesto_id: $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener nominas del periodo',data:  $nominas,
                header: $header,ws:$ws);
        }

        foreach ($nominas as $indice => $nomina) {
            $nomina = $this->data_nomina_btn(nomina: $nomina);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al asignar botones', data: $nomina, header: $header, ws: $ws);
            }
            $nominas[$indice] = $nomina;
        }
        $this->nominas = $nominas;

        return $this->nominas;
    }

    public function descarga_recibo(bool $header, bool $ws = false){
        $r_nomina = (new \gamboamartin\nomina\models\nom_nomina($this->link))->descarga_recibo_nomina(nom_nomina_id: $this->registro_id);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener recibo de nomina', data: $r_nomina);
            print_r($error);
            die('Error');
        }

        return $r_nomina;
    }

    public function descarga_recibo_conjunto(bool $header, bool $ws = false){

        $filtro['nom_nomina.nom_periodo_id'] = $this->registro_id;
        $nominas = (new \gamboamartin\nomina\models\nom_nomina($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener nominas', data: $nominas);
        }

        $r_nomina = (new nom_nomina($this->link))->descarga_recibo_nomina_foreach(nom_nominas: $nominas);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener recibo de nomina', data: $r_nomina);
            print_r($error);
            die('Error');
        }
        exit;
    }


}
