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
                    <span class="text-header">Recibos masivos</span>

                    <div class="dropdown ">
                        <button class="btn btn-icon-only " type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <button type="submit" class="dropdown-item" name="btn_action_next"
                                    value="ve_nominas" form="form_descarga_pdf">
                                Descargar PDF
                            </button>
                            <button type="submit" class="dropdown-item" name="btn_action_next"
                                    value="ve_nominas" form="form_descarga_comprimido">
                                Descargar Comprimido
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
                        <form method="post" action="<?php echo $controlador->link_nom_periodo_descarga_pdf; ?> "
                              class="form-additional form_nominas " id="form_descarga_pdf">
                            <input id="descarga_pdf" name="descarga_pdf" type="hidden">
                            <div class="botones">
                                <button type="submit" class="btn btn-info" name="btn_action_next"
                                        value="exportar" form="form_descarga_pdf">
                                    Descargar PDF
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="col-sm-2">
                        <form method="post" action="<?php echo $controlador->link_nom_periodo_descarga_comprimido; ?> "
                              class="form-additional form_nominas " id="form_descarga_comprimido">
                            <input id="descarga_comprimido" name="descarga_comprimido" type="hidden">
                            <div class="botones">
                                <button type="submit" class="btn btn-info" name="btn_action_next"
                                        value="exportar" form="form_descarga_comprimido">
                                    Descargar Comprimido
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
