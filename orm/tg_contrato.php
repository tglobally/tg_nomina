<?php
namespace tglobally\tg_nomina\models;

use base\orm\modelo;

use gamboamartin\documento\models\doc_documento;
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
        $this->registro['codigo'] = $this->get_codigo_aleatorio() . $this->registro['descripcion'];

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

        $doc_documento_modelo = new doc_documento($this->link);
        $doc_documento_modelo->registro['descripcion'] = $this->registro['descripcion'];
        $doc_documento_modelo->registro['descripcion_select'] = $this->registro['descripcion_select'];
        $doc_documento_modelo->registro['doc_tipo_documento_id'] = 13;
        $doc_documento = $doc_documento_modelo->alta_bd(file: $_FILES['archivo']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta el documento', data: $doc_documento);
        }

        $this->registro['doc_documento_id'] = $doc_documento->registro_id;

        $r_alta_bd = parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta anticipo',data: $r_alta_bd);
        }

        return $r_alta_bd;
    }

}