<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_nomina;
use PDO;
use stdClass;
use tglobally\template_tg\html;

class controlador_nom_periodo extends \gamboamartin\nomina\controllers\controlador_nom_periodo {

    public array $sidebar = array();

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){
        $html_base = new html();
        parent::__construct( link: $link, html: $html_base);
        $this->titulo_lista = 'Periodos';

        $this->sidebar['lista']['titulo'] = "Periodos";
        $this->sidebar['lista']['menu'] = array(
            $this->menu_item(menu_item_titulo: "Alta", link: $this->link_alta, menu_seccion_active: true,
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


        return array();
    }
}
