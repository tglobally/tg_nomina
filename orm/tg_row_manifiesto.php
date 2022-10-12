<?php
namespace models;
use base\orm\modelo;

use gamboamartin\errores\errores;

use PDO;
use stdClass;

class tg_row_manifiesto extends modelo{

    public function __construct(PDO $link){
        $tabla = 'tg_row_manifiesto';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }
}