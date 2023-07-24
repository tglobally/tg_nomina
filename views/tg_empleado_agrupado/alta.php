<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_empleado_agrupado $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_alta_bd; ?>">

    <?php echo $controlador->inputs->codigo; ?>
    <?php echo $controlador->inputs->descripcion; ?>
    <?php echo $controlador->inputs->em_empleado_id; ?>
    <?php echo $controlador->inputs->tg_agrupador_id; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>

