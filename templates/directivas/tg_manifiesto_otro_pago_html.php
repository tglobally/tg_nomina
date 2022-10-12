<?php
namespace html;

use base\controller\controler;
use gamboamartin\errores\errores;
use gamboamartin\template\directivas;
use PDO;
use stdClass;

class tg_manifiesto_otro_pago_html extends em_html {

    private function asigna_inputs( $controler, array $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->tg_row_manifiesto_id = $inputs['selects']->tg_row_manifiesto_id;
        $controler->inputs->select->nom_par_deduccion_id = $inputs['selects']->nom_par_deduccion_id;

        return $controler->inputs;
    }

    public function genera_inputs( $controler, array $keys_selects = array()): array|stdClass
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

}
