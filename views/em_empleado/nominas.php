<?php /** @var \tglobally\tg_empleado\controllers\controlador_em_empleado $controlador */ ?>

<?php (new \tglobally\template_tg\template())->sidebar(controlador: $controlador,seccion_step: 7); ?>

<div class="col-md-9 formulario">
    <div class="lista">
        <div class="card">
            <div class="card-header">
                <span class="text-header">Nominas Generadas</span>
            </div>
            <div class="card-body">
                <?php echo $controlador->contenido_table; ?>
            </div> <!-- /. widget-table-->
        </div>
    </div>

</div>

