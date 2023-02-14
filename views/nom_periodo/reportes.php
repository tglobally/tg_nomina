<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_periodo $controlador */ ?>

<?php (new \tglobally\template_tg\template())->sidebar($controlador); ?>

<div class="col-md-9 info-lista">
    <div class="col-lg-12 content">
        <h3 class="text-center titulo-form">
            Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?></h3>

        <div class="lista">
            <div class="card">

                <div class="card-body">
                    <div class="cont_tabla_sucursal  col-md-12">
                        <div class="botones" style="display: flex; justify-content: flex-end; align-items: center">
                            <form method="post" action="<?php echo $controlador->link_nom_periodo_exportar; ?> "
                                  class="form-additional" id="form_export" style="width: 100%">
                                <?php echo $controlador->inputs->filtro_fecha_inicio; ?>
                                <?php echo $controlador->inputs->filtro_fecha_final; ?>
                            </form>
                            <button type="submit" class="btn btn-info" name="btn_action_next"
                                    style="border-radius: 5px" value="exportar" form="form_export">
                                Exportar
                            </button>
                        </div>
                        <table id="nom_nomina" class="table table-striped "></table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
