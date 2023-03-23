<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_tipo_servicio $controlador */ ?>
<?php include 'templates/tg_manifiesto/sube_manifiesto/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="./index.php?seccion=tg_manifiesto&accion=lee_archivo&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>" class="form-additional" enctype="multipart/form-data">

                <div class="control-group col-sm-12">
                    <label class="control-label" for="archivo">Archivo Nomina</label>
                    <div class="controls">
                        <input type="file" id="archivo" name="archivo" multiple />
                    </div>
                </div>

                <div class="buttons col-md-12">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " name="btn_action_next" value="modifica">Guarda</button>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " name="btn_action_next" value="Descarga Recibos">Descarga Recibos</button>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " name="btn_action_next" value="Descarga Recibos Conjunto">Descarga Recibos Conjunto</button>
                    </div>
                    <div class="col-md-6 ">
                        <a href="index.php?seccion=tg_manifiesto&accion=lista&session_id=<?php echo $controlador->session_id; ?>"  class="btn btn-info btn-guarda col-md-12 ">Regresar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
