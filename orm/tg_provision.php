<?php
namespace tglobally\tg_nomina\models;

use base\orm\_modelo_parent;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class tg_provision extends _modelo_parent {

    public function __construct(PDO $link){
        $tabla = 'tg_provision';
        $columnas = array($tabla=>false, 'tg_tipo_provision'=>$tabla, 'nom_nomina' => $tabla,
            "nom_percepcion" => 'tg_tipo_provision');
        $campos_obligatorios = array('descripcion','codigo');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        if(!isset($this->registro['codigo'])){

            $this->registro['codigo'] =  $this->get_codigo_aleatorio();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo aleatorio',data:  $this->registro);
            }

            if (isset($this->registro['rfc'])){
                $this->registro['codigo'] = $this->registro['rfc'];
            }
        }

        $r_alta_bd = parent::alta_bd($keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta provision',data:  $r_alta_bd);
        }

        return $r_alta_bd;
    }

    public function maqueta_excel_provisiones(int $nom_nomina_id){

        $filtro = array();
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.descripcion'] = "Prima Vacacional";
        $registro_prima_vacacional = $this->filtro_and(filtro: $filtro,limit: 1,);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener provisiones de nomina',data:  $registro_prima_vacacional);
        }

        $filtro = array();
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.descripcion'] = "Vacaciones";
        $registro_vacaciones = $this->filtro_and(filtro: $filtro,limit: 1);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener provisiones de nomina',data:  $registro_vacaciones);
        }

        $filtro = array();
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.descripcion'] = "Aguinaldo";
        $registro_aguinaldo = $this->filtro_and(filtro: $filtro,limit: 1);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener provisiones de nomina',data:  $registro_aguinaldo);
        }

        $filtro = array();
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.descripcion'] = "Prima Antiguedad";
        $registro_prima_antiguedad = $this->filtro_and(filtro: $filtro,limit: 1);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener provisiones de nomina',data:  $registro_aguinaldo);
        }

        $filtro = array();
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_nomina.em_empleado_id'] = "Codigo";
        $registro_codigo= $this->filtro_and(filtro: $filtro,limit: 1);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener codigo de empleado',data:  $registro_codigo);
        }

        $filtro = array();
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_nomina.em_empleado_id'] = "Nombre Completo";
        $registro_nombre_completo = $this->filtro_and(filtro: $filtro,limit: 1);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener provisiones de nomina',data:  $registro_nombre_completo);
        }

        $prima_vacacional = "";
        $vacaciones = "";
        $aguinaldo = "";
        $prima_antiguedad = "";
        $nombre_completo = "";
        $codigo = "";

        if ($registro_prima_vacacional->n_registros > 0){
            $prima_vacacional = $registro_prima_vacacional->registros[0]['tg_provision_monto'];
        }

        if ($registro_vacaciones->n_registros > 0){
            $vacaciones = $registro_vacaciones->registros[0]['tg_provision_monto'];
        }

        if ($registro_aguinaldo->n_registros > 0){
            $aguinaldo = $registro_aguinaldo->registros[0]['tg_provision_monto'];
        }

        if ($registro_prima_antiguedad->n_registros > 0){
            $prima_antiguedad = $registro_prima_antiguedad->registros[0]['tg_provision_monto'];
        }

        $total_provisionado = (int)$prima_vacacional + (int)$vacaciones + (int)$aguinaldo + (int)$prima_antiguedad;

        $datos = array();
        $datos['codigo'] = $codigo;
        $datos['nombre_completo'] = $nombre_completo;
        $datos['prima_vacacional'] = $prima_vacacional;
        $datos['vacaciones'] = $vacaciones;
        $datos['aguinaldo'] = $aguinaldo;
        $datos['prima_antiguedad'] = $prima_antiguedad;
        $datos['total_provisionado'] = $total_provisionado;

        return $datos;
    }

}