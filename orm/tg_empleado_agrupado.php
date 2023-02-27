<?php
namespace gamboamartin\tg_nomina\models;
use base\orm\_modelo_parent;
use PDO;

class tg_empleado_agrupado extends _modelo_parent {

    public function __construct(PDO $link){
        $tabla = 'tg_empleado_agrupado';
        $columnas = array($tabla=>false,'tg_agrupador'=>$tabla,'tg_empleado'=>$tabla);
        $campos_obligatorios[] = 'descripcion';
        $campos_obligatorios[] = 'descripcion_select';

        $tipo_campos['codigos'] = 'cod_1_letras_mayusc';





        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,tipo_campos: $tipo_campos);

        $this->NAMESPACE = __NAMESPACE__;
    }


}