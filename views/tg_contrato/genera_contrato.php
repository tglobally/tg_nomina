<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_manifiesto $controlador */ ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">
        <div class="  form-main" id="form">
            <form method="post" action="./index.php?seccion=tg_contrato&accion=genera_contrato_bd&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>" class="form-additional" enctype="multipart/form-data">
                <?php echo $controlador->inputs->em_empleado_id; ?>
                <?php echo $controlador->inputs->org_sucursal_id; ?>

                <div class="buttons col-md-12">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " >Guarda</button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
