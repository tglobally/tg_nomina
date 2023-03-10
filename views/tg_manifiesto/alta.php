<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_tipo_servicio $controlador */ ?>
<?php include 'templates/tg_manifiesto/alta/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="./index.php?seccion=tg_manifiesto&accion=alta_bd&session_id=<?php echo $controlador->session_id; ?>" class="form-additional">

                <?php echo $controlador->inputs->descripcion; ?>
                <?php echo $controlador->inputs->com_sucursal_id; ?>
                <?php echo $controlador->inputs->tg_cte_alianza_id; ?>
                <?php echo $controlador->inputs->org_sucursal_id; ?>
                <?php echo $controlador->inputs->tg_tipo_servicio_id; ?>
                <?php echo $controlador->inputs->fecha_inicial_pago; ?>
                <?php echo $controlador->inputs->fecha_final_pago; ?>
                <?php echo $controlador->inputs->fecha_envio; ?>
                <?php echo $controlador->inputs->fecha_pago; ?>
                <div class="buttons col-md-12">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " name="btn_action_next" value="modifica">Guarda</button>
                    </div>
                    <div class="col-md-6 ">
                        <a href="index.php?seccion=tg_manifiesto&accion=lista&session_id=<?php echo $controlador->session_id; ?>"  class="btn btn-info btn-guarda col-md-12 ">Regresar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
