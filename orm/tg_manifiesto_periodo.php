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

    public function alta_bd(): array|stdClass
    {
        if (!isset($this->registro['codigo'])) {
            $this->registro['codigo'] = $this->registro['tg_manifiesto_id'];
            $this->registro['codigo'] .= $this->registro['nom_periodo_id'];
            $this->registro['codigo'] .= $this->registro['descripcion'];
        }

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

    public function get_periodos_manifiesto(int $tg_manifiesto_id): array|stdClass
    {
        if($tg_manifiesto_id <=0){
            return $this->error->error(mensaje: 'Error $tg_manifiesto_id debe ser mayor a 0', data: $tg_manifiesto_id);
        }

        $filtro['tg_manifiesto_periodo.tg_manifiesto_id'] = $tg_manifiesto_id;
        $registros = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener periodos', data: $registros);
        }

        return $registros;
    }
}