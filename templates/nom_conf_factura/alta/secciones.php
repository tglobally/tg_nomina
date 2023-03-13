<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_conf_factura $controlador */ ?>
<?php use config\views; ?>

<div class="col-md-3 secciones">

    <div class="col-md-12 int_secciones ">
        <div class="col-md-4 seccion">
            <img src="<?php echo (new views())->url_assets.'img/stepper/1.svg'?>" class="img-seccion">
        </div>
        <div class="col-md-8">
            <h3>Alta periodo</h3>
            <?php include "templates/nom_conf_factura/_base/buttons/1.azul.php"; ?>
        </div>
    </div>
</div>

