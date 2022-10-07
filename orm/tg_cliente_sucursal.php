<?php
namespace models;
use base\orm\modelo;

use gamboamartin\errores\errores;

use PDO;
use stdClass;

class tg_cliente_sucursal extends modelo{

    public function __construct(PDO $link){
        $tabla = 'tg_cliente_sucursal';
        $columnas = array($tabla=>false, 'com_cliente'=>$tabla,'org_sucursal'=>$tabla);
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }
}