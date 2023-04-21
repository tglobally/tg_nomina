<?php
namespace tglobally\tg_nomina\controllers;

use base\orm\inicializacion;
use config\generales;
use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\nomina\models\nom_par_deduccion;
use gamboamartin\nomina\models\nom_par_otro_pago;
use gamboamartin\nomina\models\nom_par_percepcion;
use gamboamartin\plugins\exportador;
use gamboamartin\system\_ctl_base;
use Mpdf\Mpdf;
use PDO;
use stdClass;
use tglobally\template_tg\html;
use tglobally\tg_nomina\models\tg_manifiesto_periodo;
use ZipArchive;

class controlador_nom_periodo extends \gamboamartin\nomina\controllers\controlador_nom_periodo {

    public array $sidebar = array();
    public stdClass|array $keys_selects = array();
    public string $link_nom_periodo_alta_bd = '';
    public string $link_nom_periodo_reportes = '';
    public string $link_nom_periodo_exportar = '';
    public string $link_nom_periodo_nominas = '';
    public string $link_nom_periodo_agregar_percepcion = '';
    public string $link_nom_periodo_agregar_percepcion_bd = '';
    public string $link_nom_periodo_agregar_deduccion = '';
    public string $link_nom_periodo_agregar_deduccion_bd = '';
    public string $link_nom_periodo_agregar_otro_pago = '';
    public string $link_nom_periodo_agregar_otro_pago_bd = '';
    public string $link_nom_periodo_descarga_pdf = '';
    public string $link_nom_periodo_descarga_comprimido = '';
    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){
        $html_base = new html();
        parent::__construct( link: $link, html: $html_base);
        $this->titulo_lista = 'Periodos';

        $campos_view['filtro_fecha_inicio'] = array('type' => 'dates');
        $campos_view['filtro_fecha_final'] = array('type' => 'dates');

        $this->modelo->campos_view = $campos_view;

        $this->link_nom_periodo_reportes = $this->obj_link->link_con_id(accion: "reportes", link: $link, registro_id: $this->registro_id,
            seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_reportes);
            print_r($error);
            exit;
        }

        $this->link_nom_periodo_exportar = $this->obj_link->link_con_id(accion: "exportar", link: $link, registro_id: $this->registro_id,
            seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_exportar);
            print_r($error);
            exit;
        }

        $init_links = $this->init_links();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar links', data: $init_links);
            print_r($error);
            die('Error');
        }

        $this->sidebar['lista']['titulo'] = "Periodos";
        $this->sidebar['lista']['menu'] = array(
            $this->menu_item(menu_item_titulo: "Alta", link: $this->link_alta, menu_seccion_active: true,
                menu_lateral_active: true),
            $this->menu_item(menu_item_titulo: "Reportes", link: $this->link_nom_periodo_reportes, menu_seccion_active: true,
                menu_lateral_active: true));

        $this->sidebar['alta']['titulo'] = "Alta Periodos";
        $this->sidebar['alta']['stepper_active'] = true;
        $this->sidebar['alta']['menu'] = array(
            $this->menu_item(menu_item_titulo: "Alta", link: $this->link_alta, menu_lateral_active: true));

        $this->sidebar['modifica']['titulo'] = "Modifica Periodos";
        $this->sidebar['modifica']['stepper_active'] = true;
        $this->sidebar['modifica']['menu'] = array(
            $this->menu_item(menu_item_titulo: "Modifica", link: $this->link_alta, menu_lateral_active: true));

        $this->sidebar['nominas']['titulo'] = "Nominas";
        $this->sidebar['nominas']['stepper_active'] = true;
        $this->sidebar['nominas']['menu'] = array(
            $this->menu_item(menu_item_titulo: "nominas", link: $this->link_alta, menu_lateral_active: true));

        $this->sidebar['reportes']['titulo'] = "Reportes";
        $this->sidebar['reportes']['stepper_active'] = true;
        $this->sidebar['reportes']['menu'] = array(
            $this->menu_item(menu_item_titulo: "periodo", link: $this->link_alta, menu_lateral_active: true));

    }

    private function init_links(): array|string
    {

        $this->link_nom_periodo_alta_bd = $this->obj_link->link_alta_bd(link: $this->link,
            seccion: 'tg_manifiesto_periodo');
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_alta_bd);
            print_r($error);
            exit;
        }

        $this->link_nom_periodo_nominas = $this->obj_link->link_con_id(accion: "nominas",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_nominas);
            print_r($error);
            exit;
        }

        $this->link_nom_periodo_agregar_percepcion = $this->obj_link->link_con_id(accion: "agregar_percepcion",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_agregar_percepcion);
            print_r($error);
            exit;
        }

        $this->link_nom_periodo_agregar_percepcion_bd = $this->obj_link->link_con_id(accion: "agregar_percepcion_bd",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_agregar_percepcion_bd);
            print_r($error);
            exit;
        }

        $this->link_nom_periodo_agregar_deduccion = $this->obj_link->link_con_id(accion: "agregar_deduccion",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_agregar_deduccion);
            print_r($error);
            exit;
        }

        $this->link_nom_periodo_agregar_deduccion_bd = $this->obj_link->link_con_id(accion: "agregar_deduccion_bd",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_agregar_deduccion_bd);
            print_r($error);
            exit;
        }

        $this->link_nom_periodo_agregar_otro_pago = $this->obj_link->link_con_id(accion: "agregar_otro_pago",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_agregar_otro_pago);
            print_r($error);
            exit;
        }

        $this->link_nom_periodo_agregar_otro_pago_bd = $this->obj_link->link_con_id(accion: "agregar_otro_pago_bd",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_agregar_otro_pago_bd);
            print_r($error);
            exit;
        }

        $this->link_nom_periodo_descarga_pdf = $this->obj_link->link_con_id(accion: "descarga_pdf",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_descarga_pdf);
            print_r($error);
            exit;
        }

        $this->link_nom_periodo_descarga_comprimido = $this->obj_link->link_con_id(accion: "descarga_comprimido",
            link: $this->link, registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_periodo_descarga_comprimido);
            print_r($error);
            exit;
        }

        return $this->link_nom_periodo_alta_bd;
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

    public function reportes(bool $header, bool $ws = false): array|stdClass
    {
        $columns["nom_nomina_id"]["titulo"] = "Id";
        $columns["em_empleado_rfc"]["titulo"] = "Rfc";
        $columns["em_empleado_nombre"]["titulo"] = "Empleado";
        $columns["nom_periodo_fecha_inicial_pago"]["titulo"] = "Fecha Inicial";
        $columns["nom_periodo_fecha_final_pago"]["titulo"] = "Fecha Final";
        $columns["em_empleado_nombre"]["campos"] = array("em_empleado_ap","em_empleado_am");


        $filtro = array();

        $datatable = $this->datatable_init(columns: $columns,identificador: "#nom_nomina");
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al inicializar datatable',data:  $datatable,
                header: $header,ws:$ws);
        }

        $this->asignar_propiedad(identificador: 'filtro_fecha_inicio', propiedades: ['place_holder'=> 'Fecha Inicio',
            'cols' => 6, 'required' => false]);
        $this->asignar_propiedad(identificador: 'filtro_fecha_final', propiedades: ['place_holder'=> 'Fecha Final',
            'cols' => 6]);

        $r_alta =  parent::alta(header: false);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar template',data:  $r_alta, header: $header,ws:$ws);
        }

        $inputs = $this->genera_inputs(keys_selects:  $this->keys_selects);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar inputs',data:  $inputs);
            print_r($error);
            die('Error');
        }

        return $this->inputs;
    }

    public function exportar(bool $header, bool $ws = false): array|stdClass
    {
        $fecha_inicio = "";
        $fecha_fin = "";

        if (isset($_POST['filtro_fecha_inicio'])){
            $fecha_inicio = $_POST['filtro_fecha_inicio'];
        }

        if (isset($_POST['filtro_fecha_final'])){
            $fecha_fin = $_POST['filtro_fecha_final'];
        }

        $filtro_especial[0][$fecha_fin]['operador'] = '>=';
        $filtro_especial[0][$fecha_fin]['valor'] = 'nom_periodo.fecha_inicial_pago';
        $filtro_especial[0][$fecha_fin]['comparacion'] = 'AND';
        $filtro_especial[0][$fecha_fin]['valor_es_campo'] = true;

        $filtro_especial[1][$fecha_inicio]['operador'] = '<=';
        $filtro_especial[1][$fecha_inicio]['valor'] = 'nom_periodo.fecha_final_pago';
        $filtro_especial[1][$fecha_inicio]['comparacion'] = 'AND';
        $filtro_especial[1][$fecha_inicio]['valor_es_campo'] = true;

        $nominas = (new nom_nomina($this->link))->filtro_and(columnas: array('nom_nomina_id'), filtro_especial: $filtro_especial);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al obtener registros',data:  $nominas);
            print_r($error);
            die('Error');
        }

        $conceptos = (new nom_nomina($this->link))->obten_conceptos_nominas(nominas: $nominas->registros);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener nominas del periodo',data:  $conceptos,
                header: $header,ws:$ws);
        }

        $exportador = (new exportador());
        $registros_xls = array();

        foreach ($nominas->registros as $nomina){
            $row = (new nom_nomina($this->link))->maqueta_registros_excel(nom_nomina_id: $nomina['nom_nomina_id'],
                conceptos_nomina: $conceptos);
            if(errores::$error){
                return $this->retorno_error(mensaje: 'Error al maquetar datos de la nomina',data:  $row,
                    header: $header,ws:$ws);
            }

            $registros_xls[] = $row;
        }

        $keys = array();

        foreach (array_keys($registros_xls[0]) as $key) {
            $keys[$key] = strtoupper(str_replace('_', ' ', $key));
        }

        $registros = array();

        foreach ($registros_xls as $row) {
            $registros[] = array_combine(preg_replace(array_map(function($s){return "/^$s$/";},
                array_keys($keys)),$keys, array_keys($row)), $row);
        }

        $resultado = $exportador->listado_base_xls(header: $header, name: $this->seccion, keys:  $keys,
            path_base: $this->path_base,registros:  $registros,totales:  array());
        if(errores::$error){
            $error =  $this->errores->error('Error al generar xls',$resultado);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $link = "./index.php?seccion=nom_periodo&accion=reportes&registro_id=".$this->registro_id;
        $link.="&session_id=$this->session_id";
        header('Location:' . $link);
        exit;
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

        $r_alta = (new _ctl_base(html: $this->html,link: $this->link, modelo: $this->modelo, obj_link: $this->obj_link))->init_alta();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar alta', data: $r_alta, header: $header, ws: $ws);
        }

        $this->row_upd->importe_gravado = 0;
        $this->row_upd->importe_exento = 0;

        $keys_selects = (new _ctl_base(html: $this->html,link: $this->link, modelo: $this->modelo, obj_link: $this->obj_link))->init_selects_inputs();
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

        header('Location:' . $this->link_nom_periodo_nominas);
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

        $r_alta = (new _ctl_base(html: $this->html,link: $this->link, modelo: $this->modelo, obj_link: $this->obj_link))->init_alta();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar alta', data: $r_alta, header: $header, ws: $ws);
        }

        $this->row_upd->importe_gravado = 0;
        $this->row_upd->importe_exento = 0;

        $keys_selects = (new _ctl_base(html: $this->html,link: $this->link, modelo: $this->modelo, obj_link: $this->obj_link))->init_selects_inputs();
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

        header('Location:' . $this->link_nom_periodo_nominas);
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

        $r_alta = (new _ctl_base(html: $this->html,link: $this->link, modelo: $this->modelo, obj_link: $this->obj_link))->init_alta();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar alta', data: $r_alta, header: $header, ws: $ws);
        }

        $this->row_upd->importe_gravado = 0;
        $this->row_upd->importe_exento = 0;

        $keys_selects = (new _ctl_base(html: $this->html,link: $this->link, modelo: $this->modelo, obj_link: $this->obj_link))->init_selects_inputs();
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

        header('Location:' . $this->link_nom_periodo_nominas);
        exit;
    }

    public function asignar_propiedad(string $identificador, mixed $propiedades): array|stdClass
    {
        if (!array_key_exists($identificador,$this->keys_selects)){
            $this->keys_selects[$identificador] = new stdClass();
        }

        foreach ($propiedades as $key => $value){
            $this->keys_selects[$identificador]->$key = $value;
        }

        return $this->keys_selects;
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
        $nombreZip = 'Nominas.zip';
        $zip->open($nombreZip, ZipArchive::CREATE);

        foreach ($id_nominas as $nom_nomina_id){
            $temporales = (new generales())->path_base . "archivos/tmp/";
            $pdf = new Mpdf(['tempDir' => $temporales]);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al crear pdf', data: $pdf,
                    header: $header, ws: $ws);
            }

            $nom_nomina = (new \tglobally\tg_nomina\models\nom_nomina($this->link))->registro(registro_id: $nom_nomina_id);
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

        unlink($nombreZip);
        exit;
    }

    public function recibos_masivos(bool $header, bool $ws = false): array|stdClass
    {


        $filtro_nomina['nom_nomina.nom_periodo_id'] = $this->registro_id;
        $nominas = (new nom_nomina($this->link))->filtro_and(filtro: $filtro_nomina);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener nominas', data: $nominas);
        }

        $in = (new inicializacion())->genera_data_in(campo:'id', tabla: 'nom_nomina',
            registros: $nominas->registros);
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
            in: $in, multi_selects: true);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al inicializar datatable',data:  $datatables,
                header: $header,ws:$ws);
        }

        return $datatables;
    }
}
