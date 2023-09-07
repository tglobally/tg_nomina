<?php /** @var \tglobally\tg_nomina\models\tg_sucursal_alianza $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_modifica_bd; ?>">

    <?php echo $controlador->inputs->com_sucursal_id; ?>
    <?php echo $controlador->inputs->tg_cte_alianza_id; ?>
    <?php echo $controlador->inputs->descripcion; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Actualizar</button>
    </div>
</form>

