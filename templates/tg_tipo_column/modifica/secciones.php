<?php use config\views; ?>

<div class="col-md-3 secciones">
    <div class="col-md-12 int_secciones ">
        <div class="col-md-4 seccion">
            <img src="<?php echo (new views())->url_assets.'img/stepper/1.svg'?>" class="img-seccion">
        </div>
        <div class="col-md-8">
            <h3>Modifica Tipo Columna</h3>
            <?php include "templates/tg_tipo_column/_base/buttons/1.azul.modifica.php"; ?>
        </div>
    </div>
</div>