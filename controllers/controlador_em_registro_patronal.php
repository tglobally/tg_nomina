<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;

use PDO;
use stdClass;
use tglobally\template_tg\html;

class controlador_em_registro_patronal extends \tglobally\tg_empleado\controllers\controlador_em_registro_patronal {

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass())
    {
        $html_base = new html();
        parent::__construct(link: $link, paths_conf: $paths_conf);
        $this->titulo_lista = 'Registro Patronal';
        $this->seccion_titulo = "Registro Patronal";
        $this->titulo_accion = "Listado de Registros Patronales";

        $this->datatables[0] ['columnDefs'][count($this->datatables[0]['columnDefs'])-1]->type = "menu";

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }
    }

}
