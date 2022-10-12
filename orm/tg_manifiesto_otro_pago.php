<?php
namespace models;
use base\orm\modelo;

use gamboamartin\empleado\models\em_empleado;
use gamboamartin\errores\errores;

use PDO;
use stdClass;

class tg_manifiesto_otro_pago extends modelo{

    public function __construct(PDO $link){
        $tabla = 'tg_manifiesto_otro_pago';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis');

        $campos_view['tg_row_manifiesto_id']['type'] = 'selects';
        $campos_view['tg_row_manifiesto_id']['model'] = (new tg_row_manifiesto($link));
        $campos_view['nom_par_otro_pago_id']['type'] = 'selects';
        $campos_view['nom_par_otro_pago_id']['model'] = (new nom_par_otro_pago($link));

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }
}