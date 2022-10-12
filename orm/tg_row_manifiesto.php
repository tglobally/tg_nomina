<?php
namespace models;
use base\orm\modelo;

use gamboamartin\empleado\models\em_empleado;
use gamboamartin\errores\errores;

use PDO;
use stdClass;

class tg_row_manifiesto extends modelo{

    public function __construct(PDO $link){
        $tabla = 'tg_row_manifiesto';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis');

        $campos_view['tg_manifiesto_id']['type'] = 'selects';
        $campos_view['tg_manifiesto_id']['model'] = (new tg_manifiesto($link));
        $campos_view['em_empleado_id']['type'] = 'selects';
        $campos_view['em_empleado_id']['model'] = (new em_empleado($link));

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }
}