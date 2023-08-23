<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_conf_comision $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_modifica_bd; ?>">

    <?php echo $controlador->inputs->com_sucursal_id; ?>
    <?php echo $controlador->inputs->descripcion; ?>
    <?php echo $controlador->inputs->porcentaje; ?>
    <?php echo $controlador->inputs->fecha_inicio; ?>
    <?php echo $controlador->inputs->fecha_fin; ?>


    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Actualizar</button>
    </div>
</form>

