<?php /** @var \gamboamartin\cat_sat\controllers\controlador_cat_sat_clase_producto $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_em_cuenta_bancaria_alta_bd; ?>">
    <?php echo $controlador->inputs->em_empleado_id; ?>
    <?php echo $controlador->inputs->bn_sucursal_id; ?>
    <?php echo $controlador->inputs->num_cuenta; ?>
    <?php echo $controlador->inputs->clabe; ?>
    <?php echo $controlador->inputs->descripcion; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>

<div class="lista">
    <div class="card">
        <div class="card-header">
            <span class="text-header">Cuentas Bancarias</span>
        </div>
        <div class="card-body">
            <?php echo $controlador->contenido_table; ?>
        </div> <!-- /. widget-table-->
    </div>
</div>