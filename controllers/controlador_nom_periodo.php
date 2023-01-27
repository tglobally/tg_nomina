<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\nomina\models\nom_periodo;
use PDO;
use stdClass;
use tglobally\template_tg\html;

class controlador_nom_periodo extends \gamboamartin\nomina\controllers\controlador_nom_periodo {

    public array $sidebar = array();
    public array $keys_selects = array();
    public string $link_nom_periodo_reportes = '';

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

    public function asignar_propiedad(string $identificador, mixed $propiedades)
    {
        if (!array_key_exists($identificador,$this->keys_selects)){
            $this->keys_selects[$identificador] = new stdClass();
        }

        foreach ($propiedades as $key => $value){
            $this->keys_selects[$identificador]->$key = $value;
        }
    }
}
