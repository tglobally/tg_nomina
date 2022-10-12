<?php use config\views; ?>
<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_row_manifiesto $controlador */ ?>

<div class="col-md-3 secciones">
    <div class="col-md-12 int_secciones ">
        <div class="col-md-4 seccion">
            <img src="<?php echo (new views())->url_assets.'img/stepper/1.svg'?>" class="img-seccion">
        </div>
        <div class="col-md-8">
            <h3>Alta Row Manifiesto</h3>
            <?php include "templates/$controlador->seccion/_base/buttons/1.azul.alta.php"; ?>
        </div>
    </div>
</div>