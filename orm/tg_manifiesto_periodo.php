<?php
namespace models;
use base\orm\modelo;

use gamboamartin\errores\errores;

use PDO;
use stdClass;

class tg_manifiesto_periodo extends modelo{

    public function __construct(PDO $link){
        $tabla = 'tg_manifiesto_periodo';
        $columnas = array($tabla=>false, 'tg_manifiesto' =>$tabla,'nom_periodo'=>$tabla,
            'cat_sat_periodicidad_pago_nom'=>'nom_periodo');
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis',
            'tg_manifiesto_id','nom_periodo_id');

        $campos_view['tg_manifiesto_id']['type'] = 'selects';
        $campos_view['tg_manifiesto_id']['model'] = (new tg_manifiesto($link));
        $campos_view['nom_periodo_id']['type'] = 'selects';
        $campos_view['nom_periodo_id']['model'] = (new nom_periodo($link));

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }
}