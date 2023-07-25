<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_conf_manifiesto $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_modifica_bd; ?>">

    <?php echo $controlador->inputs->codigo; ?>
    <?php echo $controlador->inputs->descripcion; ?>
    <?php echo $controlador->inputs->fc_csd_id; ?>
    <?php echo $controlador->inputs->tg_agrupador_id; ?>
    <?php echo $controlador->inputs->nom_clasificacion_id; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Actualizar</button>
    </div>
</form>

