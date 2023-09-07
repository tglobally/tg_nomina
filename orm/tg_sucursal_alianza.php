<?php
namespace tglobally\tg_nomina\models;

use base\orm\_modelo_parent;
use base\orm\modelo;

use gamboamartin\errores\errores;

use PDO;
use stdClass;

class tg_sucursal_alianza extends _modelo_parent{

    public function __construct(PDO $link){
        $tabla = 'tg_sucursal_alianza';
        $columnas = array($tabla=>false, 'com_sucursal'=>$tabla,'tg_cte_alianza'=>$tabla,'com_cliente'=>'com_sucursal');
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        if(!isset($this->registro['codigo'])){
            $this->registro['codigo'] =  $this->get_codigo_aleatorio();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo aleatorio',data:  $this->registro);
            }
        }

        $this->registro = $this->campos_base(data: $this->registro,modelo: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campos base',data: $this->registro);
        }

        $r_alta_bd =  parent::alta_bd();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error registrar conf. nomina', data: $r_alta_bd);
        }

        return $r_alta_bd;
    }
}