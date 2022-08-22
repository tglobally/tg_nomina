<?php /** @var controllers\controlador_nom_nomina$controlador */ ?>
<?php

use config\views;
$url_icons = (new views())->url_icons;
?>

<?php include 'templates/nom_nomina/alta/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>


        <div class="  form-main" id="form">
            <form method="post" action="./index.php?seccion=nom_nomina&accion=alta_bd&session_id=<?php echo $controlador->session_id; ?>" class="form-additional">
                <?php echo $controlador->inputs->codigo; ?>
                <?php echo $controlador->inputs->codigo_bis; ?>
                <?php echo $controlador->inputs->descripcion; ?>
                <?php echo $controlador->inputs->folio; ?>
                <?php echo $controlador->inputs->fecha; ?>
                <?php echo $controlador->inputs->num_dias_pagados; ?>
                <?php echo $controlador->inputs->fecha_pago; ?>
                <?php echo $controlador->inputs->fecha_inicial_pago; ?>
                <?php echo $controlador->inputs->fecha_final_pago; ?>
                <?php echo $controlador->inputs->select->em_empleado_id; ?>
                <?php echo $controlador->inputs->select->cat_sat_tipo_nomina_id; ?>
                <?php echo $controlador->inputs->select->fc_factura_id; ?>
                <?php echo $controlador->inputs->select->dp_calle_pertenece_id; ?>
                <?php echo $controlador->inputs->select->im_registro_patronal_id; ?>
                <div class="buttons col-md-12">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " name="btn_action_next" value="modifica">Guarda</button>
                    </div>
                    <div class="col-md-6 btn-ancho">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " name="btn_action_next" value="ubicacion">Siguiente</button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>