<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_nomina $controlador */ ?>

<form class="row g-3" method="post" action="./index.php?seccion=nom_nomina&accion=alta_bd&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>">

    <?php echo $controlador->inputs->select->em_registro_patronal_id; ?>
    <?php echo $controlador->inputs->select->nom_periodo_id; ?>
    <?php echo $controlador->inputs->select->em_empleado_id; ?>
    <?php echo $controlador->inputs->select->org_puesto_id; ?>
    <?php echo $controlador->inputs->select->nom_conf_empleado_id; ?>
    <?php echo $controlador->inputs->select->em_cuenta_bancaria_id; ?>
    <?php echo $controlador->inputs->rfc; ?>
    <?php echo $controlador->inputs->curp; ?>
    <?php echo $controlador->inputs->nss; ?>
    <?php echo $controlador->inputs->folio; ?>
    <?php echo $controlador->inputs->fecha; ?>
    <?php echo $controlador->inputs->fecha_inicio_rel_laboral; ?>
    <?php echo $controlador->inputs->select->cat_sat_tipo_nomina_id; ?>
    <?php echo $controlador->inputs->select->cat_sat_periodicidad_pago_nom_id; ?>
    <?php echo $controlador->inputs->fecha_pago; ?>
    <?php echo $controlador->inputs->fecha_inicial_pago; ?>
    <?php echo $controlador->inputs->fecha_final_pago; ?>
    <?php echo $controlador->inputs->num_dias_pagados; ?>
    <?php echo $controlador->inputs->salario_diario; ?>
    <?php echo $controlador->inputs->salario_diario_integrado; ?>
    <?php echo $controlador->inputs->subtotal; ?>
    <?php echo $controlador->inputs->descuento; ?>
    <?php echo $controlador->inputs->total; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>

