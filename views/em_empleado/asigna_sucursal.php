<?php /** @var \tglobally\tg_empleado\controllers\controlador_em_empleado $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_tg_empleado_sucursal_alta_bd; ?>">

    <?php echo $controlador->inputs->em_empleado_id; ?>
    <?php echo $controlador->inputs->com_sucursal_id; ?>

    <?php echo $controlador->inputs->hidden_row_id; ?>
    <?php echo $controlador->inputs->hidden_seccion_retorno; ?>
    <?php echo $controlador->inputs->hidden_id_retorno; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>

<div class="lista">
    <div class="card">
        <div class="card-header">
            <span class="text-header">Sucursales Asignadas</span>
        </div>
        <div class="card-body">
            <?php echo $controlador->contenido_table; ?>
        </div> <!-- /. widget-table-->
    </div>
</div>

