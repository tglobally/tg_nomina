<?php
namespace tglobally\tg_nomina\models;

use base\orm\modelo;

use gamboamartin\errores\errores;

use PDO;
use stdClass;

class tg_contrato extends modelo{

    public function __construct(PDO $link){
        $tabla = 'tg_contrato';
        $columnas = array($tabla=>false, 'doc_documento'=>$tabla);
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