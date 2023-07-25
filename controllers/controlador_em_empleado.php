<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\banco\models\bn_sucursal;
use gamboamartin\documento\models\doc_documento;
use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_conf_nomina;
use gamboamartin\plugins\Importador;
use PDO;
use stdClass;
use tglobally\tg_nomina\models\em_empleado;

class controlador_em_empleado extends \tglobally\tg_empleado\controllers\controlador_em_empleado {

    public string $link_em_empleado_nominas = '';

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass())
    {
        parent::__construct($link, $paths_conf);

        $this->modelo = new em_empleado($link);

        $this->link_em_empleado_nominas = $this->obj_link->link_con_id(accion: "nominas",link: $this->link,
            registro_id: $this->registro_id,seccion: "em_empleado");
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_em_empleado_nominas);
            print_r($error);
            exit;
        }

        $this->seccion_titulo = 'Empleados';
        $this->titulo_pagina = "Nominas - Empleados";
        $this->titulo_accion = "Empleados";

        $acciones = $this->define_acciones_menu(acciones: array("alta" => $this->link_alta,
            "importar" => $this->link_em_empleado_sube_archivo, "reportes" => $this->link_em_empleado_reportes));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al integrar acciones para el menu', data: $acciones);
            print_r($error);
            die('Error');
        }
    }

    protected function init_datatable(): stdClass
    {
        $columns["em_empleado_id"]["titulo"] = "Id";
        $columns["em_empleado_nombre"]["titulo"] = "Nombre";
        $columns["em_empleado_nombre"]["campos"] = array("em_empleado_ap","em_empleado_am");
        $columns["em_empleado_rfc"]["titulo"] = "Rfc";
        $columns["em_empleado_nss"]["titulo"] = "NSS";
        $columns["org_puesto_descripcion"]["titulo"] = "Puesto";
        $columns["em_empleado_n_cuentas_bancarias"]["titulo"] = "Cuentas Bancarias";

        $filtro = array("em_empleado.id","em_empleado.nombre","em_empleado.ap","em_empleado.am","em_empleado.rfc",
            "em_empleado_nombre_completo","em_empleado_nombre_completo_inv", "em_empleado.nss","org_puesto.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;
        $datatables->menu_active = true;

        return $datatables;
    }

    protected function campos_view(): array
    {
        $keys = new stdClass();
        $keys->inputs = array('codigo', 'descripcion', 'nombre', 'ap', 'am',  'rfc', 'curp', 'nss', 'salario_diario',
            'salario_diario_integrado','com_sucursal','org_sucursal', 'salario_total','correo', 'num_cuenta', 'clabe');
        $keys->telefonos = array('telefono');
        $keys->fechas = array('fecha_inicio_rel_laboral', 'fecha_inicio', 'fecha_final', 'fecha_antiguedad');
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
        $init_data['bn_sucursal'] = "gamboamartin\\banco";


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

        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "bn_sucursal_id", label: "Banco",
            cols: 12);

        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "com_sucursal_id", label: "Cliente",
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

    protected function key_selects_txt(array $keys_selects): array
    {
        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'num_cuenta',
            keys_selects: $keys_selects, place_holder: 'Número Cuenta');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'clabe',
            keys_selects: $keys_selects, place_holder: 'Clabe');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = parent::key_selects_txt($keys_selects);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        return $keys_selects;
    }

    public function lee_archivo(bool $header, bool $ws = false)
    {
        $doc_documento_modelo = new doc_documento($this->link);
        $doc_documento_modelo->registro['descripcion'] = "Alta empleados ". rand();
        $doc_documento_modelo->registro['descripcion_select'] = rand();
        $doc_documento_modelo->registro['doc_tipo_documento_id'] = 1;
        $doc_documento = $doc_documento_modelo->alta_bd(file: $_FILES['archivo']);
        if (errores::$error) {
            $error =  $this->errores->error(mensaje: 'Error al dar de alta el documento', data: $doc_documento);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $columnas = array("CODIGO", "NOMBRE", "APELLIDO PATERNO", "APELLIDO MATERNO", "TELEFONO", "CURP", "RFC",
            "NSS", "FECHA DE INGRESO", "FECHA ANTIGUEDAD","SALARIO DIARIO", "FACTOR DE INTEGRACION", "SALARIO DIARIO INTEGRADO",
            "BANCO", "NUMERO DE CUENTA", "CLABE", "NOMINA", "CLIENTE");
        $fechas = array("FECHA DE INGRESO", "FECHA ANTIGUEDAD");

        $empleados_excel = Importador::getInstance()
            ->leer_registros(ruta_absoluta: $doc_documento->registro['doc_documento_ruta_absoluta'], columnas: $columnas,
                fechas: $fechas);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al leer archivo de anticipos', data: $empleados_excel);
            if (!$header) {
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $this->link->beginTransaction();

        foreach ($empleados_excel as $empleado){
            $empleado = (array)$empleado;

            $registros_empleado['codigo'] = $empleado['CODIGO'];
            $registros_empleado['nombre'] = $empleado['NOMBRE'];
            $registros_empleado['ap'] = $empleado['APELLIDO PATERNO'];
            $registros_empleado['am'] = $empleado['APELLIDO MATERNO'];
            $registros_empleado['telefono'] = $empleado['TELEFONO'];
            $registros_empleado['curp'] = $empleado['CURP'];
            $registros_empleado['rfc'] = $empleado['RFC'];
            $registros_empleado['nss'] = $empleado['NSS'];
            $registros_empleado['fecha_inicio_rel_laboral'] = $empleado['FECHA DE INGRESO'];
            $registros_empleado['fecha_antiguedad'] = $empleado['FECHA ANTIGUEDAD'];
            $registros_empleado['salario_diario'] = $empleado['SALARIO DIARIO'];

            $filtro_nom_conf_nomina['nom_conf_nomina.descripcion_select'] = strtoupper($empleado['NOMINA']);
            $nom_conf_nomina = (new nom_conf_nomina($this->link))->filtro_and(columnas: array('nom_conf_nomina_id'),
                filtro: $filtro_nom_conf_nomina, limit: 1);
            if (errores::$error) {
                $error = $this->errores->error(mensaje: 'Error al obtener la conf. de nomina', data: $nom_conf_nomina);
                if (!$header) {
                    return $error;
                }
                print_r($error);
                die('Error');
            }

            if ($nom_conf_nomina->n_registros <= 0){
                $error = $this->errores->error(mensaje: 'Error no existe la conf. de nomina', data: $empleado['NOMINA']);
                if (!$header) {
                    return $error;
                }
                print_r($error);
                die('Error');
            }

            $filtro_bn_sucursal['bn_sucursal.descripcion'] = strtoupper($empleado['BANCO']);
            $bn_sucursal = (new bn_sucursal($this->link))->filtro_and(columnas: array('bn_sucursal_id'),
                filtro: $filtro_bn_sucursal, limit: 1);
            if (errores::$error) {
                $error = $this->errores->error(mensaje: 'Error al obtener banco', data: $bn_sucursal);
                if (!$header) {
                    return $error;
                }
                print_r($error);
                die('Error');
            }

            if ($bn_sucursal->n_registros <= 0){
                $error = $this->errores->error(mensaje: 'Error no existe el banco', data: $empleado['BANCO']);
                if (!$header) {
                    return $error;
                }
                print_r($error);
                die('Error');
            }

            $registros_empleado['com_sucursal_id'] = $empleado['CLIENTE'];
            $registros_empleado['nom_conf_nomina_id'] = $nom_conf_nomina->registros[0]['nom_conf_nomina_id'];
            $registros_empleado['bn_sucursal_id'] = $bn_sucursal->registros[0]['bn_sucursal_id'];
            $registros_empleado['num_cuenta'] = $empleado['NUMERO DE CUENTA'];
            $registros_empleado['clabe'] = $empleado['CLABE'];

            $alta_empleado = (new em_empleado($this->link))->alta_registro(registro: $registros_empleado);
            if (errores::$error) {
                $this->link->rollBack();
                $error = $this->errores->error(mensaje: 'Error al dar de alta empleado', data: $alta_empleado);
                if (!$header) {
                    return $error;
                }
                print_r($error);
                die('Error');
            }
        }

        $this->link->commit();

        header('Location:' . $this->link_lista);
        exit;
    }

    public function nominas(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        $seccion = "nom_nomina";

        $data_view = new stdClass();
        $data_view->names = array('Id', 'Fecha Inicio', 'Fecha Final', 'Fecha Pago', 'Percepcion Total', 'Otro Pago Total',
            'Deduccion Total', 'Total', 'Tipo Nomina', 'RFC Empresa');
        $data_view->keys_data = array($seccion . "_id",$seccion . "_fecha_inicial_pago",$seccion . "_fecha_final_pago",
            $seccion . "_fecha_pago",$seccion . "_total_percepcion_total",$seccion . "_total_otro_pago_total",
            $seccion . "_total_deduccion_total",$seccion . "_total","cat_sat_tipo_nomina_descripcion","org_empresa_rfc");
        $data_view->key_actions = 'acciones';
        $data_view->namespace_model = 'gamboamartin\\nomina\\models';
        $data_view->name_model_children = $seccion;

        $contenido_table = $this->contenido_children(data_view: $data_view, next_accion: __FUNCTION__,
            not_actions: $not_actions);
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al obtener tbody', data: $contenido_table, header: $header, ws: $ws);
        }

        return $contenido_table;
    }

}
