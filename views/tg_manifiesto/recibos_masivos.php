<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_periodo $controlador */ ?>
<?php

use config\views;
$url_icons = (new views())->url_icons;
?>

<?php include 'templates/nom_periodo/recibos_masivos/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="lista">
            <div class="card">
                <div class="card-header">
                    <span class="text-header">Recibos Masivos</span>

                    <div class="dropdown ">
                        <button class="btn btn-icon-only " type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <button type="submit" class="dropdown-item" name="btn_action_next"
                                    value="ve_nominas" form="form_agregar_percepcion">
                                Agregar Percepción
                            </button>
                            <button type="submit" class="dropdown-item" name="btn_action_next"
                                    value="ve_nominas" form="form_agregar_deduccion">
                                Agregar Deducción
                            </button>
                            <button type="submit" class="dropdown-item" name="btn_action_next"
                                    value="ve_nominas" form="form_agregar_otro_pago">
                                Agregar Otro Pago
                            </button>

                            <!--<a class="dropdown-item" href="#">Something else here</a>-->
                        </div>
                    </div>


                </div>
                <div class="card-body">
                    <div class="cont_tabla_sucursal  col-md-12">
                        <table id="nom_nomina" class="datatables table table-striped "></table>
                    </div>
                    <div class="col-sm-2">
                        <a href="<?php echo $controlador->link_lista; ?>"
                           class="btn btn-info btn-guarda col-md-12"><i class="icon-edit"></i>Regresar
                        </a>
                    </div>
                    <div class="col-sm-2">
                        <form method="post" action="<?php echo $controlador->link_tg_manifiesto_agregar_percepcion; ?> "
                              class="form-additional form_nominas " id="form_agregar_percepcion">
                            <input id="agregar_percepcion" name="agregar_percepcion" type="hidden">
                            <div class="botones">
                                <button type="submit" class="btn btn-info" name="btn_action_next"
                                        value="exportar" form="form_agregar_percepcion">
                                    Agregar Percepción
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="col-sm-2">
                        <form method="post" action="<?php echo $controlador->link_tg_manifiesto_agregar_deduccion; ?> "
                              class="form-additional form_nominas " id="form_agregar_deduccion">
                            <input id="agregar_deduccion" name="agregar_deduccion" type="hidden">
                            <div class="botones">
                                <button type="submit" class="btn btn-info" name="btn_action_next"
                                        value="exportar" form="form_agregar_deduccion">
                                    Agregar Deducción
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="col-sm-2">
                        <form method="post" action="<?php echo $controlador->link_tg_manifiesto_agregar_otro_pago; ?> "
                              class="form-additional form_nominas " id="form_agregar_otro_pago">
                            <input id="agregar_otro_pago" name="agregar_otro_pago" type="hidden">
                            <div class="botones">
                                <button type="submit" class="btn btn-info" name="btn_action_next"
                                        value="exportar" form="form_agregar_otro_pago">
                                    Agregar Otro Pago
                                </button>
                            </div>
                        </form>
                    </div>

                   <!-- <div class="col-sm-2">
                        <form method="post" action="<?php /*echo $controlador->link_tg_manifiesto_elimina_percepciones; */?> "
                              class="form-additional" id="form_export">
                            <input id="percepciones_eliminar" name="percepciones_eliminar" type="hidden">
                            <div class="botones">
                                <button type="submit" class="btn btn-info" name="btn_action_next"
                                        value="exportar" form="form_export">
                                    Eliminar Percepciones
                                </button>
                            </div>
                        </form>
                    </div>-->

                </div>
            </div>
        </div>

        <div class="lista">
            <div class="card">
                <div class="card-header">
                    <span class="text-header">Nominas Seleccionadas</span>
                </div>
                <div class="card-body tablas_nominas ">
                    <div class="col-md-12">
                        <div class="tabla_titulo">
                            <span class="text-header">Percepciones</span>
                        </div>
                        <table id="nominas_percepciones" class="datatables table table-striped "></table>
                    </div>
                    <div class="col-md-12">
                        <div class="tabla_titulo">
                            <span class="text-header">Deducciones</span>
                        </div>
                        <table id="nominas_deducciones" class="datatables table table-striped "></table>
                    </div>
                    <div class="col-md-12">
                        <div class="tabla_titulo">
                            <span class="text-header">Otros pagos</span>
                        </div>
                        <table id="nominas_otros_pagos" class="datatables table table-striped "></table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

