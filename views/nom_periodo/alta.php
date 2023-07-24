<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_periodo $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_alta_bd; ?>">

    <?php echo $controlador->inputs->codigo; ?>
    <?php echo $controlador->inputs->select->nom_conf_nomina_id; ?>
    <?php echo $controlador->inputs->descripcion; ?>
    <?php echo $controlador->inputs->select->cat_sat_periodicidad_pago_nom_id; ?>
    <?php echo $controlador->inputs->select->em_registro_patronal_id; ?>
    <?php echo $controlador->inputs->select->nom_tipo_periodo_id; ?>
    <?php echo $controlador->inputs->select->cat_sat_tipo_nomina_id; ?>
    <?php echo $controlador->inputs->fecha_inicial_pago; ?>
    <?php echo $controlador->inputs->fecha_final_pago; ?>
    <?php echo $controlador->inputs->fecha_pago; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>
