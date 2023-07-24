<?php
namespace tglobally\tg_nomina\models;
use base\orm\_modelo_parent;
use PDO;

class tg_empleado_agrupado extends _modelo_parent {

    public function __construct(PDO $link){
        $tabla = 'tg_empleado_agrupado';
        $columnas = array($tabla=>false,'tg_agrupador'=>$tabla,'em_empleado'=>$tabla);
        $campos_obligatorios[] = 'descripcion';
        $campos_obligatorios[] = 'descripcion_select';

        $tipo_campos['codigos'] = 'cod_1_letras_mayusc';
        $columnas_extra['em_empleado_nombre_completo'] = 'CONCAT (IFNULL(em_empleado.nombre,"")," ",IFNULL(em_empleado.ap, "")," ",IFNULL(em_empleado.am,""))';

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios, columnas: $columnas,
            columnas_extra: $columnas_extra, tipo_campos: $tipo_campos);

        $this->NAMESPACE = __NAMESPACE__;
    }


}