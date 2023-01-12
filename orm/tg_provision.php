<?php
namespace models;
use base\orm\_modelo_parent;
use PDO;

class tg_provision extends _modelo_parent {

    public function __construct(PDO $link){
        $tabla = 'tg_provision';
        $columnas = array($tabla=>false, 'tg_tipo_provision'=>$tabla, 'nom_nomina' => $tabla);
        $campos_obligatorios = array('descripcion','codigo');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

}