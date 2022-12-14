<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace tglobally\tg_nomina\controllers;

use base\orm\inicializacion;
use gamboamartin\empleado\models\em_empleado;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_csd;
use gamboamartin\plugins\exportador;
use gamboamartin\system\actions;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\tg_manifiesto_html;
use gamboamartin\documento\models\doc_documento;
use models\im_registro_patronal;
use models\nom_conf_empleado;
use models\nom_incidencia;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_percepcion;
use models\nom_percepcion;
use models\nom_periodo;
use models\tg_manifiesto;
use models\tg_manifiesto_periodo;
use models\tg_sucursal_alianza;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use stdClass;

class controlador_tg_manifiesto extends system
{
    public controlador_tg_manifiesto_periodo $controlador_tg_manifiesto_periodo;
    public array $keys_selects = array();
    public stdClass $periodos;
    public int $tg_manifiesto_periodo_id = -1;
    public array $nominas = array();

    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new tg_manifiesto(link: $link);
        $html_ = new tg_manifiesto_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);
        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, paths_conf: $paths_conf);

        $this->titulo_lista = 'Manifiesto';
        $this->controlador_tg_manifiesto_periodo= new controlador_tg_manifiesto_periodo($this->link);

        if (isset($_GET['tg_manifiesto_periodo_id'])){
            $this->tg_manifiesto_periodo_id = $_GET['tg_manifiesto_periodo_id'];
        }

        $this->asignar_propiedad(identificador: 'fc_csd_id', propiedades: ["label" => "CSD"]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador: 'tg_tipo_servicio_id', propiedades: ["label" => "Tipo Servicio"]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador: 'com_sucursal_id', propiedades: ["label" => "Sucursal"]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador: 'tg_cte_alianza_id', propiedades: ["label" => "Alianza"]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador: 'fecha_inicial_pago', propiedades: ["place_holder" => "Fecha Inicial Pago"]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador: 'fecha_final_pago', propiedades: ["place_holder" => "Fecha Final Pago"]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador: 'fecha_envio', propiedades: ["place_holder" => "Fecha Envio"]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador: 'fecha_pago', propiedades: ["place_holder" => "Fecha Pago"]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

    }

    public function asignar_propiedad(string $identificador, mixed $propiedades)
    {
        if (!array_key_exists($identificador, $this->keys_selects)) {
            $this->keys_selects[$identificador] = new stdClass();
        }

        foreach ($propiedades as $key => $value) {
            $this->keys_selects[$identificador]->$key = $value;
        }
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta = parent::alta(header: false);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $r_alta, header: $header, ws: $ws);
        }

        $this->row_upd->fecha_envio = date('Y-m-d');
        $this->row_upd->fecha_pago = date('Y-m-d');
        $this->row_upd->fecha_inicial_pago = date('Y-m-d');
        $this->row_upd->fecha_final_pago = date('Y-m-d');

        $inputs = (new tg_manifiesto_html(html: $this->html_base))->genera_inputs(controler: $this,
            keys_selects: $this->keys_selects);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

    private function asigna_link_descarga_nomina_row(stdClass $row): array|stdClass
    {
        $keys = array('tg_manifiesto_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $row);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar row',data:  $valida);
        }

        $link_descarga_nomina = $this->obj_link->link_con_id(accion:'descarga_nomina',link:$this->link,
            registro_id:  $row->tg_manifiesto_id, seccion:  $this->tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera link',data:  $link_descarga_nomina);
        }

        $row->link_descarga_nomina = $link_descarga_nomina;

        return $row;
    }

    private function asigna_link_sube_manifiesto_row(stdClass $row): array|stdClass
    {
        $keys = array('tg_manifiesto_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $row);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar row',data:  $valida);
        }

        $link_sube_manifiesto = $this->obj_link->link_con_id(accion:'sube_manifiesto',link: $this->link,
            registro_id:  $row->tg_manifiesto_id, seccion:  $this->tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera link',data:  $link_sube_manifiesto);
        }

        $row->link_sube_manifiesto = $link_sube_manifiesto;

        return $row;
    }

    private function asigna_link_ve_nominas_row(stdClass $row): array|stdClass
    {
        $keys = array('tg_manifiesto_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $row);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar row',data:  $valida);
        }

        $link_ve_nominas = $this->obj_link->link_con_id(accion:'ve_nominas',link: $this->link,
            registro_id:  $row->tg_manifiesto_id, seccion:  $this->tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera link',data:  $link_ve_nominas);
        }

        $row->link_ve_nominas = $link_ve_nominas;

        return $row;
    }

    private function base(): array|stdClass
    {
        $r_modifica =  parent::modifica(header: false,ws:  false);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
        }

        $tg_manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $this->registro_id);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error obtener registro manifiesto', data: $tg_manifiesto);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'com_sucursal_id',
            propiedades: ["id_selected"=>$tg_manifiesto['com_sucursal_id']]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'tg_cte_alianza_id',
            propiedades: ["id_selected"=>$tg_manifiesto['tg_cte_alianza_id']]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'fc_csd_id',
            propiedades: ["id_selected"=>$this->row_upd->fc_csd_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'tg_tipo_servicio_id',
            propiedades: ["id_selected"=>$this->row_upd->tg_tipo_servicio_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $inputs = (new tg_manifiesto_html(html: $this->html_base))->genera_inputs(controler: $this,
            keys_selects: $this->keys_selects);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar inputs',data:  $inputs);
        }

        $data = new stdClass();
        $data->template = $r_modifica;
        $data->inputs = $inputs;

        return $data;
    }

    private function data_nomina_btn(array $nomina): array
    {
        $btn_elimina = $this->html_base->button_href(accion: 'elimina_nomina_bd', etiqueta: 'Elimina',
            registro_id: $nomina['nom_nomina_id'], seccion: 'tg_manifiesto', style: 'danger');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_elimina);
        }
        $nomina['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion: 'modifica_nomina', etiqueta: 'Modifica',
            registro_id: $nomina['nom_nomina_id'], seccion: 'tg_manifiesto', style: 'warning');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_modifica);
        }
        $nomina['link_modifica'] = $btn_modifica;

        return $nomina;
    }

    private function data_periodo_btn(array $periodo): array
    {
        $params['tg_manifiesto_periodo_id'] = $periodo['tg_manifiesto_periodo_id'];

        $btn_elimina = $this->html_base->button_href(accion: 'periodo_elimina_bd', etiqueta: 'Elimina',
            registro_id: $this->registro_id, seccion: 'tg_manifiesto', style: 'danger',params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_elimina);
        }
        $periodo['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion: 'periodo_modifica', etiqueta: 'Modifica',
            registro_id: $this->registro_id, seccion: 'tg_manifiesto', style: 'warning',params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_modifica);
        }
        $periodo['link_modifica'] = $btn_modifica;

        return $periodo;
    }

    public function descarga_nomina(bool $header, bool $ws = false): array|stdClass
    {

        $nominas = (new tg_manifiesto_periodo($this->link))->nominas_by_manifiesto(tg_manifiesto_id: $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener nominas del periodo',data:  $nominas,
                header: $header,ws:$ws);
        }

        $conceptos = (new nom_nomina($this->link))->obten_conceptos_nominas(nominas: $nominas);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener nominas del periodo',data:  $conceptos,
                header: $header,ws:$ws);
        }

        $exportador = (new exportador());
        $registros_xls = array();

        foreach ($nominas as $nomina){
            $row = (new nom_nomina($this->link))->maqueta_registros_excel(nom_nomina_id: $nomina['nom_nomina_id'],
                conceptos_nomina: $conceptos);
            if(errores::$error){
                return $this->retorno_error(mensaje: 'Error al maquetar datos de la nomina',data:  $row,
                    header: $header,ws:$ws);
            }
            $registros_xls[] = $row;
        }

        $keys = array();

        foreach (array_keys($registros_xls[0]) as $key) {
            $keys[$key] = strtoupper(str_replace('_', ' ', $key));
        }

        $registros = array();

        foreach ($registros_xls as $row) {
            $registros[] = array_combine(preg_replace(array_map(function($s){return "/^$s$/";},
                array_keys($keys)),$keys, array_keys($row)), $row);

        }

        $resultado = $exportador->listado_base_xls(header: $header, name: $this->seccion, keys:  $keys,
            path_base: $this->path_base,registros:  $registros,totales:  array());
        if(errores::$error){
            $error =  $this->errores->error('Error al generar xls',$resultado);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }


        exit;
        //return $this->nominas;
    }

    public function lista(bool $header, bool $ws = false): array
    {
        $r_lista = parent::lista($header, $ws); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar datos',data:  $r_lista, header: $header,ws:$ws);
        }

        $registros = $this->maqueta_registros_lista(registros: $this->registros);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar registros',data:  $registros, header: $header,ws:$ws);
        }
        $this->registros = $registros;



        return $r_lista;
    }

    /**
     * @throws \JsonException
     */
    public function lee_archivo(bool $header, bool $ws = false)
    {
        $tg_manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $this->registro_id);
        if (errores::$error) {
            $error =  $this->errores->error(mensaje: 'Error al obtener manifiesto', data: $tg_manifiesto);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }
        $doc_documento_modelo = new doc_documento($this->link);
        $doc_documento_modelo->registro['descripcion'] = $tg_manifiesto['tg_manifiesto_descripcion'];
        $doc_documento_modelo->registro['descripcion_select'] = $tg_manifiesto['tg_manifiesto_descripcion'];
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

        $empleados_excel = $this->obten_empleados_excel(ruta_absoluta: $doc_documento->registro['doc_documento_ruta_absoluta']);
        if (errores::$error) {
            $error =  $this->errores->error(mensaje: 'Error obtener empleados',data:  $empleados_excel);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $im_registro_patronal = $this->obten_registro_patronal(tg_manifiesto_id: $this->registro_id);
        if (errores::$error) {
            $error =  $this->errores->error(mensaje: 'Error obtener registro patronal',data:  $im_registro_patronal);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $im_registro_patronal_id = $im_registro_patronal['im_registro_patronal_id'];
        $empleados = array();
        foreach ($empleados_excel as $empleado_excel){
            $filtro['im_registro_patronal.id'] = $im_registro_patronal_id;
            $filtro['em_empleado.nombre'] = $empleado_excel->nombre;
            $filtro['em_empleado.ap'] = $empleado_excel->ap;
            $filtro['em_empleado.am'] = $empleado_excel->am;

            $registro = (new em_empleado($this->link))->filtro_and(filtro: $filtro);
            if (errores::$error) {
                $error =  $this->errores->error(mensaje: 'Error al al obtener registro de empleado', data: $registro);
                if(!$header){
                    return $error;
                }
                print_r($error);
                die('Error');
            }
            if ($registro->n_registros === 0) {
                $error =  $this->errores->error(mensaje: 'Error se encontro el empleado '.$empleado_excel->nombre.' '.
                    $empleado_excel->ap.' '.$empleado_excel->am, data: $registro);
                if(!$header){
                    return $error;
                }
                print_r($error);
                die('Error');
            }
            if($registro->n_registros > 0){
                $empleados[] = $registro->registros[0];
            }
        }

        $filtro_per['tg_manifiesto.id'] = $this->registro_id;
        $nom_periodos = (new tg_manifiesto_periodo($this->link))->filtro_and(filtro: $filtro_per);
        if (errores::$error) {
            $error =  $this->errores->error(mensaje: 'Error al al obtener periodos', data: $nom_periodos);
            if(!$header){
                return $error;
            }
            print_r($error);
            die('Error');
        }

        foreach ($nom_periodos->registros  as $nom_periodo) {
            $empleados_res = array();
            foreach ($empleados as $empleado) {
                $filtro_em['em_empleado.id'] = $empleado['em_empleado_id'];
                $filtro_em['cat_sat_periodicidad_pago_nom.id'] = $nom_periodo['nom_periodo_cat_sat_periodicidad_pago_nom_id'];
                $conf_empleado = (new nom_conf_empleado($this->link))->filtro_and(filtro: $filtro_em);
                if (errores::$error) {
                    $error =  $this->errores->error(mensaje: 'Error al obtener configuracion de empleado',
                        data: $conf_empleado);
                    if(!$header){
                        return $error;
                    }
                    print_r($error);
                    die('Error');
                }

                if (isset($conf_empleado->registros[0])) {
                    $empleados_res[] = $conf_empleado->registros[0];
                }
            }

            foreach ($empleados_res as $empleado) {
                foreach ($empleados_excel as $empleado_excel) {
                    if(trim($empleado_excel->nombre) === trim($empleado['em_empleado_nombre']) &&
                        trim($empleado_excel->ap) === trim($empleado['em_empleado_ap']) &&
                        trim($empleado_excel->am) === trim($empleado['em_empleado_am'])) {

                        if ((int)$empleado_excel->faltas > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 1;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->faltas;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                        if ((int)$empleado_excel->prima_dominical > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 2;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->prima_dominical;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                        if ((int)$empleado_excel->dias_festivos_laborados > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 3;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->dias_festivos_laborados;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                        if ((int)$empleado_excel->incapacidades > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 4;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->incapacidades;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                        if ((int)$empleado_excel->vacaciones > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 5;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->vacaciones;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                        if ((int)$empleado_excel->dias_descanso_laborado > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 6;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->dias_descanso_laborado;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                $error = $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                                if (!$header) {
                                    return $error;
                                }
                                print_r($error);
                                die('Error');
                            }
                        }
                    }
                }

                $alta_empleado = (new nom_periodo($this->link))->alta_empleado_periodo(empleado: $empleado,
                    nom_periodo: $nom_periodo);
                if (errores::$error) {
                    $error =  $this->errores->error(mensaje: 'Error al dar de alta la nomina del empleado',
                        data: $alta_empleado);
                    if(!$header){
                        return $error;
                    }
                    print_r($error);
                    die('Error');
                }
                
                foreach ($empleados_excel as $empleado_excel) {
                    if (trim($empleado_excel->nombre) === trim($empleado['em_empleado_nombre']) &&
                        trim($empleado_excel->ap) === trim($empleado['em_empleado_ap']) &&
                        trim($empleado_excel->am) === trim($empleado['em_empleado_am'])) {
                        if ($empleado_excel->compensacion > 0) {
                            $nom_percepcion = (new nom_percepcion($this->link))->get_aplica_compensacion();
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error insertar conceptos', data: $nom_percepcion);
                            }

                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = $nom_percepcion['nom_percepcion_id'];
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->compensacion;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }

                        if ($empleado_excel->prima_vacacional > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 12;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->prima_vacacional;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }

                        if ($empleado_excel->despensa > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 4;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->despensa;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->horas_extras_dobles > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 13;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->horas_extras_dobles;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->gratificacion_especial > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 14;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->gratificacion_especial;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->premio_puntualidad > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 15;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->premio_puntualidad;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->premio_asistencia > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 16;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->premio_asistencia;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->ayuda_transporte > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 17;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->ayuda_transporte;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->gratificacion > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 18;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->gratificacion;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->seguro_vida > 0) {
                            $nom_par_deduccion_sep = array();
                            $nom_par_deduccion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_deduccion_sep['nom_deduccion_id'] = 5;
                            $nom_par_deduccion_sep['importe_gravado'] = $empleado_excel->seguro_vida;

                            $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $nom_par_deduccion_sep);
                            if (errores::$error) {
                                return $this->errores->error(mensaje: 'Error al insertar deduccion default', data: $r_alta_nom_par_deduccion);
                            }
                        }
                    }
                }

            }
        }

        $link = "./index.php?seccion=tg_manifiesto&accion=lista&registro_id=".$this->registro_id;
        $link.="&session_id=$this->session_id";
        header('Location:' . $link);
        exit;
    }

    private function maqueta_registros_lista(array $registros): array
    {
        foreach ($registros as $indice=> $row){
            $row = $this->asigna_link_sube_manifiesto_row(row: $row);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al maquetar row',data:  $row);
            }

            $registros[$indice] = $row;

            $row = $this->asigna_link_ve_nominas_row(row: $row);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al maquetar row',data:  $row);
            }

            $row = $this->asigna_link_descarga_nomina_row(row: $row);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al maquetar row',data:  $row);
            }

            $registros[$indice] = $row;

        }
        return $registros;
    }

    public function modifica(bool $header, bool $ws = false): array|stdClass
    {
        $base = $this->base();
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar datos',data:  $base,
                header: $header,ws:$ws);
        }

        return $base->template;
    }

    public function obten_columna_faltas(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'FALTAS') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_prima_dominical(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'D??AS DE PRIMA DOMINICAL') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_dias_festivos_laborados(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'FESTIVO LABORADO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_dias_descanso_laborado(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'DESCANSO LABORADO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_compensacion(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'COMPENSACION') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_despensa(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'DESPENSA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_horas_extras_dobles(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'HORAS EXTRAS DOBLES') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_gratificacion_especial(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'GRATIFICACION ESPECIAL') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_premio_puntualidad(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'PREMIO PUNTUALIDAD') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_premio_asistencia(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'PREMIO ASISTENCIA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_ayuda_transporte(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'AYUDA TRANSPORTE') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_gratificacion(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'GRATIFICACION') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }
    
    public function obten_columna_seguro_vida(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'SEGURO DE VIDA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_incapacidades(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'INCAPACIDAD') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_vacaciones(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'D??AS DE VACACIONES') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_prima_vacacional(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'PRIMA VACACIONAL') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_empleados_excel(string $ruta_absoluta){
        $documento = IOFactory::load($ruta_absoluta);
        $totalDeHojas = $documento->getSheetCount();

        $columna_faltas = $this->obten_columna_faltas(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de faltas',data:  $columna_faltas);
        }

        $columna_prima_dominical = $this->obten_columna_prima_dominical(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de prima dominical',
                data:  $columna_prima_dominical);
        }

        $columna_dias_festivos_laborados = $this->obten_columna_dias_festivos_laborados(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de dias festivos laborados',
                data:  $columna_dias_festivos_laborados);
        }

        $columna_incapacidades = $this->obten_columna_incapacidades(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de incapacidades',
                data:  $columna_incapacidades);
        }

        $columna_vacaciones = $this->obten_columna_vacaciones(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de vacaciones',
                data:  $columna_vacaciones);
        }

        $columna_dias_descanso_laborado = $this->obten_columna_dias_descanso_laborado(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de vacaciones',
                data:  $columna_dias_descanso_laborado);
        }

        $columna_compensacion = $this->obten_columna_compensacion(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de compensacion',
                data:  $columna_compensacion);
        }

        $columna_prima_vacacional = $this->obten_columna_prima_vacacional(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de prima vacacional',
                data:  $columna_prima_vacacional);
        }

        $columna_despensa = $this->obten_columna_despensa(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de despensa',
                data:  $columna_despensa);
        }

        $columna_horas_extras_dobles = $this->obten_columna_horas_extras_dobles(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de horas_extras_dobles',
                data:  $columna_horas_extras_dobles);
        }

        $columna_gratificacion_especial = $this->obten_columna_gratificacion_especial(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de gratificacion_especial',
                data:  $columna_gratificacion_especial);
        }

        $columna_premio_puntualidad = $this->obten_columna_premio_puntualidad(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de premio puntualidad',
                data:  $columna_premio_puntualidad);
        }

        $columna_premio_asistencia = $this->obten_columna_premio_asistencia(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de premio_asistencia',
                data:  $columna_premio_asistencia);
        }

        $columna_ayuda_transporte = $this->obten_columna_ayuda_transporte(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de ayuda transporte',
                data:  $columna_ayuda_transporte);
        }

        $columna_gratificacion = $this->obten_columna_gratificacion(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de gratificacion',
                data:  $columna_gratificacion);
        }

        $columna_seguro_vida = $this->obten_columna_seguro_vida(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de seguro_vida',
                data:  $columna_seguro_vida);
        }

        $empleados = array();
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            $registros = array();
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $fila = $celda->getRow();
                    $valorRaw = $celda->getValue();
                    $columna = $celda->getColumn();

                    if($fila >= 7){
                        if($columna === "A" && is_numeric($valorRaw)){
                            $reg = new stdClass();
                            $reg->fila = $fila;
                            $registros[] = $reg;
                        }
                    }
                }
            }

            foreach ($registros as $registro){
                $reg = new stdClass();
                $reg->codigo = $hojaActual->getCell('A'.$registro->fila)->getValue();
                $reg->nombre = $hojaActual->getCell('B'.$registro->fila)->getValue();
                $reg->ap = $hojaActual->getCell('C'.$registro->fila)->getValue();
                $reg->am = $hojaActual->getCell('D'.$registro->fila)->getValue();
                $reg->faltas = 0;
                $reg->prima_dominical = 0;
                $reg->dias_festivos_laborados = 0;
                $reg->incapacidades = 0;
                $reg->vacaciones = 0;
                $reg->dias_descanso_laborado = 0;
                $reg->compensacion = 0;

                $reg->prima_vacacional = 0;
                $reg->despensa = 0;
                $reg->seguro_vida = 0;
                $reg->horas_extras_dobles = 0;
                $reg->gratificacion_especial = 0;
                $reg->premio_puntualidad = 0;
                $reg->premio_asistencia = 0;
                $reg->ayuda_transporte = 0;
                $reg->gratificacion = 0;

                if($columna_faltas !== -1) {
                    $reg->faltas = $hojaActual->getCell($columna_faltas . $registro->fila)->getValue();
                    if(!is_numeric($reg->faltas)){
                        $reg->faltas = 0;
                    }
                } 
                
                if($columna_prima_dominical !== -1) {
                    $reg->prima_dominical = $hojaActual->getCell($columna_prima_dominical . $registro->fila)->getValue();
                    if(!is_numeric($reg->prima_dominical)){
                        $reg->prima_dominical = 0;
                    }
                }
                
                if($columna_dias_festivos_laborados !== -1) {
                    $reg->dias_festivos_laborados = $hojaActual->getCell($columna_dias_festivos_laborados . $registro->fila)->getValue();
                    if(!is_numeric($reg->dias_festivos_laborados)){
                        $reg->dias_festivos_laborados = 0;
                    }
                }
                
                if($columna_incapacidades !== -1) {
                    $reg->incapacidades = $hojaActual->getCell($columna_incapacidades . $registro->fila)->getValue();
                    if(!is_numeric($reg->incapacidades)){
                        $reg->incapacidades = 0;
                    }
                }     
                
                if($columna_vacaciones !== -1) {
                    $reg->vacaciones = $hojaActual->getCell($columna_vacaciones . $registro->fila)->getValue();
                    if(!is_numeric($reg->vacaciones)){
                        $reg->vacaciones = 0;
                    }
                }
                if($columna_dias_descanso_laborado !== -1) {
                    $reg->dias_descanso_laborado = $hojaActual->getCell($columna_dias_descanso_laborado . $registro->fila)->getValue();
                    if(!is_numeric($reg->dias_descanso_laborado)){
                        $reg->dias_descanso_laborado = 0;
                    }
                }
                if($columna_compensacion !== -1) {
                    $compensacion = $hojaActual->getCell($columna_compensacion . $registro->fila)->getCalculatedValue();
                    $reg->compensacion = trim((string)$compensacion);

                    if(!is_numeric($reg->compensacion)){
                        $reg->compensacion = 0;
                    }
                }

                if($columna_prima_vacacional !== -1) {
                    $prima_vacacional = $hojaActual->getCell($columna_prima_vacacional . $registro->fila)->getCalculatedValue();
                    $reg->prima_vacacional = trim((string)$prima_vacacional);

                    if(!is_numeric($reg->prima_vacacional)){
                        $reg->prima_vacacional = 0;
                    }
                }
                if($columna_despensa !== -1) {
                    $despensa = $hojaActual->getCell($columna_despensa. $registro->fila)->getCalculatedValue();
                    $reg->despensa = trim((string)$despensa);

                    if(!is_numeric($reg->despensa)){
                        $reg->despensa = 0;
                    }
                }
                if($columna_seguro_vida !== -1) {
                    $seguro_vida = $hojaActual->getCell($columna_seguro_vida. $registro->fila)->getCalculatedValue();
                    $reg->seguro_vida = trim((string)$seguro_vida);

                    if(!is_numeric($reg->seguro_vida)){
                        $reg->seguro_vida = 0;
                    }
                }
                if($columna_horas_extras_dobles !== -1) {
                    $horas_extras_dobles = $hojaActual->getCell($columna_horas_extras_dobles . $registro->fila)->getCalculatedValue();
                    $reg->horas_extras_dobles = trim((string)$horas_extras_dobles);

                    if(!is_numeric($reg->horas_extras_dobles)){
                        $reg->horas_extras_dobles = 0;
                    }
                }
                if($columna_gratificacion_especial !== -1) {
                    $gratificacion_especial = $hojaActual->getCell($columna_gratificacion_especial . $registro->fila)->getCalculatedValue();
                    $reg->gratificacion_especial = trim((string)$gratificacion_especial);

                    if(!is_numeric($reg->gratificacion_especial)){
                        $reg->gratificacion_especial = 0;
                    }
                }
                if($columna_premio_puntualidad !== -1) {
                    $premio_puntualidad = $hojaActual->getCell($columna_premio_puntualidad. $registro->fila)->getCalculatedValue();
                    $reg->premio_puntualidad = trim((string)$premio_puntualidad);

                    if(!is_numeric($reg->premio_puntualidad)){
                        $reg->premio_puntualidad = 0;
                    }
                }
                if($columna_premio_asistencia !== -1) {
                    $premio_asistencia = $hojaActual->getCell($columna_premio_asistencia . $registro->fila)->getCalculatedValue();
                    $reg->premio_asistencia = trim((string)$premio_asistencia);

                    if(!is_numeric($reg->premio_asistencia)){
                        $reg->premio_asistencia = 0;
                    }
                }
                if($columna_ayuda_transporte !== -1) {
                    $ayuda_transporte = $hojaActual->getCell($columna_ayuda_transporte . $registro->fila)->getCalculatedValue();
                    $reg->ayuda_transporte = trim((string)$ayuda_transporte);

                    if(!is_numeric($reg->ayuda_transporte)){
                        $reg->ayuda_transporte = 0;
                    }
                }
                if($columna_gratificacion !== -1) {
                    $gratificacion = $hojaActual->getCell($columna_gratificacion . $registro->fila)->getCalculatedValue();
                    $reg->gratificacion = trim((string)$gratificacion);

                    if(!is_numeric($reg->ayuda_transporte)){
                        $reg->gratificacion = 0;
                    }
                }
                $empleados[] = $reg;
            }
        }

        return $empleados;
    }


    public function obten_registro_patronal(int $tg_manifiesto_id){
        $tg_manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $tg_manifiesto_id);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener manifiesto',data:  $tg_manifiesto);
        }

        $filtro_im['fc_csd.id'] = $tg_manifiesto['tg_manifiesto_fc_csd_id'];
        $im_registro_patronal = (new im_registro_patronal($this->link))->filtro_and(filtro: $filtro_im);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener registro patronal',data:  $im_registro_patronal);
        }

        return $im_registro_patronal->registros[0];
    }

    public function periodo(bool $header, bool $ws = false): array|stdClass
    {
        $alta = $this->controlador_tg_manifiesto_periodo->alta(header: false);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $alta, header: $header, ws: $ws);
        }

        $this->controlador_tg_manifiesto_periodo->asignar_propiedad(identificador: 'tg_manifiesto_id',
            propiedades: ["id_selected" => $this->registro_id, "disabled" => true,
                "filtro" => array('tg_manifiesto.id' => $this->registro_id)]);

        $this->inputs = $this->controlador_tg_manifiesto_periodo->genera_inputs(
            keys_selects:  $this->controlador_tg_manifiesto_periodo->keys_selects);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $this->inputs);
            print_r($error);
            die('Error');
        }

        $periodos = (new tg_manifiesto_periodo($this->link))->get_periodos_manifiesto(tg_manifiesto_id: $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener periodos',data:  $periodos,header: $header,ws:$ws);
        }

        foreach ($periodos->registros as $indice => $periodo) {
            $periodo = $this->data_periodo_btn(periodo: $periodo);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al asignar botones', data: $periodo, header: $header, ws: $ws);
            }
            $periodos->registros[$indice] = $periodo;
        }

        $this->periodos = $periodos;

        return $this->inputs;
    }

    public function periodo_alta_bd(bool $header, bool $ws = false): array|stdClass
    {
        $this->link->beginTransaction();

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }
        $_POST['tg_manifiesto_id'] = $this->registro_id;

        $alta = (new tg_manifiesto_periodo($this->link))->alta_registro(registro: $_POST);
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al dar de alta periodo', data: $alta,
                header: $header, ws: $ws);
        }

        $this->link->commit();

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $alta,
                siguiente_view: "periodo", ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($alta, JSON_THROW_ON_ERROR);
            exit;
        }
        $alta->siguiente_view = "periodo";

        return $alta;
    }

    public function periodo_modifica(bool $header, bool $ws = false): array|stdClass
    {
        $this->controlador_tg_manifiesto_periodo->registro_id = $this->tg_manifiesto_periodo_id;

        $modifica = $this->controlador_tg_manifiesto_periodo->modifica(header: false);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar template',data:  $modifica, header: $header,ws:$ws);
        }

        $this->inputs = $this->controlador_tg_manifiesto_periodo->genera_inputs(
            keys_selects:  $this->controlador_tg_manifiesto_periodo->keys_selects);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar inputs',data:  $this->inputs);
            print_r($error);
            die('Error');
        }

        return $this->inputs;
    }

    public function periodo_modifica_bd(bool $header, bool $ws = false): array|stdClass
    {
        $this->link->beginTransaction();

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $registros = $_POST;

        $r_modifica = (new tg_manifiesto_periodo($this->link))->modifica_bd(registro: $registros,
            id: $this->tg_manifiesto_periodo_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al modificar abono', data: $r_modifica, header: $header, ws: $ws);
        }

        $this->link->commit();

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $r_modifica,
                siguiente_view: "periodo", ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($r_modifica, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_modifica->siguiente_view = "periodo";

        return $r_modifica;
    }

    public function periodo_elimina_bd(bool $header, bool $ws = false): array|stdClass
    {
        $this->link->beginTransaction();

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $r_elimina = (new tg_manifiesto_periodo($this->link))->elimina_bd(id: $this->tg_manifiesto_periodo_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al eliminar periodo', data: $r_elimina, header: $header,
                ws: $ws);
        }

        $this->link->commit();

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $r_elimina,
                siguiente_view: "periodo", ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($r_elimina, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_elimina->siguiente_view = "periodo";

        return $r_elimina;
    }

    public function sube_manifiesto(bool $header, bool $ws = false){
    $r_modifica =  parent::modifica(header: false,ws:  false); // TODO: Change the autogenerated stub
    if(errores::$error){
        return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
    }

    return $r_modifica;
}

    public function ve_nominas(bool $header, bool $ws = false): array|stdClass
    {



        $nominas = (new tg_manifiesto_periodo($this->link))->nominas_by_manifiesto(tg_manifiesto_id: $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener nominas del periodo',data:  $nominas,
                header: $header,ws:$ws);
        }

        foreach ($nominas as $indice => $nomina) {
            $nomina = $this->data_nomina_btn(nomina: $nomina);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al asignar botones', data: $nomina, header: $header, ws: $ws);
            }
            $nominas[$indice] = $nomina;
        }
        $this->nominas = $nominas;

        return $this->nominas;
    }

}
