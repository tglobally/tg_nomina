<?php /** @var tglobally\tg_nomina\controllers\controlador_nom_conf_nomina $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_alta_bd; ?>">

    <?php echo $controlador->inputs->cat_sat_tipo_nomina_id; ?>
    <?php echo $controlador->inputs->cat_sat_periodicidad_pago_nom_id; ?>
    <?php echo $controlador->inputs->nom_conf_factura_id; ?>
    <?php echo $controlador->inputs->descripcion; ?>

    <div class="control-group col-sm-12">
        <label class="form-label" for="aplicaciones">Aplicaciones</label>
        <div class="controls">
            <select class="form-multi-select" name="aplicaciones[]"
                    multiple="multiple" data-coreui-selection-type="tags" data-coreui-search="true" >
                <option value="aplica_septimo_dia">Séptimo Día</option>
                <option value="aplica_despensa">Despensa</option>
                <option value="aplica_prima_dominical">Prima Dominical</option>
                <option value="aplica_dia_festivo_laborado">Día Festivo Laborado</option>
                <option value="aplica_dia_descanso">Día Descanso</option>
                <option value="aplica_desgaste">Desgaste</option>
                <option value="aplica_nomina_pura">Nomina Pura</option>
            </select>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>

