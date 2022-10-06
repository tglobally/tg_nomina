<?php
namespace models;
use base\orm\modelo;

use gamboamartin\comercial\models\com_sucursal;
use gamboamartin\errores\errores;

use gamboamartin\facturacion\models\fc_csd;
use PDO;
use stdClass;

class tg_manifiesto extends modelo{

    public function __construct(PDO $link){
        $tabla = 'tg_manifiesto';
        $columnas = array($tabla=>false, 'fc_csd'=>$tabla,'tg_tipo_servicio' =>$tabla,'tg_sucursal_alianza'=>$tabla);
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis',
            'fc_csd_id','tg_tipo_servicio_id','fecha_envio','fecha_pago');

        $campos_view['com_sucursal_id']['type'] = 'selects';
        $campos_view['com_sucursal_id']['model'] = (new com_sucursal($link));
        $campos_view['tg_cte_alianza_id']['type'] = 'selects';
        $campos_view['tg_cte_alianza_id']['model'] = (new tg_cte_alianza($link));
        $campos_view['fc_csd_id']['type'] = 'selects';
        $campos_view['fc_csd_id']['model'] = (new fc_csd($link));
        $campos_view['tg_tipo_servicio_id']['type'] = 'selects';
        $campos_view['tg_tipo_servicio_id']['model'] = (new tg_tipo_servicio($link));
        $campos_view['fecha_envio']['type'] = 'dates';
        $campos_view['fecha_pago']['type'] = 'dates';

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view:  $campos_view );

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
        if(isset($this->registro['com_sucursal_id'])){
            unset($this->registro['com_sucursal_id']);
        }
        if(isset($this->registro['tg_cte_alianza_id'])){
            unset($this->registro['tg_cte_alianza_id']);
        }

        $r_alta_bd = parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta manifiesto',data: $r_alta_bd);
        }

        return $r_alta_bd;
    }

    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array|stdClass
    {
        if(isset($this->registro['com_sucursal_id'])){
            unset($this->registro['com_sucursal_id']);
        }
        if(isset($this->registro['tg_cte_alianza_id'])){
            unset($this->registro['tg_cte_alianza_id']);
        }

        $r_modifica_bd = parent::modifica_bd($registro, $id, $reactiva);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar manifiesto',data: $r_modifica_bd);
        }

        return $r_modifica_bd;
    }
}