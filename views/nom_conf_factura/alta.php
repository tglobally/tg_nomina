<?php /** @var tglobally\tg_nomina\controllers\controlador_nom_conf_factura $controlador */ ?>
<?php include $controlador->include_menu_secciones; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>


        <div class="  form-main" id="form">
            <form method="post" action="./index.php?seccion=org_empresa&accion=alta_bd&session_id=<?php echo $controlador->session_id; ?>" class="form-additional">
                <?php echo $controlador->inputs->select->org_tipo_empresa_id; ?>
                <?php echo $controlador->inputs->codigo; ?>
                <?php echo $controlador->inputs->rfc; ?>
                <?php echo $controlador->inputs->razon_social; ?>
                <?php echo $controlador->inputs->nombre_comercial; ?>
                <div class="buttons col-md-12">
                    <?php echo $controlador->btns['sub_guarda']; ?>
                    <?php echo $controlador->btns['sub_siguiente_ubicacion']; ?>
                </div>
            </form>
        </div>
    </div>
</div>
