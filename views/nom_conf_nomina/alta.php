<?php /** @var tglobally\tg_nomina\controllers\controlador_nom_conf_nomina $controlador */ ?>
<?php include $controlador->include_menu_secciones; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>


        <div class="  form-main" id="form">
            <form method="post" action="./index.php?seccion=nom_conf_nomina&accion=alta_bd&session_id=<?php echo $controlador->session_id; ?>" class="form-additional">
                <?php echo $controlador->inputs->codigo; ?>
                <?php echo $controlador->inputs->codigo_bis; ?>
                <?php echo $controlador->inputs->descripcion; ?>
                <?php echo $controlador->inputs->descripcion_select; ?>
                <?php echo $controlador->inputs->alias; ?>
                <?php echo $controlador->inputs->select->nom_conf_factura_id; ?>
                <div class="buttons col-md-12">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " >Guarda</button>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " >Siguiente</button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
