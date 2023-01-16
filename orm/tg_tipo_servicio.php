<?php
namespace tglobally\tg_nomina\models;

use base\orm\modelo;

use gamboamartin\errores\errores;

use PDO;
use stdClass;

class tg_tipo_servicio extends modelo{

    public function __construct(PDO $link){
        $tabla = 'tg_tipo_servicio';
        $columnas = array($tabla=>false, 'nom_conf_nomina'=>$tabla,'cat_sat_periodicidad_pago_nom'=>'nom_conf_nomina',
            'cat_sat_tipo_nomina'=>'nom_conf_nomina');
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(): array|stdClass
    {
        if (!isset($this->registro['descripcion_select'])) {
            $this->registro['descripcion_select'] = $this->registro['descripcion'];
        }

        if (!isset($this->registro['codigo_bis'])) {
            $this->registro['codigo_bis'] = $this->registro['codigo'];
        }

        if (!isset($this->registro['alias'])) {
            $this->registro['alias'] = $this->registro['codigo'];
            $this->registro['alias'] .= $this->registro['descripcion'];
        }

        $r_alta_bd = parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta anticipo',data: $r_alta_bd);
        }

        return $r_alta_bd;
    }

}