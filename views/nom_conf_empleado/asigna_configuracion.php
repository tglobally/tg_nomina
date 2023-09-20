<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_conf_empleado $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_asigna_configuracion_bd; ?>">

    <?php echo $controlador->inputs->com_sucursal_id; ?>
    <?php echo $controlador->inputs->nom_conf_nomina_id; ?>
    <?php echo $controlador->inputs->descripcion; ?>
    <div class="control-group col-sm-12">
        <label class="form-label" for="empleados">Empleados</label>
        <div class="controls">
            <select id="lista" class="form-multi-select" name="empleados[]"
                    multiple="multiple" data-coreui-selection-type="tags" data-coreui-search="true" >
            </select>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>


