<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_manifiesto $controlador */ ?>
<?php include 'templates/tg_manifiesto/periodo/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="./index.php?seccion=tg_manifiesto&accion=periodo_alta&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>" class="form-additional">
                <?php echo $controlador->inputs->descripcion; ?>
                <?php echo $controlador->inputs->tg_manifiesto_id; ?>
                <?php echo $controlador->inputs->nom_periodo_id; ?>

                <div class="buttons col-md-12">
                    <div class="col-md-6 btn-ancho">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " > Alta</button>
                    </div>
                    <div class="col-md-6 btn-ancho">
                        <a href="index.php?seccion=tg_manifiesto&accion=lista&session_id=<?php echo $controlador->session_id; ?>"  class="btn btn-info btn-guarda col-md-12 ">Lista</a>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>


