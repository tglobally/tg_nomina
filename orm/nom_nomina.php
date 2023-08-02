<?php

namespace tglobally\tg_nomina\models;

use base\orm\modelo;
use config\generales;
use config\pac;
use gamboamartin\comercial\models\com_sucursal;
use gamboamartin\comercial\models\com_tmp_cte_dp;
use gamboamartin\documento\models\doc_documento;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\_comprobante;
use gamboamartin\facturacion\models\fc_cer_pem;
use gamboamartin\facturacion\models\fc_cfdi_sellado;
use gamboamartin\facturacion\models\fc_factura;
use gamboamartin\facturacion\models\fc_factura_documento;
use gamboamartin\facturacion\models\fc_key_pem;
use gamboamartin\facturacion\models\fc_partida;
use gamboamartin\im_registro_patronal\models\im_movimiento;
use gamboamartin\nomina\controllers\xml_nom;
use gamboamartin\nomina\models\nom_nomina_documento;
use gamboamartin\plugins\files;
use gamboamartin\proceso\models\pr_proceso;
use gamboamartin\xml_cfdi_4\cfdis;
use gamboamartin\xml_cfdi_4\fechas;
use gamboamartin\xml_cfdi_4\timbra;
use Mpdf\Mpdf;
use stdClass;
use tglobally\tg_cliente\models\tg_cliente_empresa;
use Throwable;
use ZipArchive;

class nom_nomina extends \gamboamartin\nomina\models\nom_nomina
{

    public function alta_bd(): array|stdClass
    {
        $movimiento = (new im_movimiento($this->link))->filtro_and(filtro: array("em_empleado.id" => $this->registro['em_empleado_id']),
            limit: 1, order: array("im_movimiento_id" => "DESC"));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener movimientos', data: $movimiento);
        }

        if ($movimiento->n_registros > 0 && $movimiento->registros [0]['im_tipo_movimiento_descripcion'] === 'BAJA') {
            return $this->error->error(mensaje: 'Error el empleado esta dado de baja', data: $movimiento);
        }

        $alta = parent::alta_bd();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar nomina', data: $alta);
        }

        $acciones = $this->conf_provisiones_acciones(em_empleado_id: $this->registro['em_empleado_id'],
            nom_nomina_id: $alta->registro_id, fecha: $this->registro['fecha_pago']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar acciones de conf. de provisiones', data: $acciones);
        }

        return $alta;
    }

    public function conf_provisiones_acciones(int $em_empleado_id, int $nom_nomina_id, string $fecha): array|stdClass
    {
        $filtro_empleado['tg_empleado_sucursal.em_empleado_id'] = $em_empleado_id;
        $empleado = (new tg_empleado_sucursal($this->link))->filtro_and(filtro: $filtro_empleado);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener cliente del empleado', data: $empleado);
        }


        if ($empleado->n_registros > 0){
            $filtro_provisiones['tg_cliente_empresa.com_sucursal_id'] = $empleado->registros[0]['com_sucursal_id'];
            $filtro_provisiones['tg_cliente_empresa.org_sucursal_id'] = $empleado->registros[0]['fc_csd_org_sucursal_id'];
            $provisiones = (new tg_cliente_empresa($this->link))->filtro_and(filtro: $filtro_provisiones);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener cliente del empleado', data: $provisiones);
            }


        }

        $data = $this->get_tg_conf_provisiones(em_empleado_id: $em_empleado_id, fecha: $fecha);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener conf. de provisiones del empleado', data: $data);
        }

        foreach ($data->registros as $configuracion) {

            $datos = $this->maqueta_data_provision(tg_conf_provision: $configuracion, nom_nomina_id: $nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al maquetar datos de conf. de provisiones del empleado',
                    data: $datos);
            }

            $alta_provision = (new tg_provision($this->link))->alta_registro(registro: $datos);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta conf. de provisiones del empleado',
                    data: $alta_provision);
            }

        }

        return $data;
    }

    public function get_tg_conf_provisiones(int $em_empleado_id, string $fecha): array|stdClass
    {
        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id debe ser mayor a 0', data: $em_empleado_id);
        }

        $filtro['em_empleado.id'] = $this->registro['em_empleado_id'];
        $filtro_especial[0][$fecha]['operador'] = '>=';
        $filtro_especial[0][$fecha]['valor'] = 'tg_conf_provision.fecha_inicio';
        $filtro_especial[0][$fecha]['comparacion'] = 'AND';
        $filtro_especial[0][$fecha]['valor_es_campo'] = true;

        $filtro_especial[1][$fecha]['operador'] = '<=';
        $filtro_especial[1][$fecha]['valor'] = 'tg_conf_provision.fecha_fin';
        $filtro_especial[1][$fecha]['comparacion'] = 'AND';
        $filtro_especial[1][$fecha]['valor_es_campo'] = true;

        $conf = (new tg_conf_provision($this->link))->filtro_and(filtro: $filtro, filtro_especial: $filtro_especial);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al filtrar conf. de provisiones del empleado', data: $conf);
        }

        return $conf;
    }

    public function maqueta_data_provision(array $tg_conf_provision, int $nom_nomina_id): array|stdClass
    {
        $data = array();
        $data['codigo'] = $tg_conf_provision['tg_conf_provision_codigo'] . $nom_nomina_id;
        $data['descripcion'] = $tg_conf_provision['tg_conf_provision_descripcion'] . $nom_nomina_id;
        $data['tg_tipo_provision_id'] = $tg_conf_provision['tg_tipo_provision_id'];
        $data['nom_nomina_id'] = $nom_nomina_id;
        $data['monto'] = $tg_conf_provision['tg_conf_provision_monto'];

        return $data;
    }


    private function guarda_documento(string $directorio, string $extension, string $contenido, int $fc_factura_id,
                                      int    $nom_nomina_id): array|stdClass
    {
        $ruta_archivos = $this->ruta_archivos(directorio: $directorio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener ruta de archivos', data: $ruta_archivos);
        }

        $ruta_archivo = "$ruta_archivos/$this->registro_id.$extension";

        $guarda_archivo = (new files())->guarda_archivo_fisico(contenido_file: $contenido, ruta_file: $ruta_archivo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al guardar archivo', data: $guarda_archivo);
        }

        $tipo_documento = (new fc_factura(link: $this->link))->doc_tipo_documento_id(extension: $extension);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar extension del documento', data: $tipo_documento);
        }

        $file['name'] = $guarda_archivo;
        $file['tmp_name'] = $guarda_archivo;

        $documento['doc_tipo_documento_id'] = $tipo_documento;
        $documento['descripcion'] = "$this->registro_id.$extension";
        $documento['descripcion_select'] = "$this->registro_id.$extension";

        $documento = (new doc_documento(link: $this->link))->alta_documento(registro: $documento, file: $file);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al guardar jpg', data: $documento);
        }

        $registro['fc_factura_id'] = $fc_factura_id;
        $registro['doc_documento_id'] = $documento->registro_id;
        $factura_documento = (new fc_factura_documento($this->link))->alta_registro(registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al guardar relacion factura con documento', data: $factura_documento);
        }

        $registro_nomina['nom_nomina_id'] = $nom_nomina_id;
        $registro_nomina['doc_documento_id'] = $documento->registro_id;
        $nomina_documento = (new nom_nomina_documento($this->link))->alta_registro(registro: $registro_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al guardar relacion nomina con documento', data: $nomina_documento);
        }

        return $documento;
    }


    private function get_datos_json(string $ruta_json = ""): array
    {
        $xml = simplexml_load_file($ruta_json);
        $ns = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('c', $ns['cfdi']);


        $xml_data = array();
        $xml_data['cfdi_comprobante'] = array();
        $xml_data['cfdi_emisor'] = array();
        $xml_data['cfdi_receptor'] = array();
        $xml_data['cfdi_conceptos'] = array();


        $nodos = array();
        $nodos[] = '//cfdi:Comprobante';
        $nodos[] = '//cfdi:Comprobante//cfdi:Emisor';
        $nodos[] = '//cfdi:Comprobante//cfdi:Receptor';
        $nodos[] = '//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto';

        foreach ($nodos as $key => $nodo) {
            foreach ($xml->xpath($nodo) as $value) {
                $data = (array)$value->attributes();
                $data = $data['@attributes'];
                $xml_data[array_keys($xml_data)[$key]] = $data;
            }
        }
        return $xml_data;
    }


    private function data_comprobante(stdClass $fc_factura): array|stdClass
    {
        $keys = array('cat_sat_moneda_codigo', 'fc_factura_exportacion', 'cat_sat_tipo_de_comprobante_codigo',
            'dp_cp_descripcion', 'fc_factura_fecha', 'fc_factura_folio', 'cat_sat_metodo_pago_codigo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $fc_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar fc_factura', data: $valida);
        }

       /* $total = (new fc_factura($this->link))->total(modelo_partida: ,name_entidad: '',registro_id: $fc_factura->fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total', data: $total);
        }

        $sub_total = (new fc_factura($this->link))->sub_total(modelo_partida: $this,name_entidad: '',registro_id: $fc_factura->fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sub_total', data: $sub_total);
        }*/

        $descuento = (new fc_factura($this->link))->get_factura_descuento(registro_id: $fc_factura->fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener descuento', data: $descuento);
        }

        $comprobante = new stdClass();
        $comprobante->Moneda = $fc_factura->cat_sat_moneda_codigo;
        //$comprobante->Total = number_format((float)$total, 2, '.', '');
        $comprobante->Exportacion = $fc_factura->fc_factura_exportacion;
        $comprobante->TipoDeComprobante = $fc_factura->cat_sat_tipo_de_comprobante_codigo;
        //$comprobante->SubTotal = number_format((float)$sub_total, 2, '.', '');
        $comprobante->LugarExpedicion = $fc_factura->dp_cp_descripcion;
        $comprobante->Fecha = $fc_factura->fc_factura_fecha;
        $comprobante->Folio = $fc_factura->fc_factura_folio;
        $comprobante->Version = "4.0";

        if (isset($fc_factura->fc_csd_no_certificado)) {
            if (trim($fc_factura->fc_csd_no_certificado) !== '') {
                $comprobante->NoCertificado = $fc_factura->fc_csd_no_certificado;
            }
        }

        $comprobante->FormaPago = $fc_factura->cat_sat_forma_pago_codigo;
        $comprobante->MetodoPago = $fc_factura->cat_sat_metodo_pago_codigo;
        $comprobante->Descuento = number_format((float)$descuento, 2, '.', '');


        return $comprobante;
    }

    private function data_emisor(stdClass $fc_factura): stdClass|array
    {
        $keys = array('org_empresa_rfc', 'org_empresa_razon_social', 'cat_sat_regimen_fiscal_codigo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $fc_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar fc_factura', data: $valida);
        }

        $keys = array('org_empresa_rfc');
        $valida = $this->validacion->valida_rfcs(keys: $keys, registro: $fc_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar fc_factura', data: $valida);
        }

        $keys = array('cat_sat_regimen_fiscal_codigo');
        $valida = $this->validacion->valida_codigos_int_0_3_numbers(keys: $keys, registro: $fc_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar fc_factura', data: $valida);
        }

        $emisor = new stdClass();
        $emisor->Rfc = $fc_factura->org_empresa_rfc;
        $emisor->Nombre = $fc_factura->org_empresa_razon_social;
        $emisor->RegimenFiscal = $fc_factura->cat_sat_regimen_fiscal_codigo;

        return $emisor;
    }

    private function data_receptor(stdClass $fc_factura, stdClass $com_sucursal): stdClass|array
    {
        $keys = array('com_cliente_rfc', 'com_cliente_razon_social', 'cat_sat_uso_cfdi_codigo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $fc_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar factura', data: $valida);
        }

        $keys = array('dp_cp_descripcion', 'cat_sat_regimen_fiscal_codigo');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $com_sucursal);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar com_sucursal', data: $valida);
        }

        $keys = array('com_cliente_rfc');
        $valida = $this->validacion->valida_rfcs(keys: $keys, registro: $fc_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar factura', data: $valida);
        }

        $keys = array('dp_cp_descripcion');
        $valida = $this->validacion->valida_codigos_int_0_5_numbers(keys: $keys, registro: $com_sucursal);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar com_sucursal', data: $valida);
        }

        $keys = array('cat_sat_regimen_fiscal_codigo');
        $valida = $this->validacion->valida_codigos_int_0_3_numbers(keys: $keys, registro: $com_sucursal);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar com_sucursal', data: $valida);
        }

        $receptor = new stdClass();
        $receptor->Rfc = $com_sucursal->com_cliente_rfc;
        $receptor->Nombre = $com_sucursal->com_cliente_razon_social;
        $receptor->DomicilioFiscalReceptor = $com_sucursal->dp_cp_descripcion;
        $receptor->RegimenFiscalReceptor = $com_sucursal->cat_sat_regimen_fiscal_codigo;
        $receptor->UsoCFDI = $fc_factura->cat_sat_uso_cfdi_codigo;

        return $receptor;
    }

    private function data_conceptos(stdClass $fc_factura): stdClass|array
    {
        $fc_partida = (new fc_partida($this->link))->filtro_and(filtro: array("fc_factura_id" => $fc_factura->fc_factura_id));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener partida de nomina', data: $fc_partida);
        }

        if ($fc_partida->n_registros > 1) {
            return $this->error->error(mensaje: 'Error una nomina no puede tener mas de una partida', data: $fc_partida);
        }

        $fc_partida = $fc_partida->registros[0];

        $keys = array('com_producto_codigo', 'fc_partida_cantidad', 'cat_sat_unidad_codigo', 'com_producto_descripcion',
            'fc_partida_valor_unitario', 'cat_sat_obj_imp_codigo', 'fc_partida_descuento');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $fc_partida);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar partida', data: $valida);
        }

        $valor_unitario = number_format((float)$fc_partida['fc_partida_valor_unitario'], 2, '.', '');
        $importe = number_format((float)$valor_unitario * (float)$fc_partida['fc_partida_cantidad'], 2, '.', '');
        $descuento = number_format((float)$fc_partida['fc_partida_descuento'], 2, '.', '');

        $conceptos = array();
        $conceptos[0] = new stdClass();
        $conceptos[0]->ClaveProdServ = $fc_partida['com_producto_codigo'];
        $conceptos[0]->NoIdentificacion = $fc_partida['com_producto_descripcion'];
        $conceptos[0]->Unidad = $fc_partida['cat_sat_unidad_codigo'];
        $conceptos[0]->Descuento = $descuento;
        $conceptos[0]->Cantidad = $fc_partida['fc_partida_cantidad'];
        $conceptos[0]->ClaveUnidad = $fc_partida['cat_sat_unidad_codigo'];
        $conceptos[0]->Descripcion = $fc_partida['com_producto_descripcion'];
        $conceptos[0]->ValorUnitario = $valor_unitario;
        $conceptos[0]->Importe = $importe;
        $conceptos[0]->ObjetoImp = $fc_partida['cat_sat_obj_imp_codigo'];

        return $conceptos;
    }

    private function data_complemento(stdClass $nom_nomina): stdClass|array
    {
        $keys = array('cat_sat_tipo_nomina_codigo', 'nom_nomina_fecha_pago', 'nom_nomina_fecha_inicial_pago',
            'nom_nomina_fecha_final_pago', 'nom_nomina_total_percepcion_total', 'nom_nomina_total_deduccion_total',
            'nom_nomina_total_otro_pago_total', 'em_registro_patronal_descripcion', 'org_empresa_rfc',
            'em_empleado_curp', 'em_empleado_nss', 'em_empleado_fecha_inicio_rel_laboral', 'cat_sat_tipo_contrato_nom_codigo',
            'cat_sat_tipo_jornada_nom_codigo', 'cat_sat_tipo_regimen_nom_codigo', 'em_empleado_codigo',
            'em_clase_riesgo_codigo', 'cat_sat_periodicidad_pago_nom_codigo', 'em_cuenta_bancaria_clabe',
            'em_empleado_salario_diario_integrado');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar nomina', data: $valida);
        }

        $complemento = new stdClass();
        $complemento->nomina = new stdClass();
        $complemento->nomina->version = "1.2";
        $complemento->nomina->tipo_nomina = $nom_nomina->cat_sat_tipo_nomina_codigo;
        $complemento->nomina->fecha_pago = $nom_nomina->nom_nomina_fecha_pago;
        $complemento->nomina->fecha_inicial_pago = $nom_nomina->nom_nomina_fecha_inicial_pago;
        $complemento->nomina->fecha_final_pago = $nom_nomina->nom_nomina_fecha_final_pago;
        $complemento->nomina->num_dias_pagados = $nom_nomina->nom_nomina_fecha_final_pago;
        $complemento->nomina->total_percepciones = $nom_nomina->nom_nomina_total_percepcion_total;
        $complemento->nomina->total_deducciones = $nom_nomina->nom_nomina_total_deduccion_total;
        $complemento->nomina->total_otros_pagos = $nom_nomina->nom_nomina_total_otro_pago_total;
        $complemento->nomina->emisor = new stdClass();
        $complemento->nomina->emisor->registro_patronal = $nom_nomina->em_registro_patronal_descripcion;
        $complemento->nomina->emisor->rfc_patron_origen = $nom_nomina->org_empresa_rfc;
        $complemento->nomina->receptor = new stdClass();
        $complemento->nomina->receptor->curp = $nom_nomina->em_empleado_curp;
        $complemento->nomina->receptor->nss = $nom_nomina->em_empleado_nss;
        $complemento->nomina->receptor->fecha_inicio_rel_laboral = $nom_nomina->em_empleado_fecha_inicio_rel_laboral;
        $complemento->nomina->receptor->revisar = $nom_nomina->em_registro_patronal_descripcion;
        $complemento->nomina->receptor->tipo_contrato = $nom_nomina->cat_sat_tipo_contrato_nom_codigo;
        $complemento->nomina->receptor->tipo_jornada = $nom_nomina->cat_sat_tipo_jornada_nom_codigo;
        $complemento->nomina->receptor->tipo_regimen = $nom_nomina->cat_sat_tipo_contrato_nom_codigo;
        $complemento->nomina->receptor->num_empleado = $nom_nomina->em_empleado_codigo;
        $complemento->nomina->receptor->departamento = $nom_nomina->org_departamento_descripcion;
        $complemento->nomina->receptor->puesto = $nom_nomina->org_puesto_descripcion;
        $complemento->nomina->receptor->riesgo_puesto = $nom_nomina->em_clase_riesgo_codigo;
        $complemento->nomina->receptor->periodicidad_pago = $nom_nomina->cat_sat_periodicidad_pago_nom_codigo;
        $complemento->nomina->receptor->cuenta_bancaria = $nom_nomina->em_cuenta_bancaria_clabe;
        $complemento->nomina->receptor->salario_base = $nom_nomina->em_empleado_salario_diario_integrado;
        $complemento->nomina->receptor->salario_diario_integrado = $nom_nomina->em_empleado_salario_diario_integrado;
        $complemento->nomina->receptor->clave_ent = $nom_nomina->dp_estado_codigo;

        $total_percepciones_gravado = $this->total_percepciones_gravado(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_percepciones_gravado);
        }

        $total_percepciones_exento = $this->total_percepciones_exento(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_percepciones_exento);
        }

        $total_percepciones = $this->total_percepciones_monto(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener total percepciones', data: $total_percepciones);
        }

        $percepciones = $this->percepciones(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener percepciones', data: $percepciones);
        }

        $deducciones = $this->deducciones(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener deducciones', data: $deducciones);
        }

        $otros_pagos = $this->otros_pagos(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener otros_pagos', data: $otros_pagos);
        }

        $salida_percepciones = array();

        foreach ($percepciones as $index => $percepcion) {
            $salida_percepciones[$index] = new stdClass();
            $salida_percepciones[$index]->tipo_percepcion = $percepcion['cat_sat_tipo_percepcion_nom_codigo'];
            $salida_percepciones[$index]->clave = $percepcion['nom_percepcion_codigo'];
            $salida_percepciones[$index]->concepto = $percepcion['nom_percepcion_descripcion'];
            $salida_percepciones[$index]->importe_gravado = $percepcion['nom_par_percepcion_importe_gravado'];
            $salida_percepciones[$index]->importe_exento = $percepcion['nom_par_percepcion_importe_exento'];
        }

        $complemento->nomina->percepciones = new stdClass();
        $complemento->nomina->percepciones->total_sueldos = $total_percepciones;
        $complemento->nomina->percepciones->total_gravado = $total_percepciones_gravado;
        $complemento->nomina->percepciones->total_exento = $total_percepciones_exento;
        $complemento->nomina->percepciones->conceptos = $salida_percepciones;

        $total_deducciones = $this->total_otras_deducciones_monto(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener total deducciones', data: $total_deducciones);
        }

        $total_impuestos_retenidos = $this->total_impuestos_retenidos_monto(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener total impuestos retenidos', data: $total_impuestos_retenidos);
        }

        $salida_deducciones = array();

        foreach ($deducciones as $index => $deduccion) {
            $salida_deducciones[$index] = new stdClass();
            $salida_deducciones[$index]->tipo_deduccion = $deduccion['cat_sat_tipo_deduccion_nom_codigo'];
            $salida_deducciones[$index]->clave = $deduccion['nom_deduccion_codigo'];
            $salida_deducciones[$index]->concepto = $deduccion['nom_deduccion_descripcion'];
            $salida_deducciones[$index]->importe = $deduccion['nom_par_deduccion_importe_gravado'] + $deduccion['nom_par_deduccion_importe_exento'];
        }

        $complemento->nomina->deducciones = new stdClass();
        $complemento->nomina->deducciones->total_otras_deducciones = $total_deducciones;
        $complemento->nomina->deducciones->total_impuestos_retenidos = $total_impuestos_retenidos;
        $complemento->nomina->deducciones->conceptos = $salida_deducciones;

        $salida_otros_pagos = array();

        foreach ($otros_pagos as $index => $otros_pago) {
            $salida_otros_pagos[$index] = new stdClass();
            $salida_otros_pagos[$index]->tipo_otro_pago = $otros_pago['cat_sat_tipo_otro_pago_nom_codigo'];
            $salida_otros_pagos[$index]->clave = $otros_pago['nom_otro_pago_codigo'];
            $salida_otros_pagos[$index]->concepto = $otros_pago['nom_otro_pago_descripcion'];
            $salida_otros_pagos[$index]->importe = $otros_pago['nom_par_otro_pago_importe_gravado'];
            $salida_otros_pagos[$index]->subsidio_al_empleo = array("subsidio_causado" => $otros_pago['nom_par_otro_pago_importe_exento']);
        }

        $complemento->nomina->otros_pagos = new stdClass();
        $complemento->nomina->otros_pagos->conceptos = $salida_otros_pagos;

        return $complemento;
    }

    private function data_json(stdClass $nom_nomina): bool|string|array
    {
        $fc_factura = (new fc_factura($this->link))->registro(registro_id: $nom_nomina->fc_factura_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener factura', data: $fc_factura);
        }

        $com_sucursal = (new com_sucursal(link: $this->link))->registro(registro_id: $fc_factura->com_sucursal_id,
            retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener com_sucursal', data: $com_sucursal);
        }

        $comprobante = $this->data_comprobante(fc_factura: $fc_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al crear comprobante', data: $comprobante);
        }

        $emisor = $this->data_emisor(fc_factura: $fc_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al crear emisor', data: $emisor);
        }

        $receptor = $this->data_receptor(fc_factura: $fc_factura, com_sucursal: $com_sucursal);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al crear receptor', data: $receptor);
        }

        $conceptos = $this->data_conceptos(fc_factura: $fc_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al crear conceptos', data: $conceptos);
        }

        $complemento = $this->data_complemento(nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al crear complemento', data: $complemento);
        }

        $fecha_cfdi = (new fechas())->fecha_cfdi(comprobante: $comprobante);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al calcular fecha', data: $fecha_cfdi);
        }

        $comprobante->Fecha = $fecha_cfdi;

        $data = new stdClass();
        $data->Comprobante = $comprobante;
        $data->Comprobante->Emisor = $emisor;
        $data->Comprobante->Receptor = $receptor;
        $data->Comprobante->Conceptos = $conceptos;
        $data->Comprobante->Complemento = $complemento;

        return json_encode($data);
    }

    private function document_actions(string $json, stdClass $nom_nomina): bool|string|array|stdClass
    {
        $existe_nomina = (new nom_nomina_documento(link: $this->link))->existe(array('nom_nomina.id' => $nom_nomina->nom_nomina_id,
            "doc_tipo_documento.descripcion" => "json"));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe documento', data: $existe_nomina);
        }

        $existe_factura = (new fc_factura_documento(link: $this->link))->existe(array('fc_factura.id' => $nom_nomina->fc_factura_id,
            "doc_tipo_documento.descripcion" => "json"));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe documento', data: $existe_factura);
        }

        $ruta_archivos_tmp = (new fc_factura(link: $this->link))->genera_ruta_archivo_tmp();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener ruta de archivos', data: $ruta_archivos_tmp);
        }

        $doc_tipo_documento_id = $this->doc_tipo_documento_id(extension: "json");
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar extension del documento', data: $doc_tipo_documento_id);
        }

        $file_xml_st = $ruta_archivos_tmp . '/' . $nom_nomina->nom_nomina_id . '.json';
        file_put_contents($file_xml_st, $json);

        if (!$existe_nomina && !$existe_factura) {
            $file['name'] = $file_xml_st;
            $file['tmp_name'] = $file_xml_st;

            $documento['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $documento['descripcion'] = $ruta_archivos_tmp;

            $documento = (new doc_documento(link: $this->link))->alta_documento(registro: $documento, file: $file);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al guardar xml', data: $documento);
            }

            $nom_nomina_documento = array();
            $nom_nomina_documento['nom_nomina_id'] = $nom_nomina->nom_nomina_id;
            $nom_nomina_documento['doc_documento_id'] = $documento->registro_id;

            $nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->alta_registro(registro: $nom_nomina_documento);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta nomina documento', data: $nom_nomina_documento);
            }

            $fc_factura_documento = array();
            $fc_factura_documento['fc_factura_id'] = $nom_nomina->fc_factura_id;
            $fc_factura_documento['doc_documento_id'] = $documento->registro_id;

            $fc_factura_documento = (new fc_factura_documento(link: $this->link))->alta_registro(registro: $fc_factura_documento);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta factura documento', data: $fc_factura_documento);
            }

        } else if ($existe_nomina && !$existe_factura) {
            $nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->filtro_and(
                filtro: array('nom_nomina.id' => $nom_nomina->nom_nomina_id, "doc_tipo_documento.descripcion" => "json"));
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener nomina documento', data: $nom_nomina_documento);
            }

            if ($nom_nomina_documento->n_registros > 1) {
                return $this->error->error(mensaje: 'Error solo debe existir una nomina documento', data: $nom_nomina_documento);
            }

            $nomina_documento = $nom_nomina_documento->registros[0];

            $doc_documento_id = $nomina_documento['doc_documento_id'];

            $registro['descripcion'] = $ruta_archivos_tmp;
            $registro['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $_FILES['name'] = $file_xml_st;
            $_FILES['tmp_name'] = $file_xml_st;

            $documento = (new doc_documento(link: $this->link))->modifica_bd(registro: $registro, id: $doc_documento_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al modificar documento', data: $documento);
            }

            $fc_factura_documento = array();
            $fc_factura_documento['fc_factura_id'] = $nom_nomina->fc_factura_id;
            $fc_factura_documento['doc_documento_id'] = $documento->registro_id;

            $fc_factura_documento = (new fc_factura_documento(link: $this->link))->alta_registro(registro: $fc_factura_documento);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta factura documento', data: $fc_factura_documento);
            }
        } else if (!$existe_nomina && $existe_factura) {
            $fc_factura_documento = (new fc_factura_documento(link: $this->link))->filtro_and(
                filtro: array('fc_factura.id' => $nom_nomina->fc_factura_id, "doc_tipo_documento.descripcion" => "json"));
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener nomina documento', data: $fc_factura_documento);
            }

            if ($fc_factura_documento->n_registros > 1) {
                return $this->error->error(mensaje: 'Error solo debe existir una factura documento', data: $fc_factura_documento);
            }

            $factura_documento = $fc_factura_documento->registros[0];

            $doc_documento_id = $factura_documento['doc_documento_id'];

            $registro['descripcion'] = $ruta_archivos_tmp;
            $registro['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $_FILES['name'] = $file_xml_st;
            $_FILES['tmp_name'] = $file_xml_st;

            $documento = (new doc_documento(link: $this->link))->modifica_bd(registro: $registro, id: $doc_documento_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al modificar documento', data: $documento);
            }

            $nom_nomina_documento = array();
            $nom_nomina_documento['nom_nomina_id'] = $nom_nomina->nom_nomina_id;
            $nom_nomina_documento['doc_documento_id'] = $documento->registro_id;

            $nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->alta_registro(registro: $nom_nomina_documento);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta nomina documento', data: $nom_nomina_documento);
            }
        } else {
            $nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->filtro_and(
                filtro: array('nom_nomina.id' => $nom_nomina->nom_nomina_id, "doc_tipo_documento.descripcion" => "json"));
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener nomina documento', data: $nom_nomina_documento);
            }

            if ($nom_nomina_documento->n_registros > 1) {
                return $this->error->error(mensaje: 'Error solo debe existir una nomina documento', data: $nom_nomina_documento);
            }

            $fc_factura_documento = (new fc_factura_documento(link: $this->link))->filtro_and(
                filtro: array('fc_factura.id' => $nom_nomina->fc_factura_id, "doc_tipo_documento.descripcion" => "json"));
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener nomina documento', data: $fc_factura_documento);
            }

            if ($fc_factura_documento->n_registros > 1) {
                return $this->error->error(mensaje: 'Error solo debe existir una factura documento', data: $fc_factura_documento);
            }

            $nomina_documento = $nom_nomina_documento->registros[0];

            $doc_documento_id = $nomina_documento['doc_documento_id'];

            $registro['descripcion'] = $ruta_archivos_tmp;
            $registro['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $_FILES['name'] = $file_xml_st;
            $_FILES['tmp_name'] = $file_xml_st;

            $documento = (new doc_documento(link: $this->link))->modifica_bd(registro: $registro, id: $doc_documento_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al modificar documento', data: $documento);
            }
        }

        $salida = new stdClass();
        $salida->registro = (new doc_documento(link: $this->link))->registro(registro_id: $documento->registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error  al obtener documento', data: $documento);
        }
        $salida->file_xml_st = $file_xml_st;

        return $salida;
    }

    public function genera_json(stdClass $nom_nomina, string $tipo): array|stdClass
    {
        $json = $this->data_json(nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener data JSON', data: $json);
        }

        $ruta_archivos_tmp = (new fc_factura(link: $this->link))->genera_ruta_archivo_tmp();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener ruta de archivos', data: $ruta_archivos_tmp);
        }

        $acciones_documento = $this->document_actions(json: $json, nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar acciones para el documento', data: $acciones_documento);
        }

        $rutas = new stdClass();
        $rutas->file_xml_st = $acciones_documento->file_xml_st;
        $rutas->doc_documento_ruta_absoluta = $acciones_documento->registro['doc_documento_ruta_absoluta'];

        return $rutas;
    }

    public function genera_xml_v2(stdClass $nom_nomina)
    {
        $xml = (new xml_nom())->xml(link: $this->link, nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar xml', data: $xml);
        }

        $ruta_archivos_tmp = (new fc_factura($this->link))->genera_ruta_archivo_tmp();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar ruta de archivos', data: $ruta_archivos_tmp);
        }

        $documento = array();
        $file = array();
        $file_xml_st = $ruta_archivos_tmp . '/' . $nom_nomina->nom_nomina_id . '.nom.xml';
        file_put_contents($file_xml_st, $xml);

        $existe = (new nom_nomina_documento(link: $this->link))->existe(array('nom_nomina.id' => $nom_nomina->nom_nomina_id,
            "doc_tipo_documento.descripcion" => "xml_cfdi_nomina"));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe documento', data: $existe);
        }

        $doc_tipo_documento_id = $this->doc_tipo_documento_id(extension: "xml");
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar extension del documento', data: $doc_tipo_documento_id);
        }

        if (!$existe) {

            $doc_documento_modelo = new doc_documento(link: $this->link);

            $file['name'] = $file_xml_st;
            $file['tmp_name'] = $file_xml_st;

            $doc_documento_modelo->registro['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $doc_documento_modelo->registro['descripcion'] = $ruta_archivos_tmp;

            $documento = $doc_documento_modelo->alta_bd(file: $file);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al guardar xml', data: $documento);
            }

            $nom_nomina_documento = array();
            $nom_nomina_documento['nom_nomina_id'] = $nom_nomina->nom_nomina_id;
            $nom_nomina_documento['doc_documento_id'] = $documento->registro_id;

            $nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->alta_registro(registro: $nom_nomina_documento);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta factura documento', data: $nom_nomina_documento);
            }
        } else {
            $r_nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->filtro_and(
                filtro: array('nom_nomina.id' => $nom_nomina->nom_nomina_id, "doc_tipo_documento.descripcion" => "xml_cfdi_nomina"));
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener factura documento', data: $r_nom_nomina_documento);
            }

            if ($r_nom_nomina_documento->n_registros > 1) {
                return $this->error->error(mensaje: 'Error solo debe existir un documento de nomina', data: $r_nom_nomina_documento);
            }
            if ($r_nom_nomina_documento->n_registros === 0) {
                return $this->error->error(mensaje: 'Error  debe existir al menos un documento de nomina', data: $r_nom_nomina_documento);
            }
            $nom_nomina_documento = $r_nom_nomina_documento->registros[0];

            $doc_documento_id = $nom_nomina_documento['doc_documento_id'];

            $registro['descripcion'] = $ruta_archivos_tmp;
            $registro['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $_FILES['name'] = $file_xml_st;
            $_FILES['tmp_name'] = $file_xml_st;

            $documento = (new doc_documento(link: $this->link))->modifica_bd(registro: $registro, id: $doc_documento_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al modificar documento', data: $r_nom_nomina_documento);
            }

            $documento->registro = (new doc_documento(link: $this->link))->registro(registro_id: $documento->registro_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al obtener documento', data: $documento);
            }
        }

        $rutas = new stdClass();
        $rutas->file_xml_st = $file_xml_st;
        $rutas->doc_documento_ruta_absoluta = $documento->registro['doc_documento_ruta_absoluta'];

        return $rutas;
    }

    public function get_datos_xml_v2(string $ruta_xml = ""): array
    {
        $xml = simplexml_load_file($ruta_xml);
        $ns = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('c', $ns['cfdi']);
        $xml->registerXPathNamespace('t', $ns['tfd']);

        $xml_data = array();
        $xml_data['cfdi_comprobante'] = array();
        $xml_data['cfdi_emisor'] = array();
        $xml_data['cfdi_receptor'] = array();
        $xml_data['cfdi_conceptos'] = array();
        $xml_data['tfd'] = array();

        $nodos = array();
        $nodos[] = '//cfdi:Comprobante';
        $nodos[] = '//cfdi:Comprobante//cfdi:Emisor';
        $nodos[] = '//cfdi:Comprobante//cfdi:Receptor';
        $nodos[] = '//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto';
        $nodos[] = '//t:TimbreFiscalDigital';

        foreach ($nodos as $key => $nodo) {
            foreach ($xml->xpath($nodo) as $value) {
                $data = (array)$value->attributes();
                $data = $data['@attributes'];
                $xml_data[array_keys($xml_data)[$key]] = $data;
            }
        }
        return $xml_data;
    }

    public function timbra_json(int $nom_nomina_id): array|stdClass
    {
        $nom_nomina = $this->registro(registro_id: $nom_nomina_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo obtener la nomina', data: $nom_nomina);
        }

        /*$permite_transaccion = (new fc_factura($this->link))->verifica_permite_transaccion(registro_id: $nom_nomina->fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error verificar transaccion', data: $permite_transaccion);
        }*/

        $timbrada = (new fc_cfdi_sellado($this->link))->existe(filtro: array('fc_factura.id' => $nom_nomina->fc_factura_id));
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo validar si la nomina esta timbrada', data: $timbrada);
        }

        if ($timbrada) {
            return $this->error->error(mensaje: 'La nomina ya ha sido timbrada', data: $timbrada);
        }

        $tipo = (new pac())->tipo;

        $json = $this->genera_json(nom_nomina: $nom_nomina, tipo: $tipo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo generar el archivo JSON', data: $json);
        }

        $xml = $this->genera_xml_v2(nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo generar el archivo XML', data: $xml);
        }

        $json_contenido = file_get_contents($json->doc_documento_ruta_absoluta);

        $filtro_files['fc_csd.id'] = $nom_nomina->fc_factura_fc_csd_id;
        $r_fc_key_pem = (new fc_key_pem(link: $this->link))->filtro_and(filtro: $filtro_files);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo obtener key', data: $r_fc_key_pem);
        }

        if ($r_fc_key_pem->n_registros === 0) {
            return $this->error->error(mensaje: 'No existe un key pem asignado', data: $r_fc_key_pem);
        }

        $ruta_key_pem = '';
        if ((int)$r_fc_key_pem->n_registros === 1) {
            $ruta_key_pem = $r_fc_key_pem->registros[0]['doc_documento_ruta_absoluta'];
        }

        $r_fc_cer_pem = (new fc_cer_pem(link: $this->link))->filtro_and(filtro: $filtro_files);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo obtener cer', data: $r_fc_cer_pem);
        }
        $ruta_cer_pem = '';
        if ((int)$r_fc_cer_pem->n_registros === 1) {
            $ruta_cer_pem = $r_fc_cer_pem->registros[0]['doc_documento_ruta_absoluta'];
        }

        $pac_prov = (new pac())->pac_prov;
        $json_timbrado = (new timbra())->timbra(contenido_xml: $json_contenido, id_comprobante: '',
            ruta_cer_pem: $ruta_cer_pem, ruta_key_pem: $ruta_key_pem, pac_prov: $pac_prov);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo timbrar el archivo', data: $json_timbrado);
        }

        file_put_contents(filename: $xml->doc_documento_ruta_absoluta, data: $json_timbrado->xml_sellado);

        $qr_code = $json_timbrado->qr_code;
        if ((new pac())->base_64_qr) {
            $qr_code = base64_decode($qr_code);
        }

        $alta_qr = $this->guarda_documento(directorio: "codigos_qr", extension: "jpg", contenido: $qr_code,
            fc_factura_id: $nom_nomina->fc_factura_id, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo guardar QR', data: $alta_qr);
        }

        $alta_txt = $this->guarda_documento(directorio: "textos", extension: "txt", contenido: $json_timbrado->txt,
            fc_factura_id: $nom_nomina->fc_factura_id, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo guardar TXT', data: $alta_txt);
        }

        $datos_xml = $this->get_datos_xml_v2(ruta_xml: $xml->doc_documento_ruta_absoluta);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo obtener datos del XML', data: $datos_xml);
        }

        $cfdi_sellado = (new fc_cfdi_sellado($this->link))->maqueta_datos(codigo: $datos_xml['cfdi_comprobante']['NoCertificado'],
            descripcion: $datos_xml['cfdi_comprobante']['NoCertificado'],
            comprobante_sello: $datos_xml['cfdi_comprobante']['Sello'], comprobante_certificado: $datos_xml['cfdi_comprobante']['Certificado'],
            comprobante_no_certificado: $datos_xml['cfdi_comprobante']['NoCertificado'], complemento_tfd_sl: "",
            complemento_tfd_fecha_timbrado: $datos_xml['tfd']['FechaTimbrado'],
            complemento_tfd_no_certificado_sat: $datos_xml['tfd']['NoCertificadoSAT'], complemento_tfd_rfc_prov_certif: $datos_xml['tfd']['RfcProvCertif'],
            complemento_tfd_sello_cfd: $datos_xml['tfd']['SelloCFD'], complemento_tfd_sello_sat: $datos_xml['tfd']['SelloSAT'],
            uuid: $datos_xml['tfd']['UUID'], complemento_tfd_tfd: "", cadena_complemento_sat: $json_timbrado->txt,key_entidad_id: '',
            registro_id: $nom_nomina->fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo maquetar datos para cfdi sellado', data: $cfdi_sellado);
        }

        $alta = (new fc_cfdi_sellado($this->link))->alta_registro(registro: $cfdi_sellado);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo dar de alta cfdi sellado', data: $alta);
        }

        /*$r_alta_factura_etapa = (new pr_proceso(link: $this->link))->inserta_etapa(adm_accion: __FUNCTION__, fecha: '',
            modelo: $this, modelo_etapa: (new fc_factura($this->link))->modelo_etapa, registro_id: $nom_nomina->fc_factura_id,
            valida_existencia_etapa: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar etapa', data: $r_alta_factura_etapa);
        }*/

        return $cfdi_sellado;
    }

    public function genera_documentos(int $nom_nomina_id): array|stdClass
    {
        $nom_nomina = $this->registro(registro_id: $nom_nomina_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo obtener la nomina', data: $nom_nomina);
        }

        /*$permite_transaccion = (new fc_factura($this->link))->verifica_permite_transaccion(registro_id: $nom_nomina->fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error verificar transaccion', data: $permite_transaccion);
        }*/

        $timbrada = (new fc_cfdi_sellado($this->link))->existe(filtro: array('fc_factura.id' => $nom_nomina->fc_factura_id));
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo validar si la nomina esta timbrada', data: $timbrada);
        }

        if ($timbrada) {
            return $this->error->error(mensaje: 'La nomina ya ha sido timbrada', data: $timbrada);
        }

        $tipo = (new pac())->tipo;

        $json = $this->genera_json(nom_nomina: $nom_nomina, tipo: $tipo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo generar el archivo JSON', data: $json);
        }

        $xml = $this->genera_xml_v2(nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'No se pudo generar el archivo XML', data: $xml);
        }



        return $json;
    }

    public function descarga_recibo_nomina_zip_v2(array|stdClass $nom_nominas)
    {
        $zip = new ZipArchive();
        $nombreZip = 'Recibos por periodo.zip';
        $zip->open($nombreZip, ZipArchive::CREATE);

        $contador = 1;

        foreach ($nom_nominas->registros as $nomina) {
            try {
                $temporales = (new generales())->path_base . "archivos/tmp/";
                $pdf = new Mpdf(['tempDir' => $temporales]);
            } catch (Throwable $e) {
                return $this->error->error('Error al generar objeto de pdf', $e);
            }

            $r_pdf = $this->crea_pdf_recibo_nomina(nom_nomina_id: $nomina->nom_nomina_id ,pdf: $pdf);
            $archivo_pdf = $pdf->Output('','S');

            $timbrada = (new fc_cfdi_sellado($this->link))->existe(filtro: array('fc_factura.id' => $nomina->fc_factura_id));
            if (errores::$error) {
                return $this->error->error(mensaje: 'No se pudo validar si la nomina esta timbrada', data: $timbrada);
            }

            $ruta_xml = '';
            $archivo_xml = '';
            if ($timbrada) {
                $xml_documento = (new nom_nomina_documento(link: $this->link))->filtro_and(
                    filtro: array('nom_nomina.id' => $nomina->nom_nomina_id, "doc_tipo_documento.descripcion" => "xml_cfdi_nomina"),limit: 1);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al obtener nomina documento', data: $xml_documento);
                }

                $ruta_xml = $xml_documento->registros[0]['doc_documento_ruta_absoluta'];
                $archivo_xml = file_get_contents($xml_documento->registros[0]['doc_documento_ruta_absoluta']);

            } else {
                $xml_documento = (new nom_nomina_documento(link: $this->link))->filtro_and(
                    filtro: array('nom_nomina.id' => $nomina->nom_nomina_id, "doc_tipo_documento.descripcion" => "xml_cfdi_nomina"),limit: 1);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al obtener nomina documento', data: $xml_documento);
                }

                if($xml_documento->n_registros > 0){
                    $ruta_xml = $xml_documento->registros[0]['doc_documento_ruta_absoluta'];
                    $archivo_xml = file_get_contents($xml_documento->registros[0]['doc_documento_ruta_absoluta']);
                }
            }

            $zip->addFromString($nomina->nom_nomina_descripcion.$contador.'.pdf', $archivo_pdf);
            if($ruta_xml !== ''){
                $zip->addFromString($nomina->nom_nomina_descripcion.$contador.'.xml', $archivo_xml);
            }
            $contador ++;
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $nombreZip);
        header('Content-Length: ' . filesize($nombreZip));
        readfile($nombreZip);

        unlink($nombreZip);
        exit;
    }
}