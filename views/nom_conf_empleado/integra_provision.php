<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_conf_empleado $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_nom_conf_empleado_integra_provision_alta_bd; ?>">

    <?php echo $controlador->inputs->em_empleado_id; ?>
    <?php echo $controlador->inputs->tg_tipo_provision_id; ?>
    <?php echo $controlador->inputs->nom_nomina_id; ?>
    <?php echo $controlador->inputs->descripcion; ?>
    <?php echo $controlador->inputs->monto; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Actualizar</button>
    </div>
</form>


