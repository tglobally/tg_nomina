<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace tglobally\tg_nomina\controllers;

use gamboamartin\empleado\models\em_anticipo;
use gamboamartin\empleado\models\em_empleado;
use gamboamartin\errores\errores;
use gamboamartin\system\actions;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\em_anticipo_html;
use html\tg_manifiesto_html;
use models\doc_documento;
use models\im_registro_patronal;
use models\nom_conf_empleado;
use models\nom_incidencia;
use models\nom_periodo;
use models\tg_manifiesto;
use models\tg_manifiesto_periodo;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use stdClass;

class controlador_tg_manifiesto extends system
{
    public controlador_tg_manifiesto_periodo $controlador_tg_manifiesto_periodo;
    public array $keys_selects = array();
    public stdClass $periodos;

    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new tg_manifiesto(link: $link);
        $html_ = new tg_manifiesto_html(html: $html);
        $obj_link = new links_menu($this->registro_id);
        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, paths_conf: $paths_conf);

        $this->titulo_lista = 'Manifiesto';
        $this->controlador_tg_manifiesto_periodo= new controlador_tg_manifiesto_periodo($this->link);

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

        $inputs = (new tg_manifiesto_html(html: $this->html_base))->genera_inputs(controler: $this,
            keys_selects: $this->keys_selects);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

    private function asigna_link_sube_manifiesto_row(stdClass $row): array|stdClass
    {
        $keys = array('tg_manifiesto_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $row);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar row',data:  $valida);
        }

        $link_sube_manifiesto = $this->obj_link->link_con_id(accion:'sube_manifiesto',registro_id:  $row->tg_manifiesto_id,
            seccion:  $this->tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera link',data:  $link_sube_manifiesto);
        }

        $row->link_sube_manifiesto = $link_sube_manifiesto;

        return $row;
    }

    private function base(): array|stdClass
    {
        $r_modifica =  parent::modifica(header: false,aplica_form:  false);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
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

    private function data_periodo_btn(array $periodo): array
    {
        $params['tg_manifiesto_periodo_id'] = $periodo['tg_manifiesto_periodo_id'];

        $btn_elimina = $this->html_base->button_href(accion: 'abono_elimina_bd', etiqueta: 'Elimina',
            registro_id: $this->registro_id, seccion: 'em_empleado', style: 'danger',params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_elimina);
        }
        $periodo['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion: 'abono_modifica', etiqueta: 'Modifica',
            registro_id: $this->registro_id, seccion: 'em_empleado', style: 'warning',params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_modifica);
        }
        $periodo['link_modifica'] = $btn_modifica;

        return $periodo;
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
        $doc_documento_modelo = new doc_documento($this->link);
        $doc_documento_modelo->registro['doc_tipo_documento_id'] = 1;
        $doc_documento = $doc_documento_modelo->alta_bd(file: $_FILES['archivo']);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al dar de alta el documento', data: $doc_documento);
        }

        $empleados_excel = $this->obten_empleados_excel(ruta_absoluta: $doc_documento->registro['doc_documento_ruta_absoluta']);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener empleados',data:  $empleados_excel);
        }

        $im_registro_patronal = $this->obten_registro_patronal(tg_manifiesto_id: $this->registro_id);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener registro patronal',data:  $im_registro_patronal);
        }

        $im_registro_patronal_id = $im_registro_patronal['im_registro_patronal_id'];
        $empleados = array();
        foreach ($empleados_excel as $empleado_excel){
            $filtro['im_registro_patronal.id'] = $im_registro_patronal_id;
            $filtro['em_empleado.codigo'] = $empleado_excel->codigo;
            $registro = (new em_empleado($this->link))->filtro_and(filtro: $filtro);
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al al obtener registro de empleado', data: $registro);
            }
            if($registro->n_registros > 0){
                $empleados[] = $registro->registros[0];
            }
        }

        $filtro_per['tg_manifiesto.id'] = $this->registro_id;
        $nom_periodos = (new tg_manifiesto_periodo($this->link))->filtro_and(filtro: $filtro_per);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al al obtener periodos', data: $nom_periodos);
        }

        foreach ($nom_periodos->registros  as $nom_periodo) {
            $empleados_res = array();
            foreach ($empleados as $empleado) {
                $filtro_em['em_empleado.id'] = $empleado['em_empleado_id'];
                $filtro_em['cat_sat_periodicidad_pago_nom.id'] = $nom_periodo['nom_periodo_cat_sat_periodicidad_pago_nom_id'];
                $conf_empleado = (new nom_conf_empleado($this->link))->filtro_and(filtro: $filtro_em);
                if (errores::$error) {
                    return $this->errores->error(mensaje: 'Error al obtener configuracion de empleado',
                        data: $conf_empleado);
                }

                if (isset($conf_empleado->registros[0])) {
                    $empleados_res[] = $conf_empleado->registros[0];
                }
            }

            foreach ($empleados_res as $empleado) {
                foreach ($empleados_excel as $empleado_excel) {
                    if ((string)$empleado_excel->codigo === (string)$empleado['em_empleado_codigo']
                     && (int)$empleado_excel->faltas > 0) {
                        $registro_inc['nom_tipo_incidencia_id'] = 1;
                        $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                        $registro_inc['n_dias'] = $empleado_excel->faltas;

                        $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                        if (errores::$error) {
                            return $this->errores->error(mensaje: 'Error al dar de alta incidencias',
                                data: $nom_incidencia);
                        }
                    }
                }

                $alta_empleado = (new nom_periodo($this->link))->alta_empleado_periodo(empleado: $empleado,
                    nom_periodo: $nom_periodo);
                if (errores::$error) {
                    return $this->errores->error(mensaje: 'Error al dar de alta la nomina del empleado',
                        data: $alta_empleado);
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
        }
        return $registros;
    }

    public function modifica(bool $header, bool $ws = false, string $breadcrumbs = '', bool $aplica_form = true,
                             bool $muestra_btn = true): array|string
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

    public function obten_empleados_excel(string $ruta_absoluta){
        $documento = IOFactory::load($ruta_absoluta);
        $totalDeHojas = $documento->getSheetCount();

        $columna_faltas = $this->obten_columna_faltas(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de faltas',data:  $columna_faltas);
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
                $reg->faltas = $hojaActual->getCell($columna_faltas.$registro->fila)->getValue();
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

    public function sube_manifiesto(bool $header, bool $ws = false){
    $r_modifica =  parent::modifica(header: false,aplica_form:  false); // TODO: Change the autogenerated stub
    if(errores::$error){
        return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
    }

    return $r_modifica;
}

}
