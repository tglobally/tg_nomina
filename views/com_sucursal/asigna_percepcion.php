<?php /** @var \tglobally\tg_nomina\controllers\controlador_com_cliente $controlador */ ?>

<form class="row g-3" method="post" action="./index.php?seccion=com_sucursal&accion=asigna_percepcion_bd&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>" class="form-additional">

    <?php echo $controlador->inputs->select->com_sucursal_id; ?>
    <?php echo $controlador->inputs->select->nom_percepcion_id; ?>
    <?php echo $controlador->inputs->monto; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>


<div class="lista">
    <div class="card">
        <div class="card-header">
            <span class="text-header">Percepciones Asignadas</span>
        </div>
        <div class="card-body">
            <?php echo $controlador->contenido_table; ?>
        </div> <!-- /. widget-table-->
    </div>
</div>