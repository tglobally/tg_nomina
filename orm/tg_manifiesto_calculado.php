<?php
namespace tglobally\tg_nomina\models;

use base\orm\_modelo_parent;
use PDO;

class tg_manifiesto_calculado extends _modelo_parent{

    public function __construct(PDO $link){
        $tabla = 'tg_manifiesto_calculado';
        $columnas = array($tabla => false, 'cat_sat_isn' => $tabla,'nom_nomina' => $tabla);
        $campos_obligatorios = array('descripcion','codigo');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

}