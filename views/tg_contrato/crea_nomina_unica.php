<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_manifiesto $controlador */ ?>
<?php include 'templates/tg_manifiesto/crea_nomina_unica/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="./index.php?seccion=tg_manifiesto&accion=crea_nomina_unica&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>" class="form-additional" enctype="multipart/form-data">
                <?php echo $controlador->inputs->em_registro_patronal_id; ?>
                <?php echo $controlador->inputs->nom_periodo_id; ?>
                <?php echo $controlador->inputs->em_empleado_id; ?>
                <?php echo $controlador->inputs->org_puesto_id; ?>
                <?php echo $controlador->inputs->nom_conf_empleado_id; ?>
                <?php echo $controlador->inputs->em_cuenta_bancaria_id; ?>
                <?php echo $controlador->inputs->rfc; ?>
                <?php echo $controlador->inputs->curp; ?>
                <?php echo $controlador->inputs->nss; ?>
                <?php echo $controlador->inputs->folio; ?>
                <?php echo $controlador->inputs->fecha; ?>
                <?php echo $controlador->inputs->fecha_inicio_rel_laboral; ?>
                <?php echo $controlador->inputs->cat_sat_tipo_nomina_id; ?>
                <?php echo $controlador->inputs->cat_sat_periodicidad_pago_nom_id; ?>
                <?php echo $controlador->inputs->fecha_pago; ?>
                <?php echo $controlador->inputs->fecha_inicial_pago; ?>
                <?php echo $controlador->inputs->fecha_final_pago; ?>
                <?php echo $controlador->inputs->num_dias_pagados; ?>
                <?php echo $controlador->inputs->salario_diario; ?>
                <?php echo $controlador->inputs->salario_diario_integrado; ?>
                <?php echo $controlador->inputs->subtotal; ?>
                <?php echo $controlador->inputs->descuento; ?>
                <?php echo $controlador->inputs->total; ?>

                <div class="buttons col-md-12">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " >Guarda</button>
                    </div>
                    <div class="col-md-6 ">
                        <a href="<?php echo $controlador->link_lista; ?>"  class="btn btn-info btn-guarda col-md-12 ">Regresar</a>
                    </div>
                    
                </div>
            </form>
        </div>
    </div>
</div>
