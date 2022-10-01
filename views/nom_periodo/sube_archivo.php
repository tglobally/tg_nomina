<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_periodo $controlador */ ?>
<?php include 'templates/nom_periodo/sube_archivo/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="<?php echo $controlador->link_lee_archivo; ?>" class="form-additional" enctype="multipart/form-data">
                <div class="control-group col-sm-6">
                    <label class="control-label" for="archivo">Archivo Nomina</label>
                    <div class="controls">
                        <input type="file" id="archivo" name="archivo" multiple />
                    </div>
                </div>

                <div class="buttons col-md-12">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12" >Guarda</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
