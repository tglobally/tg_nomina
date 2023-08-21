<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_manifiesto $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_alta_bd; ?>" enctype="multipart/form-data">
    <?php echo $controlador->inputs->com_sucursal_id; ?>
    <?php echo $controlador->inputs->org_sucursal_id; ?>
    <?php echo $controlador->inputs->tg_tipo_servicio_id; ?>
    <?php echo $controlador->inputs->fecha_pago; ?>
    <?php echo $controlador->inputs->fecha_inicial_pago; ?>
    <?php echo $controlador->inputs->fecha_final_pago; ?>


    <div class="control-group col-12">
        <label class="control-label" for="archivo">Archivo Nomina</label>
        <div class="controls">
            <input type="file" class="form-control" id="archivo" name="archivo" multiple/>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>
