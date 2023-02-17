<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\nomina\models\nom_periodo;
use gamboamartin\plugins\exportador;
use PDO;
use stdClass;
use tglobally\template_tg\html;

class controlador_nom_periodo extends \gamboamartin\nomina\controllers\controlador_nom_periodo {

    public array $sidebar = array();
    public stdClass|array $keys_selects = array();
    public string $link_nom_periodo_reportes = '';
    public string $link_nom_periodo_exportar = '';
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
}
