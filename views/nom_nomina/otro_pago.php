<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_nomina $controlador  controlador en ejecucion */ ?>
<?php

use config\views;
$url_icons = (new views())->url_icons;
?>

<?php include 'templates/nom_nomina/otro_pago/secciones.php'; ?>

<div class="col-md-9 formulario">
    <div class="col-lg-12">
        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>
        <div class="  form-main" id="form">
            <form method="post" action="<?php echo $controlador->link; ?>" class="form-additional">

                <div class="buttons col-md-12">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " >Guarda</button>
                    </div>
                    <div class="col-md-6">
                        <a href="<?php echo $controlador->link_modifica ?>" class="btn btn-info btn-guarda col-md-12 ">Regresar</a>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>


