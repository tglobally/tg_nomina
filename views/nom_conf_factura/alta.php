<?php /** @var tglobally\tg_nomina\controllers\controlador_nom_conf_factura $controlador */ ?>
<?php include $controlador->include_menu_secciones; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>


        <div class="  form-main" id="form">
            <form method="post" action="./index.php?seccion=nom_conf_factura&accion=alta_bd&session_id=<?php echo $controlador->session_id; ?>" class="form-additional">
                <?php echo $controlador->inputs->cat_sat_forma_pago_id; ?>
                <?php echo $controlador->inputs->cat_sat_metodo_pago_id; ?>
                <?php echo $controlador->inputs->com_tipo_cambio_id; ?>
                <?php echo $controlador->inputs->cat_sat_tipo_de_comprobante_id; ?>
                <?php echo $controlador->inputs->cat_sat_moneda_id; ?>
                <?php echo $controlador->inputs->cat_sat_uso_cfdi_id; ?>
                <?php echo $controlador->inputs->com_producto_id; ?>
                <?php echo $controlador->inputs->descripcion; ?>
                <div class="buttons col-md-12">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " >Guarda</button>
                    </div>
                    <div class="col-md-6">
                        <a href="index.php?seccion=tg_manifiesto&accion=lista&session_id=<?php echo $controlador->session_id; ?>"  class="btn btn-info btn-guarda col-md-12 ">Regresar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
