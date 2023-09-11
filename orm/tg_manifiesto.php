<?php
namespace tglobally\tg_nomina\models;

use base\orm\_modelo_parent;

use config\generales;
use gamboamartin\comercial\models\com_sucursal;
use gamboamartin\documento\models\doc_documento;
use gamboamartin\empleado\models\em_registro_patronal;
use gamboamartin\errores\errores;

use gamboamartin\facturacion\models\fc_csd;
use gamboamartin\nomina\models\calcula_nomina;
use gamboamartin\nomina\models\em_empleado;
use gamboamartin\nomina\models\nom_conf_empleado;
use gamboamartin\nomina\models\nom_incidencia;
use gamboamartin\nomina\models\nom_par_deduccion;
use gamboamartin\nomina\models\nom_par_percepcion;
use gamboamartin\nomina\models\nom_percepcion;
use tglobally\tg_nomina\models\nom_periodo;
use gamboamartin\organigrama\models\org_departamento;
use gamboamartin\organigrama\models\org_sucursal;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use stdClass;
use tglobally\tg_nomina\controllers\controlador_tg_manifiesto;
use tglobally\tg_nomina\controllers\exportador_eliminar;

class tg_manifiesto extends _modelo_parent{

    public function __construct(PDO $link){
        $tabla = 'tg_manifiesto';
        $columnas = array($tabla=>false, 'tg_tipo_servicio' =>$tabla,'tg_sucursal_alianza'=>$tabla,'fc_csd'=>$tabla,
            'org_sucursal'=>$tabla, 'org_empresa'=>'org_sucursal','tg_agrupador'=>$tabla,
            'com_sucursal'=>'tg_sucursal_alianza', 'com_cliente'=>'com_sucursal',
            'tg_cte_alianza'=>'tg_sucursal_alianza', 'nom_conf_nomina'=>'tg_tipo_servicio');
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis',
            'fc_csd_id','tg_tipo_servicio_id','fecha_envio','fecha_pago');

        $campos_view['com_sucursal_id']['type'] = 'selects';
        $campos_view['com_sucursal_id']['model'] = (new com_sucursal($link));
        $campos_view['tg_cte_alianza_id']['type'] = 'selects';
        $campos_view['tg_cte_alianza_id']['model'] = (new tg_cte_alianza($link));
        $campos_view['tg_tipo_servicio_id']['type'] = 'selects';
        $campos_view['tg_tipo_servicio_id']['model'] = (new tg_tipo_servicio($link));
        $campos_view['org_sucursal_id']['type'] = 'selects';
        $campos_view['org_sucursal_id']['model'] = (new org_sucursal($link));
        $campos_view['tg_agrupador_id']['type'] = 'selects';
        $campos_view['tg_agrupador_id']['model'] = (new tg_agrupador($link));
        $campos_view['fecha_envio']['type'] = 'dates';
        $campos_view['fecha_pago']['type'] = 'dates';
        $campos_view['fecha_inicial_pago']['type'] = 'dates';
        $campos_view['fecha_final_pago']['type'] = 'dates';



        $columnas_extra['tg_manifiesto_n_nominas'] =
            "IFNULL ((SELECT COUNT(*) FROM  nom_nomina 
            INNER JOIN tg_manifiesto_periodo ON tg_manifiesto_periodo.tg_manifiesto_id = tg_manifiesto.id
            INNER JOIN nom_periodo ON nom_nomina.nom_periodo_id = tg_manifiesto_periodo.nom_periodo_id
            AND nom_nomina.nom_periodo_id = nom_periodo.id), 0)";

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view:  $campos_view,columnas_extra: $columnas_extra);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        $filtro_csd['org_sucursal.id'] = $this->registro['org_sucursal_id'];
        $fc_csd = (new fc_csd($this->link))->filtro_and(filtro: $filtro_csd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro empresa',data: $fc_csd);
        }

        if($fc_csd->n_registros < 1){
            return $this->error->error(mensaje: 'Error no existe registro de fc_csd relacionado',
                data: $fc_csd);
        }

        $fc_csd = $fc_csd->registros[0];

        $tg_sucursal_alianza = $this->obten_sucursal_alianza(com_sucursal_id: $this->registro['com_sucursal_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro sucursal alianza',data: $tg_sucursal_alianza);
        }

        $this->registro['tg_sucursal_alianza_id'] = $tg_sucursal_alianza['tg_sucursal_alianza_id'];

        if (!isset($this->registro['codigo'])) {
            $filtro_cod['tg_manifiesto.org_sucursal_id'] = $this->registro['org_sucursal_id'];
            $filtro_cod['tg_manifiesto.tg_sucursal_alianza_id'] = $this->registro['tg_sucursal_alianza_id'];

            $ultimo_registro_man = $this->filtro_and(filtro: $filtro_cod,order: array($this->tabla.'.id'=>'DESC'));
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener ultimo registro manifiesto',
                    data: $ultimo_registro_man);
            }

            $consecutivo = 1;
            $this->registro['codigo'] = $fc_csd['org_empresa_codigo'].$tg_sucursal_alianza['com_cliente_codigo'];
            $this->registro['codigo'] .= $consecutivo;

            if((int)$ultimo_registro_man->n_registros > 0){
                $buscar = array($fc_csd['org_empresa_codigo'], $tg_sucursal_alianza['com_cliente_codigo']);
                $consecutivo = str_replace($buscar,"",$ultimo_registro_man->registros[0][$this->tabla.'_codigo']);
                $consecutivo = $consecutivo + 1;

                $this->registro['codigo'] = $fc_csd['org_empresa_codigo'].$tg_sucursal_alianza['com_cliente_codigo'];
                $this->registro['codigo'] .= $consecutivo;
            }
        }

        if (!isset($this->registro['descripcion'])) {
            $this->registro['descripcion'] = $fc_csd['org_empresa_rfc'].' ';
                $this->registro['descripcion'] .= $this->registro['codigo'];
            }

            if (!isset($this->registro['descripcion_select'])) {
                $this->registro['descripcion_select'] = $this->registro['codigo'].' ';
            $this->registro['descripcion_select'] .= $tg_sucursal_alianza['com_cliente_rfc'].' ';
            $this->registro['descripcion_select'] .= $fc_csd['org_empresa_rfc'];
        }

        if (!isset($this->registro['codigo_bis'])) {
            $this->registro['codigo_bis'] = $this->registro['codigo'];
        }

        if (!isset($this->registro['alias'])) {
            $alias = $this->registro['codigo'].' ';
            $alias .= $tg_sucursal_alianza['com_cliente_rfc'].' ';
            $alias .= $fc_csd['org_empresa_rfc'];

            $this->registro['alias'] = strtoupper($alias);
        }

        if(!isset($this->registro['fc_csd_id']) || $this->registro['fc_csd_id'] === ''){
            $this->registro['fc_csd_id'] = $fc_csd['fc_csd_id'];
        }

        if(isset($this->registro['com_sucursal_id'])){
            unset($this->registro['com_sucursal_id']);
        }
        if(isset($this->registro['tg_cte_alianza_id'])){
            unset($this->registro['tg_cte_alianza_id']);
        }

        $this->registro['fecha_envio'] = date("Y/m/d");

        $r_alta_bd = parent::alta_bd(keys_integra_ds: $keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta manifiesto',data: $r_alta_bd);
        }

        $tg_tipo_servicio = (new tg_tipo_servicio($this->link))->registro(
            registro_id: $this->registro['tg_tipo_servicio_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener tipo de servicio',data: $tg_tipo_servicio);
        }

        $filtro_im['fc_csd.id'] = $this->registro['fc_csd_id'];
        $em_registro_patronal = (new em_registro_patronal($this->link))->filtro_and(filtro: $filtro_im);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error obtener registro patronal',data:  $em_registro_patronal);
        }

        if($em_registro_patronal->n_registros < 1){
            return $this->error->error(mensaje: 'Error no existe registro patronal relacionado',
                data:  $em_registro_patronal);
        }

        $registro_periodo['codigo'] = $this->registro['codigo'];
        $registro_periodo['descripcion'] = $this->registro['descripcion'];
        $registro_periodo['fecha_pago'] = $this->registro['fecha_pago'];
        $registro_periodo['fecha_inicial_pago'] = $this->registro['fecha_inicial_pago'];
        $registro_periodo['fecha_final_pago'] = $this->registro['fecha_final_pago'];
        $registro_periodo['cat_sat_periodicidad_pago_nom_id'] = $tg_tipo_servicio['cat_sat_periodicidad_pago_nom_id'];
        $registro_periodo['im_registro_patronal_id'] = $em_registro_patronal->registros[0]['em_registro_patronal_id'];
        $registro_periodo['em_registro_patronal_id'] = $em_registro_patronal->registros[0]['em_registro_patronal_id'];
        $registro_periodo['nom_tipo_periodo_id'] = 1;
        $registro_periodo['cat_sat_tipo_nomina_id'] = $tg_tipo_servicio['cat_sat_tipo_nomina_id'];

        $r_nom_periodo = (new nom_periodo($this->link))->alta_registro(registro: $registro_periodo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta periodo',data:  $r_nom_periodo);
        }

        $registro_man['codigo'] = $this->registro['codigo'];
        $registro_man['descripcion'] = $this->registro['descripcion'];
        $registro_man['tg_manifiesto_id'] = $r_alta_bd->registro_id;
        $registro_man['nom_periodo_id'] = $r_nom_periodo->registro_id;
        $r_tg_manifiesto_periodo = (new tg_manifiesto_periodo($this->link))->alta_registro(registro:$registro_man);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta manifiesto_periodo',data:  $r_tg_manifiesto_periodo);
        }

        $sube_manifiesto = $this->lee_archivo($r_alta_bd->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta manifiesto_periodo',data:  $sube_manifiesto);
        }
/*
        $descarga_nomina = $this->descarga_nomina($r_alta_bd->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta manifiesto_periodo',data:  $descarga_nomina);
        }
*/
        return $r_alta_bd;
    }

    public function descarga_nomina($registro_id)
    {
        $manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener manifiesto', data: $manifiesto);
        }

        $nominas = (new tg_manifiesto_periodo($this->link))->nominas_by_manifiesto(tg_manifiesto_id: $registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nominas del periodo', data: $nominas);
        }

        $conceptos = (new nom_nomina($this->link))->obten_conceptos_nominas(nominas: $nominas);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nominas del periodo', data: $conceptos);
        }

        $departametos = (new org_departamento($this->link))->registros();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nominas del periodo', data: $conceptos);
        }

        $exportador = (new exportador_eliminar(num_hojas: 3));
        $registros_xls = array();
        $registros_provisiones = array();
        $acumulado_dep = array();
        $cont_dep = 0;
        $total = 0;

        foreach ($nominas as $nomina) {
            $row = (new nom_nomina($this->link))->maqueta_registros_excel(nom_nomina_id: $nomina['nom_nomina_id'],
                conceptos_nomina: $conceptos);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al maquetar datos de la nomina', data: $row);
            }

            $provisiones = (new tg_provision($this->link))->maqueta_excel_provisiones(
                nom_nomina_id: $nomina['nom_nomina_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al maquetar provisiones de la nomina', data: $provisiones);
            }

            $pagos = (new em_cuenta_bancaria($this->link))->maqueta_excel_pagos(data_general: $row);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al maquetar pagos de la nomina', data: $pagos);
            }
            $registros_xls[] = $row;
            $registros_provisiones[] = $provisiones;
            $registros_pagos[] = $pagos;

            $suma_percepcion = (new nom_nomina($this->link))->total_ingreso_bruto(nom_nomina_id: $nomina['nom_nomina_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener la suma de percepciones',
                    data: $nomina);
            }

            foreach ($departametos as $departameto){
                if($departameto['org_departamento_id'] === $nomina['org_departamento_id']){
                    if(isset($acumulado_dep[$nomina['org_departamento_descripcion']])){
                        $acumulado_dep[$nomina['org_departamento_descripcion']] += $suma_percepcion;
                    }else{
                        $acumulado_dep[$nomina['org_departamento_descripcion']] = $suma_percepcion;
                        $cont_dep++;
                    }
                }
            }

            $total += $suma_percepcion;
        }

        $keys = array();
        $keys_provisiones = array();
        $keys_pagos = array();

        foreach (array_keys($registros_xls[0]) as $key) {
            $keys[$key] = strtoupper(str_replace('_', ' ', $key));
        }

        foreach (array_keys($registros_provisiones[0]) as $key) {
            $keys_provisiones[$key] = strtoupper(str_replace('_', ' ', $key));
        }

        foreach (array_keys($registros_pagos[0]) as $key) {
            $keys_pagos[$key] = strtoupper(str_replace('_', ' ', $key));
        }

        $registros = array();
        $registros_provisiones_excel = array();
        $registros_pagos_excel = array();

        foreach ($registros_xls as $row) {
            $registros[] = array_combine(preg_replace(array_map(function ($s) {
                return "/^$s$/";
            },
                array_keys($keys)), $keys, array_keys($row)), $row);
        }

        foreach ($registros_provisiones as $row) {
            $registros_provisiones_excel[] = array_combine(preg_replace(array_map(function ($s) {
                return "/^$s$/";
            },
                array_keys($keys_provisiones)), $keys_provisiones, array_keys($row)), $row);
        }

        foreach ($registros_pagos as $row) {
            $registros_pagos_excel[] = array_combine(preg_replace(array_map(function ($s) {
                return "/^$s$/";
            },
                array_keys($keys_pagos)), $keys_pagos, array_keys($row)), $row);
        }

        $keys_provisiones_sum =  array('IMSS','RCV','INFONAVIT','ISN','ISN ADICIONAL','TOTAL IMPUESTO',
            'PRIMA VACACIONAL','VACACIONES','PRIMA DE ANTIGÜEDAD','GRATIFICACIÓN ANUAL (AGUINALDO)',
            'TOTAL PROVICIONADO','PROV PRIMA VACACIONAL','PROV VACACIONES','PROV PRIMA DE ANTIGÜEDAD',
            'PROV GRATIFICACIÓN ANUAL (AGUINALDO)','SUMA PERCEPCION','FACTOR DE SERVICIO','SUBTOTAL','IVA','TOTAL');
        $totales = array();
        foreach ($registros_provisiones_excel as $empleado) {
            foreach ($empleado as $campo => $valor) {
                foreach ($keys_provisiones_sum as $key) {
                    if ($key === $campo) {
                        if (!isset($totales[$key])) {
                            $totales[$key] = floatval($valor);
                        } else {
                            $totales[$key] += floatval($valor);
                        }
                    }
                }
            }
        }
        $registros_provisiones_excel[] = $totales;

        $keys_hojas = array();
        $keys_hojas['NOMINAS'] = new stdClass();
        $keys_hojas['NOMINAS']->keys = $keys;
        $keys_hojas['NOMINAS']->registros = $registros;
        $keys_hojas['NOMINAS']->inicio_fila_encabezado = 4;
        $keys_hojas['NOMINAS']->inicio_fila_contenido = 5;

        $datos_documentos = array();
        $datos_documentos['empresa'] = $manifiesto['org_empresa_razon_social'];
        $datos_documentos['cliente'] = $manifiesto['com_cliente_razon_social'];
        $datos_documentos['periodo'] = $manifiesto['tg_manifiesto_fecha_inicial_pago'] .' - '.
            $manifiesto['tg_manifiesto_fecha_final_pago'];
        $datos_documentos['folio'] = $manifiesto['tg_manifiesto_id'];
        $datos_documentos['fecha_emision'] = date('Y-m-d');

        $keys_hojas['NOMINAS']->datos_documento = $datos_documentos;

        $keys_hojas['CENTRO DE COSTO'] = new stdClass();
        $keys_hojas['CENTRO DE COSTO']->keys = $keys_provisiones;
        $keys_hojas['CENTRO DE COSTO']->registros = $registros_provisiones_excel;
        $keys_hojas['CENTRO DE COSTO']->inicio_fila_encabezado = 12 + $cont_dep;
        $keys_hojas['CENTRO DE COSTO']->inicio_fila_contenido = 13 + $cont_dep;

        $datos_provisiones = array();
        $datos_provisiones['PERCEPCIONES'] = 0;
        $datos_provisiones['CUOTAS PATRONALES'] = 0;
        $datos_provisiones['PROVISIONES'] = 0;
        $datos_provisiones['FACTOR DE SERVICIO'] = 0;
        $datos_provisiones['SUBTOTAL'] = 0;
        $datos_provisiones['IVA'] = 0;
        $datos_provisiones['FACTOR TOTAL'] = 0;

        foreach ($registros_provisiones as $registro_provision){
            $datos_provisiones['PERCEPCIONES'] += $total;
            $datos_provisiones['CUOTAS PATRONALES'] += $registro_provision['total_impuesto'];
            $datos_provisiones['PROVISIONES'] += $registro_provision['total_provicionado'];
            $datos_provisiones['FACTOR DE SERVICIO'] += $registro_provision['factor_de_servicio'];
            $datos_provisiones['SUBTOTAL'] += $registro_provision['subtotal'];
            $datos_provisiones['IVA'] += $registro_provision['iva'];
            $datos_provisiones['FACTOR TOTAL'] += $registro_provision['total'];
        }

        $keys_hojas['CENTRO DE COSTO']->desgloce_departamento = 'DESGLOSE POR DEPARTAMENTO | FOLIO: '.$manifiesto['tg_manifiesto_id'];
        $keys_hojas['CENTRO DE COSTO']->acumulado_dep = $acumulado_dep;

        $acumulado_cli = array();
        $acumulado_cli[$manifiesto['com_cliente_razon_social']] = $total;
        $keys_hojas['CENTRO DE COSTO']->desgloce_cliente = 'DESGLOSE POR CLIENTE | FOLIO: '.$manifiesto['tg_manifiesto_id'];
        $keys_hojas['CENTRO DE COSTO']->acumulado_cli = $acumulado_cli;

        $keys_hojas['CENTRO DE COSTO']->totales_costos = $datos_provisiones;

        $keys_hojas['PAGOS'] = new stdClass();
        $keys_hojas['PAGOS']->keys = $keys_pagos;
        $keys_hojas['PAGOS']->registros = $registros_pagos_excel;
        $keys_hojas['PAGOS']->inicio_fila_encabezado = 1;
        $keys_hojas['PAGOS']->inicio_fila_contenido = 2;

        $xls = $exportador->genera_xls(header: true, name: $manifiesto["tg_manifiesto_descripcion"],
            nombre_hojas: array("NOMINAS", "CENTRO DE COSTO", "PAGOS"), keys_hojas: $keys_hojas,
            path_base: (new generales())->path_base,  color_contenido: 'DCE6FF', color_encabezado: '0070C0');
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar xls', data: $xls);
        }
        $controlador = new controlador_tg_manifiesto($this->link);

        $link = "./index.php?seccion=tg_manifiesto&accion=lista&registro_id=" . $this->registro_id;
        $link .= "&session_id=$controlador->session_id";
        header('Location:' . $link);

        return $xls;
    }

    public function obten_columna_faltas(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'FALTAS') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_prima_dominical(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'DÍAS DE PRIMA DOMINICAL') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_dias_festivos_laborados(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'FESTIVO LABORADO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_dias_descanso_laborado(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'DESCANSO LABORADO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_compensacion(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'COMPENSACION') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_haberes(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'HABERES') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_monto_neto(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'MONTO NETO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_monto_sueldo(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'MONTO SUELDO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_despensa(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'DESPENSA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_actividades_culturales(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'ACTIVIDADES CULTURALES') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_horas_extras_dobles(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'HORAS EXTRAS DOBLES') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_horas_extras_triples(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'HORAS EXTRAS TRIPLES') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_gratificacion_especial(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'GRATIFICACION ESPECIAL') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_premio_puntualidad(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'PREMIO PUNTUALIDAD') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_premio_asistencia(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'PREMIO ASISTENCIA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_ayuda_transporte(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'AYUDA TRANSPORTE') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_productividad(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'PRODUCTIVIDAD') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_gratificacion(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'GRATIFICACION') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_seguro_vida(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'SEGURO DE VIDA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_caja_ahorro(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'CAJA DE AHORRO') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_anticipo_nomina(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'ANTICIPO DE NOMINA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_pension_alimenticia(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'PENSION ALIMENTICIA') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_descuentos(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'DESCUENTOS') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_incapacidades(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'INCAPACIDAD') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_vacaciones(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'DÍAS DE VACACIONES') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_columna_prima_vacacional(Spreadsheet $documento)
    {
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if ($valorRaw === 'PRIMA VACACIONAL') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_empleados_excel(string $ruta_absoluta)
    {
        $documento = IOFactory::load($ruta_absoluta);
        $totalDeHojas = $documento->getSheetCount();
        /*
                $tg_layout_id = (new tg_layout(link: $this->link))->obten_tg_layout_id(layout: 'manifiesto_nomina');
                if(errores::$error){
                    return $this->errores->error(mensaje: 'Error obtener tg_layout_id',data:  $tg_layout_id);
                }

                $filtro_colums['tg_layout.id'] = $tg_layout_id;
                $tg_columnas = (new tg_column(link: $this->link))->filtro_and(filtro: $filtro_colums);
                if(errores::$error){
                    return $this->errores->error(mensaje: 'Error no existe configuracion layout',data:  $tg_columnas);
                }

                $ubicacion_columnas = array();
                $fila_inicio = 1;
                foreach ($tg_columnas->registros as $columna){
                    $columna_base = $columna['tg_column_descripcion'];
                    $columna_cal = $columna['tg_column_alias'].'.'.$columna_base;

                    for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
                        $hojaActual = $documento->getSheet($indiceHoja);
                        foreach ($hojaActual->getRowIterator() as $fila) {
                            foreach ($fila->getCellIterator() as $celda) {
                                $valorRaw = $celda->getValue();
                                if($valorRaw === $columna_base || $valorRaw === $columna_cal) {
                                    $ubicacion_columnas[$columna_cal] = $celda->getColumn();
                                    $fila_inicio = $celda->getRow();
                                }
                            }
                        }
                    }
                }

                $keys = array('IDTR.ID Trabajador','NOMB.Nombre','APAT.Paterno','AMAT.Materno');

                $filas = array();
                foreach ($ubicacion_columnas as $columna => $valor){
                    foreach ($keys as $key){
                        $fila_init = $fila_inicio;
                        if($columna === $key){
                            $salida = false;
                            while(!$salida){
                                for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
                                    $fila_init++;

                                    $hojaActual = $documento->getSheet($indiceHoja);
                                    $coordenadas = $valor.$fila_init;
                                    $celda = $hojaActual->getCell($coordenadas);

                                    $valor_celda = (string)$celda->getCalculatedValue();
                                    if($valor_celda !== ''){
                                        $filas[] = $fila_init;
                                    }else{
                                        $salida = true;
                                    }
                                }
                            }
                        }
                    }
                }

                $filas_exist = array_unique($filas);

                $prefijos =  array('DIA.','P.','D.','OP.','M.');
                $empleados = array();
                foreach ($filas_exist as $fila_exist){
                    $reg = array();
                    foreach ($ubicacion_columnas as $columna => $valor){
                        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
                            $hojaActual = $documento->getSheet($indiceHoja);
                            $reg[$columna] = $hojaActual->getCell($valor.$fila_exist)->getCalculatedValue();

                            foreach ($prefijos as $prefijo){
                                if (stristr($columna, $prefijo)) {
                                    $reg[$columna] = trim((string)$reg[$columna]);
                                    if(!is_numeric($reg[$columna]) || $reg[$columna] === ''){
                                        $reg[$columna] = 0;
                                    }
                                }
                            }
                        }
                    }
                    $empleados[] = $reg;
                }*/

        $columna_faltas = $this->obten_columna_faltas(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de faltas', data: $columna_faltas);
        }

        $columna_prima_dominical = $this->obten_columna_prima_dominical(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de prima dominical',
                data: $columna_prima_dominical);
        }

        $columna_dias_festivos_laborados = $this->obten_columna_dias_festivos_laborados(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de dias festivos laborados',
                data: $columna_dias_festivos_laborados);
        }

        $columna_incapacidades = $this->obten_columna_incapacidades(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de incapacidades',
                data: $columna_incapacidades);
        }

        $columna_vacaciones = $this->obten_columna_vacaciones(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de vacaciones',
                data: $columna_vacaciones);
        }

        $columna_dias_descanso_laborado = $this->obten_columna_dias_descanso_laborado(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de vacaciones',
                data: $columna_dias_descanso_laborado);
        }

        $columna_compensacion = $this->obten_columna_compensacion(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de compensacion',
                data: $columna_compensacion);
        }

        $columna_haberes = $this->obten_columna_haberes(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de haberes',
                data: $columna_haberes);
        }

        $columna_monto_neto = $this->obten_columna_monto_neto(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de monto neto',
                data: $columna_monto_neto);
        }

        $columna_monto_sueldo = $this->obten_columna_monto_sueldo(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de monto neto',
                data: $columna_monto_sueldo);
        }

        $columna_prima_vacacional = $this->obten_columna_prima_vacacional(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de prima vacacional',
                data: $columna_prima_vacacional);
        }

        $columna_despensa = $this->obten_columna_despensa(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de despensa',
                data: $columna_despensa);
        }

        $columna_actividades_culturales = $this->obten_columna_actividades_culturales(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de actividades_culturales',
                data: $columna_actividades_culturales);
        }

        $columna_horas_extras_dobles = $this->obten_columna_horas_extras_dobles(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de horas_extras_dobles',
                data: $columna_horas_extras_dobles);
        }

        $columna_horas_extras_triples = $this->obten_columna_horas_extras_triples(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de horas_extras_triples',
                data: $columna_horas_extras_triples);
        }

        $columna_gratificacion_especial = $this->obten_columna_gratificacion_especial(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de gratificacion_especial',
                data: $columna_gratificacion_especial);
        }

        $columna_premio_puntualidad = $this->obten_columna_premio_puntualidad(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de premio puntualidad',
                data: $columna_premio_puntualidad);
        }

        $columna_premio_asistencia = $this->obten_columna_premio_asistencia(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de premio_asistencia',
                data: $columna_premio_asistencia);
        }

        $columna_ayuda_transporte = $this->obten_columna_ayuda_transporte(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de ayuda transporte',
                data: $columna_ayuda_transporte);
        }

        $columna_productividad = $this->obten_columna_productividad(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de ayuda transporte',
                data: $columna_productividad);
        }

        $columna_gratificacion = $this->obten_columna_gratificacion(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de gratificacion',
                data: $columna_gratificacion);
        }

        $columna_seguro_vida = $this->obten_columna_seguro_vida(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de seguro_vida',
                data: $columna_seguro_vida);
        }

        $columna_caja_ahorro = $this->obten_columna_caja_ahorro(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de caja_ahorro',
                data: $columna_caja_ahorro);
        }

        $columna_anticipo_nomina = $this->obten_columna_anticipo_nomina(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de anticipo_nomina',
                data: $columna_anticipo_nomina);
        }

        $columna_pension_alimenticia = $this->obten_columna_pension_alimenticia(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de pension_alimenticia',
                data: $columna_pension_alimenticia);
        }

        $columna_descuentos = $this->obten_columna_descuentos(documento: $documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener columna de descuentos',
                data: $columna_descuentos);
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

                    if ($fila >= 7) {
                        if ($columna === "A" && is_numeric($valorRaw)) {
                            $reg = new stdClass();
                            $reg->fila = $fila;
                            $registros[] = $reg;
                        }
                    }
                }
            }

            foreach ($registros as $registro) {
                $reg = new stdClass();
                $reg->codigo = $hojaActual->getCell('A' . $registro->fila)->getValue();
                $reg->nombre = $hojaActual->getCell('B' . $registro->fila)->getValue();
                $reg->ap = $hojaActual->getCell('C' . $registro->fila)->getValue();
                $reg->am = $hojaActual->getCell('D' . $registro->fila)->getValue();
                $reg->faltas = 0;
                $reg->prima_dominical = 0;
                $reg->dias_festivos_laborados = 0;
                $reg->incapacidades = 0;
                $reg->vacaciones = 0;
                $reg->dias_descanso_laborado = 0;
                $reg->compensacion = 0;
                $reg->haberes = 0;

                $reg->prima_vacacional = 0;
                $reg->despensa = 0;
                $reg->actividades_culturales = 0;
                $reg->seguro_vida = 0;
                $reg->caja_ahorro = 0;
                $reg->anticipo_nomina = 0;
                $reg->pension_alimenticia = 0;
                $reg->descuentos = 0;
                $reg->horas_extras_dobles = 0;
                $reg->horas_extras_triples = 0;
                $reg->gratificacion_especial = 0;
                $reg->premio_puntualidad = 0;
                $reg->premio_asistencia = 0;
                $reg->ayuda_transporte = 0;
                $reg->productividad = 0;
                $reg->gratificacion = 0;
                $reg->monto_neto = 0;
                $reg->monto_sueldo = 0;

                if ($columna_faltas !== -1) {
                    $reg->faltas = $hojaActual->getCell($columna_faltas . $registro->fila)->getValue();
                    if (!is_numeric($reg->faltas)) {
                        $reg->faltas = 0;
                    }
                }

                if ($columna_prima_dominical !== -1) {
                    $reg->prima_dominical = $hojaActual->getCell($columna_prima_dominical . $registro->fila)->getValue();
                    if (!is_numeric($reg->prima_dominical)) {
                        $reg->prima_dominical = 0;
                    }
                }

                if ($columna_dias_festivos_laborados !== -1) {
                    $reg->dias_festivos_laborados = $hojaActual->getCell($columna_dias_festivos_laborados . $registro->fila)->getValue();
                    if (!is_numeric($reg->dias_festivos_laborados)) {
                        $reg->dias_festivos_laborados = 0;
                    }
                }

                if ($columna_incapacidades !== -1) {
                    $reg->incapacidades = $hojaActual->getCell($columna_incapacidades . $registro->fila)->getValue();
                    if (!is_numeric($reg->incapacidades)) {
                        $reg->incapacidades = 0;
                    }
                }

                if ($columna_vacaciones !== -1) {
                    $reg->vacaciones = $hojaActual->getCell($columna_vacaciones . $registro->fila)->getValue();
                    if (!is_numeric($reg->vacaciones)) {
                        $reg->vacaciones = 0;
                    }
                }
                if ($columna_dias_descanso_laborado !== -1) {
                    $reg->dias_descanso_laborado = $hojaActual->getCell($columna_dias_descanso_laborado . $registro->fila)->getValue();
                    if (!is_numeric($reg->dias_descanso_laborado)) {
                        $reg->dias_descanso_laborado = 0;
                    }
                }
                if ($columna_compensacion !== -1) {
                    $compensacion = $hojaActual->getCell($columna_compensacion . $registro->fila)->getCalculatedValue();
                    $reg->compensacion = trim((string)$compensacion);

                    if (!is_numeric($reg->compensacion)) {
                        $reg->compensacion = 0;
                    }
                }
                if ($columna_haberes !== -1) {
                    $haberes = $hojaActual->getCell($columna_haberes . $registro->fila)->getCalculatedValue();
                    $reg->haberes = trim((string)$haberes);

                    if (!is_numeric($reg->haberes)) {
                        $reg->haberes = 0;
                    }
                }
                if ($columna_monto_neto !== -1) {
                    $monto_neto = $hojaActual->getCell($columna_monto_neto . $registro->fila)->getCalculatedValue();
                    $reg->monto_neto = trim((string)$monto_neto);

                    if (!is_numeric($reg->monto_neto)) {
                        $reg->monto_neto = 0;
                    }
                }

                if ($columna_monto_sueldo !== -1) {
                    $monto_sueldo = $hojaActual->getCell($columna_monto_sueldo . $registro->fila)->getCalculatedValue();
                    $reg->monto_sueldo = trim((string)$monto_sueldo);

                    if (!is_numeric($reg->monto_sueldo)) {
                        $reg->monto_sueldo = 0;
                    }
                }

                if ($columna_prima_vacacional !== -1) {
                    $prima_vacacional = $hojaActual->getCell($columna_prima_vacacional . $registro->fila)->getCalculatedValue();
                    $reg->prima_vacacional = trim((string)$prima_vacacional);

                    if (!is_numeric($reg->prima_vacacional)) {
                        $reg->prima_vacacional = 0;
                    }
                }
                if ($columna_despensa !== -1) {
                    $despensa = $hojaActual->getCell($columna_despensa . $registro->fila)->getCalculatedValue();
                    $reg->despensa = trim((string)$despensa);

                    if (!is_numeric($reg->despensa)) {
                        $reg->despensa = 0;
                    }
                }
                if ($columna_actividades_culturales !== -1) {
                    $actividades_culturales = $hojaActual->getCell($columna_actividades_culturales . $registro->fila)->getCalculatedValue();
                    $reg->actividades_culturales = trim((string)$actividades_culturales);

                    if (!is_numeric($reg->actividades_culturales)) {
                        $reg->actividades_culturales = 0;
                    }
                }
                if ($columna_seguro_vida !== -1) {
                    $seguro_vida = $hojaActual->getCell($columna_seguro_vida . $registro->fila)->getCalculatedValue();
                    $reg->seguro_vida = trim((string)$seguro_vida);

                    if (!is_numeric($reg->seguro_vida)) {
                        $reg->seguro_vida = 0;
                    }
                }
                if ($columna_caja_ahorro !== -1) {
                    $caja_ahorro = $hojaActual->getCell($columna_caja_ahorro . $registro->fila)->getCalculatedValue();
                    $reg->caja_ahorro = trim((string)$caja_ahorro);

                    if (!is_numeric($reg->caja_ahorro)) {
                        $reg->caja_ahorro = 0;
                    }
                }
                if ($columna_anticipo_nomina !== -1) {
                    $anticipo_nomina = $hojaActual->getCell($columna_anticipo_nomina . $registro->fila)->getCalculatedValue();
                    $reg->anticipo_nomina = trim((string)$anticipo_nomina);

                    if (!is_numeric($reg->anticipo_nomina)) {
                        $reg->anticipo_nomina = 0;
                    }
                }
                if ($columna_pension_alimenticia !== -1) {
                    $pension_alimenticia = $hojaActual->getCell($columna_pension_alimenticia . $registro->fila)->getCalculatedValue();
                    $reg->pension_alimenticia = trim((string)$pension_alimenticia);

                    if (!is_numeric($reg->pension_alimenticia)) {
                        $reg->pension_alimenticia = 0;
                    }
                }
                if ($columna_descuentos !== -1) {
                    $descuentos = $hojaActual->getCell($columna_descuentos . $registro->fila)->getCalculatedValue();
                    $reg->descuentos = trim((string)$descuentos);

                    if (!is_numeric($reg->descuentos)) {
                        $reg->descuentos = 0;
                    }
                }
                if ($columna_horas_extras_dobles !== -1) {
                    $horas_extras_dobles = $hojaActual->getCell($columna_horas_extras_dobles . $registro->fila)->getCalculatedValue();
                    $reg->horas_extras_dobles = trim((string)$horas_extras_dobles);

                    if (!is_numeric($reg->horas_extras_dobles)) {
                        $reg->horas_extras_dobles = 0;
                    }
                }
                if ($columna_horas_extras_triples !== -1) {
                    $horas_extras_triples = $hojaActual->getCell($columna_horas_extras_triples . $registro->fila)->getCalculatedValue();
                    $reg->horas_extras_triples = trim((string)$horas_extras_triples);

                    if (!is_numeric($reg->horas_extras_triples)) {
                        $reg->horas_extras_triples = 0;
                    }
                }
                if ($columna_gratificacion_especial !== -1) {
                    $gratificacion_especial = $hojaActual->getCell($columna_gratificacion_especial . $registro->fila)->getCalculatedValue();
                    $reg->gratificacion_especial = trim((string)$gratificacion_especial);

                    if (!is_numeric($reg->gratificacion_especial)) {
                        $reg->gratificacion_especial = 0;
                    }
                }
                if ($columna_premio_puntualidad !== -1) {
                    $premio_puntualidad = $hojaActual->getCell($columna_premio_puntualidad . $registro->fila)->getCalculatedValue();
                    $reg->premio_puntualidad = trim((string)$premio_puntualidad);

                    if (!is_numeric($reg->premio_puntualidad)) {
                        $reg->premio_puntualidad = 0;
                    }
                }
                if ($columna_premio_asistencia !== -1) {
                    $premio_asistencia = $hojaActual->getCell($columna_premio_asistencia . $registro->fila)->getCalculatedValue();
                    $reg->premio_asistencia = trim((string)$premio_asistencia);

                    if (!is_numeric($reg->premio_asistencia)) {
                        $reg->premio_asistencia = 0;
                    }
                }
                if ($columna_ayuda_transporte !== -1) {
                    $ayuda_transporte = $hojaActual->getCell($columna_ayuda_transporte . $registro->fila)->getCalculatedValue();
                    $reg->ayuda_transporte = trim((string)$ayuda_transporte);

                    if (!is_numeric($reg->ayuda_transporte)) {
                        $reg->ayuda_transporte = 0;
                    }
                }
                if ($columna_productividad !== -1) {
                    $productividad = $hojaActual->getCell($columna_productividad . $registro->fila)->getCalculatedValue();
                    $reg->productividad = trim((string)$productividad);

                    if (!is_numeric($reg->productividad)) {
                        $reg->productividad = 0;
                    }
                }
                if ($columna_gratificacion !== -1) {
                    $gratificacion = $hojaActual->getCell($columna_gratificacion . $registro->fila)->getCalculatedValue();
                    $reg->gratificacion = trim((string)$gratificacion);

                    if (!is_numeric($reg->ayuda_transporte)) {
                        $reg->gratificacion = 0;
                    }
                }
                $empleados[] = $reg;
            }
        }

        return $empleados;
    }

    public function obten_registro_patronal(int $tg_manifiesto_id)
    {
        $tg_manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $tg_manifiesto_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener manifiesto', data: $tg_manifiesto);
        }

        $filtro_org['fc_csd.id'] = $tg_manifiesto['tg_manifiesto_fc_csd_id'];
        $im_registro_patronal = (new em_registro_patronal($this->link))->filtro_and(filtro: $filtro_org);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener registro patronal', data: $im_registro_patronal);
        }

        return $im_registro_patronal->registros[0];
    }


    public function lee_archivo($registro_id){
        $tg_manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener manifiesto', data: $tg_manifiesto);
        }
        $doc_documento_modelo = new doc_documento($this->link);
        $doc_documento_modelo->registro['descripcion'] = $tg_manifiesto['tg_manifiesto_descripcion'];
        $doc_documento_modelo->registro['descripcion_select'] = $tg_manifiesto['tg_manifiesto_descripcion'];
        $doc_documento_modelo->registro['doc_tipo_documento_id'] = 1;
        $doc_documento = $doc_documento_modelo->alta_bd(file: $_FILES['archivo']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta el documento', data: $doc_documento);
        }

        $empleados_excel = $this->obten_empleados_excel(ruta_absoluta: $doc_documento->registro['doc_documento_ruta_absoluta']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener empleados', data: $empleados_excel);
        }

        $em_registro_patronal = $this->obten_registro_patronal(tg_manifiesto_id: $registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener registro patronal', data: $em_registro_patronal);
        }

        $em_registro_patronal_id = $em_registro_patronal['em_registro_patronal_id'];
        $empleados = array();
        foreach ($empleados_excel as $empleado_excel) {
            $filtro['em_registro_patronal.id'] = $em_registro_patronal_id;
            $filtro['em_empleado.nombre'] = $empleado_excel->nombre;
            $filtro['em_empleado.ap'] = $empleado_excel->ap;
            $filtro['em_empleado.am'] = $empleado_excel->am;

            $registro = (new em_empleado($this->link))->filtro_and(filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al al obtener registro de empleado', data: $registro);
            }
            if ($registro->n_registros === 0) {
                return $this->error->error(mensaje: 'Error se encontro el empleado ' . $empleado_excel->nombre . ' ' .
                    $empleado_excel->ap . ' ' . $empleado_excel->am, data: $registro);
            }
            if ($registro->n_registros > 0) {
                $empleados[] = $registro->registros[0];
            }
        }

        $filtro_per['tg_manifiesto.id'] = $registro_id;
        $nom_periodos = (new tg_manifiesto_periodo($this->link))->filtro_and(filtro: $filtro_per);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al al obtener periodos', data: $nom_periodos);
        }

        foreach ($nom_periodos->registros as $nom_periodo) {
            $empleados_res = array();
            foreach ($empleados as $empleado) {
                $filtro_em['em_empleado.id'] = $empleado['em_empleado_id'];
                $filtro_em['nom_conf_nomina.id'] = $tg_manifiesto['nom_conf_nomina_id'];
                $conf_empleado = (new nom_conf_empleado($this->link))->filtro_and(filtro: $filtro_em);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al obtener configuracion de empleado',
                        data: $conf_empleado);
                }

                if (isset($conf_empleado->registros[0])) {
                    $empleados_res[] = $conf_empleado->registros[0];
                }
            }

            foreach ($empleados_res as $empleado) {
                foreach ($empleados_excel as $empleado_excel) {
                    if (trim($empleado_excel->nombre) === trim($empleado['em_empleado_nombre']) &&
                        trim($empleado_excel->ap) === trim($empleado['em_empleado_ap']) &&
                        trim($empleado_excel->am) === trim($empleado['em_empleado_am'])) {

                        if ((float)$empleado_excel->monto_sueldo > 0) {

                            $dias_asistidos = $empleado_excel->monto_sueldo / $empleado['em_empleado_salario_diario'];

                            $dias_restantes = $empleado['cat_sat_periodicidad_pago_nom_n_dias'];
                            if ($empleado['nom_conf_nomina_aplica_septimo_dia'] === 'activo') {
                                $res = $empleado['cat_sat_periodicidad_pago_nom_n_dias'] / 7;
                                $dias_restantes -= round($res);
                            }

                            $dias_faltas = $dias_restantes - $dias_asistidos;

                            $registro_inc['nom_tipo_incidencia_id'] = 1;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $dias_faltas;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                            }
                        }

                        if ((int)$empleado_excel->faltas > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 1;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->faltas;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                            }
                        }
                        if ((int)$empleado_excel->prima_dominical > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 2;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->prima_dominical;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                            }
                        }
                        if ((int)$empleado_excel->dias_festivos_laborados > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 3;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->dias_festivos_laborados;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                            }
                        }
                        if ((int)$empleado_excel->incapacidades > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 4;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->incapacidades;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                            }
                        }
                        if ((int)$empleado_excel->vacaciones > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 5;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->vacaciones;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);
                            }
                        }
                        if ((int)$empleado_excel->dias_descanso_laborado > 0) {
                            $registro_inc['nom_tipo_incidencia_id'] = 6;
                            $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                            $registro_inc['n_dias'] = $empleado_excel->dias_descanso_laborado;
                            $registro_inc['fecha_incidencia'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];

                            $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al dar de alta incidencias',
                                    data: $nom_incidencia);

                            }
                        }
                    }
                }

                $alta_empleado = (new nom_periodo($this->link))->alta_empleado_periodo(empleado: $empleado,
                    nom_periodo: $nom_periodo);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al dar de alta la nomina del empleado',
                        data: $alta_empleado);
                }

                foreach ($empleados_excel as $empleado_excel) {
                    if (trim($empleado_excel->nombre) === trim($empleado['em_empleado_nombre']) &&
                        trim($empleado_excel->ap) === trim($empleado['em_empleado_ap']) &&
                        trim($empleado_excel->am) === trim($empleado['em_empleado_am'])) {
                        if ($empleado_excel->compensacion > 0) {
                            $nom_percepcion = (new nom_percepcion($this->link))->get_aplica_compensacion();
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error insertar conceptos', data: $nom_percepcion);
                            }

                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = $nom_percepcion['nom_percepcion_id'];
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->compensacion;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }

                        if ($empleado_excel->prima_vacacional > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 12;
                            $nom_par_percepcion_sep['importe_exento'] = $empleado_excel->prima_vacacional;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }

                        if ($empleado_excel->despensa > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 4;
                            $nom_par_percepcion_sep['importe_exento'] = $empleado_excel->despensa;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->haberes > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 22;
                            $nom_par_percepcion_sep['importe_exento'] = $empleado_excel->haberes;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }

                        if ($empleado_excel->actividades_culturales > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 21;
                            $nom_par_percepcion_sep['importe_exento'] = $empleado_excel->actividades_culturales;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->horas_extras_dobles > 0) {
                            $mitad_horas_ext = $empleado_excel->horas_extras_dobles / 2;

                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 13;
                            $nom_par_percepcion_sep['importe_gravado'] = $mitad_horas_ext;
                            $nom_par_percepcion_sep['importe_exento'] = $mitad_horas_ext;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->horas_extras_triples > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 19;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->horas_extras_triples;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->gratificacion_especial > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 14;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->gratificacion_especial;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->premio_puntualidad > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 15;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->premio_puntualidad;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->premio_asistencia > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 16;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->premio_asistencia;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->ayuda_transporte > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 17;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->ayuda_transporte;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->productividad > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 23;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->productividad;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->gratificacion > 0) {
                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 18;
                            $nom_par_percepcion_sep['importe_gravado'] = $empleado_excel->gratificacion;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }
                        }
                        if ($empleado_excel->seguro_vida > 0) {
                            $nom_par_deduccion_sep = array();
                            $nom_par_deduccion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_deduccion_sep['nom_deduccion_id'] = 5;
                            $nom_par_deduccion_sep['importe_gravado'] = $empleado_excel->seguro_vida;

                            $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $nom_par_deduccion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar deduccion default', data: $r_alta_nom_par_deduccion);
                            }
                        }
                        if ($empleado_excel->caja_ahorro > 0) {
                            $nom_par_deduccion_sep = array();
                            $nom_par_deduccion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_deduccion_sep['nom_deduccion_id'] = 8;
                            $nom_par_deduccion_sep['importe_gravado'] = $empleado_excel->caja_ahorro;

                            $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $nom_par_deduccion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar deduccion default', data: $r_alta_nom_par_deduccion);
                            }
                        }
                        if ($empleado_excel->anticipo_nomina > 0) {
                            $nom_par_deduccion_sep = array();
                            $nom_par_deduccion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_deduccion_sep['nom_deduccion_id'] = 9;
                            $nom_par_deduccion_sep['importe_gravado'] = $empleado_excel->anticipo_nomina;

                            $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $nom_par_deduccion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar deduccion default', data: $r_alta_nom_par_deduccion);
                            }
                        }
                        if ($empleado_excel->pension_alimenticia > 0) {
                            $nom_par_deduccion_sep = array();
                            $nom_par_deduccion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_deduccion_sep['nom_deduccion_id'] = 11;
                            $nom_par_deduccion_sep['importe_gravado'] = $empleado_excel->pension_alimenticia;

                            $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $nom_par_deduccion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar deduccion default', data: $r_alta_nom_par_deduccion);
                            }
                        }
                        if ($empleado_excel->descuentos > 0) {
                            $nom_par_deduccion_sep = array();
                            $nom_par_deduccion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_deduccion_sep['nom_deduccion_id'] = 6;
                            $nom_par_deduccion_sep['importe_gravado'] = $empleado_excel->descuentos;

                            $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $nom_par_deduccion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar deduccion default', data: $r_alta_nom_par_deduccion);
                            }
                        }

                        if ($empleado_excel->monto_neto > 0) {

                            $nom_nomina = (new nom_nomina($this->link))->registro(registro_id: $alta_empleado->registro_id);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error obtener nomina',
                                    data: $nom_nomina);
                            }

                            $cat_sat_periodicidad_pago_nom_id = $empleado['cat_sat_periodicidad_pago_nom_id'];
                            $em_salario_diario = $empleado['em_empleado_salario_diario'];
                            $em_empleado_salario_diario_integrado = $empleado['em_empleado_salario_diario_integrado'];
                            $nom_nomina_fecha_final_pago = $nom_periodo['nom_periodo_fecha_final_pago'];
                            $nom_nomina_num_dias_pagados = $alta_empleado->registro['cat_sat_periodicidad_pago_nom_n_dias'];
                            $total_gravado = $empleado_excel->monto_neto;
                            $resultado = (new calcula_nomina())->nomina_neto(
                                cat_sat_periodicidad_pago_nom_id: $cat_sat_periodicidad_pago_nom_id,
                                em_salario_diario: $em_salario_diario,
                                em_empleado_salario_diario_integrado: $em_empleado_salario_diario_integrado,
                                link: $this->link, nom_nomina_fecha_final_pago: $nom_nomina_fecha_final_pago,
                                nom_nomina_num_dias_pagados: $nom_nomina_num_dias_pagados,
                                total_neto: $total_gravado);

                            $resultado_calculado = $resultado - $nom_nomina['nom_nomina_total_percepcion_gravado'];


                            $nom_par_percepcion_sep = array();
                            $nom_par_percepcion_sep['nom_nomina_id'] = $alta_empleado->registro_id;
                            $nom_par_percepcion_sep['nom_percepcion_id'] = 10;
                            $nom_par_percepcion_sep['importe_gravado'] = $resultado_calculado;

                            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
                            if (errores::$error) {
                                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
                            }

                        }
                    }
                }

            }
        }

        return $registro_id;
    }

    public function obten_sucursal_alianza(int $com_sucursal_id){
        $filtro['com_sucursal.id'] = $com_sucursal_id;
        $tg_sucursal_alianza = (new tg_sucursal_alianza($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener configuracion tg_sucursal_alianza',
                data: $tg_sucursal_alianza);
        }

        if($tg_sucursal_alianza->n_registros < 1){
            return $this->error->error(mensaje: 'Error no existe alianza',
                data: $tg_sucursal_alianza);
        }

        return $tg_sucursal_alianza->registros[0];
    }

    public function modifica_bd(array $registro, int $id, bool $reactiva = false, array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        $tg_sucursal_alianza = $this->obten_sucursal_alianza(com_sucursal_id: $registro['com_sucursal_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro empresa',data: $tg_sucursal_alianza);
        }
        $this->registro['tg_sucursal_alianza_id'] = $tg_sucursal_alianza['tg_sucursal_alianza_id'];

        if(isset($registro['com_sucursal_id'])){
            unset($registro['com_sucursal_id']);
        }
        if(isset($registro['tg_cte_alianza_id'])){
            unset($registro['tg_cte_alianza_id']);
        }

        $r_modifica_bd = parent::modifica_bd($registro, $id, $reactiva, $keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar manifiesto',data: $r_modifica_bd);
        }

        return $r_modifica_bd;
    }

    public function maqueta_encabezado_excel(array $registros_xls){
        $r_manifiesto = (new tg_manifiesto($this->link))->registro(registro_id: $this->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al manifiesto',data:  $r_manifiesto);
        }

        $r_fc_csd = (new fc_csd($this->link))->registro(registro_id: $r_manifiesto['tg_manifiesto_fc_csd_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al registro de empresa',data:  $r_fc_csd);
        }

        $r_tg_sucursal_alianza = (new tg_sucursal_alianza($this->link))->registro(
            registro_id: $r_manifiesto['tg_manifiesto_tg_sucursal_alianza_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al registro de cliente',data:  $r_tg_sucursal_alianza);
        }

        $r_tg_manifiesto_periodo = (new tg_manifiesto_periodo($this->link))->get_periodos_manifiesto(
            tg_manifiesto_id:  $this->registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener manifiesto periodo',data:  $r_tg_manifiesto_periodo);
        }

        return $registros_xls;
    }
}