<?php /** @var \tglobally\tg_nomina\controllers\controlador_com_cliente $controlador */ ?>

<form class="row g-3" method="post" action="./index.php?seccion=com_sucursal&accion=asigna_provision_bd&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>" class="form-additional">

    <?php echo $controlador->inputs->select->com_sucursal_id; ?>
    <?php echo $controlador->inputs->select->org_sucursal_id; ?>
    <div class="control-group col-sm-4">
        <div class="controls">
            <input type="checkbox" name="prima_vacacional" value="activo">
            <label class="form-check-label" >Prima Vacacional</label>
        </div>
    </div>
    <div class="control-group col-sm-4">
        <div class="controls">
            <input type="checkbox" name="vacaciones" value="activo">
            <label class="form-check-label" >Vacaciones</label>
        </div>
    </div>
    <div class="control-group col-sm-4">
        <div class="controls">
            <input type="checkbox" name="prima_antiguedad" value="activo">
            <label class="form-check-label" >Prima Antigüedad</label>
        </div>
    </div>
    <div class="control-group col-sm-4">
        <div class="controls">
            <input type="checkbox" name="aguinaldo" value="activo">
            <label class="form-check-label" >Gratificación Anual (Aguinaldo)</label>
        </div>
    </div>

    <div class="control-group col-sm-12">
        <div class="controls">
            <input type="checkbox" class="form-check-input" name="tipo_calculo" value="active">
            <label class="form-check-label" for="flexCheckDefault">(Activa|Desactiva) Tipo calculo</label>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Relacionar</button>
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