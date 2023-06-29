<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use PDO;
use stdClass;

class controlador_em_empleado extends \tglobally\tg_empleado\controllers\controlador_em_empleado {

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass())
    {
        parent::__construct($link, $paths_conf);
    }

    protected function campos_view(): array
    {
        $keys = new stdClass();
        $keys->inputs = array('codigo', 'descripcion', 'nombre', 'ap', 'am',  'rfc', 'curp', 'nss', 'salario_diario',
            'salario_diario_integrado','com_sucursal','org_sucursal', 'salario_total','correo');
        $keys->telefonos = array('telefono');
        $keys->fechas = array('fecha_inicio_rel_laboral', 'fecha_inicio', 'fecha_final');
        $keys->selects = array();

        $init_data = array();
        $init_data['nom_conf_nomina'] = "gamboamartin\\nomina";
        $init_data['dp_pais'] = "gamboamartin\\direccion_postal";
        $init_data['dp_estado'] = "gamboamartin\\direccion_postal";
        $init_data['dp_municipio'] = "gamboamartin\\direccion_postal";
        $init_data['dp_cp'] = "gamboamartin\\direccion_postal";
        $init_data['dp_colonia_postal'] = "gamboamartin\\direccion_postal";
        $init_data['dp_calle_pertenece'] = "gamboamartin\\direccion_postal";
        $init_data['cat_sat_regimen_fiscal'] = "gamboamartin\\cat_sat";
        $init_data['cat_sat_tipo_regimen_nom'] = "gamboamartin\\cat_sat";
        $init_data['cat_sat_tipo_jornada_nom'] = "gamboamartin\\cat_sat";
        $init_data['org_puesto'] = "gamboamartin\\organigrama";
        $init_data['em_centro_costo'] = "gamboamartin\\empleado";
        $init_data['em_empleado'] = "gamboamartin\\empleado";
        $init_data['em_registro_patronal'] = "gamboamartin\\empleado";
        $init_data['com_sucursal'] = "gamboamartin\\comercial";


        $campos_view = $this->campos_view_base(init_data: $init_data, keys: $keys);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar campo view', data: $campos_view);
        }

        return $campos_view;
    }

    public function init_selects_inputs(): array
    {
        $keys_selects = parent::init_selects_inputs();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "nom_conf_nomina_id", label: "Conf. Nomina",
            cols: 12);

        return $keys_selects;
    }

    private function init_selects(array $keys_selects, string $key, string $label, int $id_selected = -1, int $cols = 6,
                                  bool  $con_registros = true, array $filtro = array()): array
    {
        $keys_selects = $this->key_select(cols: $cols, con_registros: $con_registros, filtro: $filtro, key: $key,
            keys_selects: $keys_selects, id_selected: $id_selected, label: $label);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        return $keys_selects;
    }

}
