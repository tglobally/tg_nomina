<?php
namespace gamboamartin\tg_nomina\models;
use base\orm\_modelo_parent;
use PDO;

class tg_empleado_agrupado extends _modelo_parent {

    public function __construct(PDO $link){
        $tabla = 'cob_concepto';
        $columnas = array($tabla=>false,'cob_tipo_concepto'=>$tabla,'cob_tipo_ingreso'=>'cob_tipo_concepto');
        $campos_obligatorios[] = 'descripcion';
        $campos_obligatorios[] = 'descripcion_select';

        $tipo_campos['codigos'] = 'cod_1_letras_mayusc';

        $columnas_extra['cob_concepto_n_deudas'] = /** @lang sql */
            "(SELECT COUNT(*) FROM cob_deuda WHERE cob_deuda.cob_concepto_id = cob_concepto .id)";




        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, columnas_extra: $columnas_extra,tipo_campos: $tipo_campos);

        $this->NAMESPACE = __NAMESPACE__;
    }


}