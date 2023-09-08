<?php /** @var \tglobally\tg_empleado\controllers\controlador_em_empleado $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_tg_empleado_asigna_provision_bd; ?>">

    <?php echo $controlador->inputs->em_empleado_id; ?>
    <?php echo $controlador->inputs->com_sucursal_id; ?>
    <?php echo $controlador->inputs->org_sucursal_id; ?>


    <div class="control-group">
        <div class="control-group col-sm-4">
            <div class="controls">
                <input type="checkbox" class="form-check-input" name="prima_vacacional" value="PRIMA VACACIONAL">
                <label class="form-check-label" for="flexCheckDefault">Prima Vacacional</label>
            </div>
        </div>
        <div class="control-group col-sm-4">
            <div class="controls">
                <input type="checkbox" class="form-check-input" name="vacaciones" value="VACACIONES">
                <label class="form-check-label" for="flexCheckDefault">Vacaciones</label>
            </div>
        </div>
        <div class="control-group col-sm-4">
            <div class="controls">
                <input type="checkbox" class="form-check-input" name="prima_antiguedad" value="PRIMA DE ANTIGÜEDAD">
                <label class="form-check-label" for="flexCheckDefault">Prima Antigüedad</label>
            </div>
        </div>
        <div class="control-group col-sm-4">
            <div class="controls">
                <input type="checkbox" class="form-check-input" name="aguinaldo" value="GRATIFICACIÓN ANUAL (AGUINALDO)">
                <label class="form-check-label" for="flexCheckDefault">Gratificación Anual (Aguinaldo)</label>
            </div>
        </div>

        <div class="control-group col-sm-12">
            <div class="controls">
                <input type="checkbox" class="form-check-input" name="tipo_calculo" value="active">
                <label class="form-check-label" for="flexCheckDefault">(Activa|Desactiva) Tipo calculo</label>
            </div>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>

<div class="lista">
    <div class="card">
        <div class="card-header">
            <span class="text-header">Provisiones Asignadas</span>
        </div>
        <div class="card-body">
            <?php echo $controlador->contenido_table; ?>
        </div> <!-- /. widget-table-->
    </div>
</div>

