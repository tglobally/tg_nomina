<?php /** @var \tglobally\tg_empleado\controllers\controlador_em_empleado $controlador */ ?>

<?php (new \tglobally\template_tg\template())->sidebar(controlador: $controlador,seccion_step: 6); ?>

<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">
            Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="<?php echo $controlador->link_tg_empleado_sucursal_alta_bd; ?>"
                  class="form-additional">
                <?php echo $controlador->inputs->em_empleado_id; ?>
                <?php echo $controlador->inputs->com_sucursal_id; ?>

                <?php echo $controlador->inputs->hidden_row_id; ?>
                <?php echo $controlador->inputs->hidden_seccion_retorno; ?>
                <?php echo $controlador->inputs->hidden_id_retorno; ?>

                <div class="buttons col-md-12">
                    <div class="col-md-6 btn-ancho">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " name="btn_action_next"
                                value="asigna_sucursal">Asigna Sucursal
                        </button>
                    </div>
                    <div class="col-md-6 btn-ancho">
                        <a href="<?php echo $controlador->link_lista; ?>" class="btn btn-info btn-guarda col-md-12 ">Lista</a>
                    </div>

                </div>
            </form>
        </div>
    </div>
    <div class="lista">
        <div class="card">
            <div class="card-header">
                <span class="text-header">Sucursales Asignadas</span>
            </div>
            <div class="card-body">
                <?php echo $controlador->contenido_table; ?>
            </div> <!-- /. widget-table-->
        </div>
    </div>

</div>

