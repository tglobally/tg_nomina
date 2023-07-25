<?php /** @var tglobally\tg_nomina\controllers\controlador_nom_conf_factura $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_alta_bd; ?>">

    <?php echo $controlador->inputs->cat_sat_forma_pago_id; ?>
    <?php echo $controlador->inputs->cat_sat_metodo_pago_id; ?>
    <?php echo $controlador->inputs->com_tipo_cambio_id; ?>
    <?php echo $controlador->inputs->cat_sat_tipo_de_comprobante_id; ?>
    <?php echo $controlador->inputs->cat_sat_moneda_id; ?>
    <?php echo $controlador->inputs->cat_sat_uso_cfdi_id; ?>
    <?php echo $controlador->inputs->com_producto_id; ?>
    <?php echo $controlador->inputs->descripcion; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>
