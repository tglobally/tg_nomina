<?php /** @var \tglobally\tg_empleado\controllers\controlador_em_empleado $controlador */ ?>
<?php (new \tglobally\template_tg\template())->sidebar($controlador); ?>

<div class="col-md-9 info-lista">
    <div class="col-lg-12 content">
        <h3 class="text-center titulo-form">
            Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?></h3>

        <div class="lista">
            <div class="card">

                <div class="card-body">
                    <div class="cont_tabla_sucursal  col-md-12">
                        <form method="post" action="<?php echo $controlador->link_em_empleado_exportar; ?> "
                              class="form-additional" id="form_export">

                            <div class="filtros">

                                <div class="filtro-titulo">
                                    <h3>Estimado usuario, por favor seleccione una opción de busqueda:</h3>
                                </div>

                                <div class="filtro-categorias">
                                    
                                </div>
                                <div class="filtro-reportes">
                                    <div class="filtro-fechas">
                                        <label>Rango Fechas</label>
                                        <div class="fechas form-main widget-form-cart">
                                            <?php echo $controlador->inputs->fecha_inicio; ?>
                                            <?php echo $controlador->inputs->fecha_final; ?>
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <div class="botones">
                                <button type="submit" class="btn btn-success export" name="btn_action_next"
                                        style="border-radius: 5px" value="exportar" form="form_export">
                                    Exportar
                                </button>
                            </div>
                        </form>
                        <table id="em_empleado" class="datatables table table-striped "></table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
