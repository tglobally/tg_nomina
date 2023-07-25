<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_conf_provision $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_alta_bd; ?>">

    <?php echo $controlador->inputs->nom_conf_empleado_id; ?>
    <?php echo $controlador->inputs->tg_tipo_provision_id; ?>
    <?php echo $controlador->inputs->codigo; ?>
    <?php echo $controlador->inputs->descripcion; ?>
    <?php echo $controlador->inputs->monto; ?>
    <?php echo $controlador->inputs->fecha_inicio; ?>
    <?php echo $controlador->inputs->fecha_fin; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>
