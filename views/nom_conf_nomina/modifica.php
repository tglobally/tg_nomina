<?php /** @var tglobally\tg_nomina\controllers\controlador_nom_conf_nomina $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_modifica_bd; ?>">

    <?php echo $controlador->inputs->cat_sat_tipo_nomina_id; ?>
    <?php echo $controlador->inputs->cat_sat_periodicidad_pago_nom_id; ?>
    <?php echo $controlador->inputs->nom_conf_factura_id; ?>
    <?php echo $controlador->inputs->descripcion; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Actualizar</button>
    </div>
</form>

