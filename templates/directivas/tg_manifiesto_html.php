<?php
namespace html;

use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use models\tg_manifiesto;
use PDO;
use stdClass;
use tglobally\tg_nomina\controllers\controlador_tg_manifiesto;

class tg_manifiesto_html extends em_html {

    private function asigna_inputs(controlador_tg_manifiesto $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->fc_csd_id = $inputs->selects->fc_csd_id;
        $controler->inputs->select->tg_tipo_servicio_id = $inputs->selects->tg_tipo_servicio_id;
        $controler->inputs->fecha_envio = $inputs->dates->fecha_envio;
        $controler->inputs->fecha_pago = $inputs->dates->fecha_pago;
        return $controler->inputs;
    }

    public function genera_inputs(controlador_tg_manifiesto $controler, array $keys_selects = array()): array|stdClass
    {
        $inputs = $this->init_alta2(row_upd: $controler->row_upd, modelo: $controler->modelo, link: $controler->link,
            keys_selects:$keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }

        $inputs_asignados = $this->asigna_inputs(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function select_tg_manifiesto_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                          array $filtro = array()): array|string
    {
        $valida = (new directivas(html:$this->html_base))->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }
        if(is_null($id_selected)){
            $id_selected = -1;
        }
        $modelo = new tg_manifiesto(link: $link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo,filtro: $filtro, label: 'Manifiesto',required: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

}
