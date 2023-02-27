<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_conf_provision $controlador */ ?>

<?php (new \tglobally\template_tg\template())->sidebar($controlador); ?>

<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="<?php echo $controlador->link_modifica_bd;?>" class="form-additional">

                <?php echo $controlador->inputs->nom_conf_empleado_id; ?>
                <?php echo $controlador->inputs->tg_tipo_provision_id; ?>
                <?php echo $controlador->inputs->codigo; ?>
                <?php echo $controlador->inputs->descripcion; ?>
                <?php echo $controlador->inputs->monto; ?>
                <?php echo $controlador->inputs->fecha_inicio; ?>
                <?php echo $controlador->inputs->fecha_fin; ?>

                <div class="buttons col-md-12">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " value="modifica">Guarda</button>
                    </div>
                    <div class="col-md-6 ">
                        <a href="<?php echo $controlador->link_lista; ?>"  class="btn btn-info btn-guarda col-md-12 ">Lista</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
