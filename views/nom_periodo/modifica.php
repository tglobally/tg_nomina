<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_periodo $controlador */ ?>

<?php (new \tglobally\template_tg\template())->sidebar($controlador); ?>


<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="<?php echo $controlador->link_modifica_bd;?>" class="form-additional">
                <?php echo $controlador->inputs->id; ?>
                <?php echo $controlador->inputs->codigo; ?>
                <?php echo $controlador->inputs->codigo_bis; ?>
                <?php echo $controlador->inputs->descripcion; ?>
                <?php echo $controlador->inputs->select->cat_sat_periodicidad_pago_nom_id; ?>
                <?php echo $controlador->inputs->select->em_registro_patronal_id; ?>
                <?php echo $controlador->inputs->select->nom_tipo_periodo_id; ?>
                <?php echo $controlador->inputs->select->cat_sat_tipo_nomina_id; ?>
                <?php echo $controlador->inputs->fecha_inicial_pago; ?>
                <?php echo $controlador->inputs->fecha_final_pago; ?>
                <?php echo $controlador->inputs->fecha_pago; ?>

                <div class="buttons col-md-12 ">
                    <div class="row mt-3">
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-info btn-success col-md-12 " value="modifica">Modifica</button>
                        </div>
                        <div class="col-sm-6">
                            <a href="<?php echo $controlador->link_lista; ?>"
                               class="btn btn-info btn-guarda col-md-12 "><i class="icon-edit"></i>Lista
                            </a>
                        </div>
                    </div>

            </form>
        </div>


    </div>
</div>

