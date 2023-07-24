<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_tipo_servicio $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_alta_bd; ?>">

    <?php echo $controlador->inputs->codigo; ?>
    <?php echo $controlador->inputs->codigo_bis; ?>
    <?php echo $controlador->inputs->descripcion; ?>
    <?php echo $controlador->inputs->descripcion_select; ?>
    <?php echo $controlador->inputs->alias; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>


