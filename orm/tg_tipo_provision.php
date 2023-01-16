<?php
namespace tglobally\tg_nomina\models;

use base\orm\_modelo_parent;
use PDO;

class tg_tipo_provision extends _modelo_parent {

    public function __construct(PDO $link){
        $tabla = 'tg_tipo_provision';
        $columnas = array($tabla=>false, 'nom_percepcion'=>$tabla);
        $campos_obligatorios = array('descripcion','codigo');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

}