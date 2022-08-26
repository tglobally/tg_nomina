<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_nomina $controlador */?>
<?php use config\views; ?>

<div class="col-md-3 secciones">

    <div class="col-md-12 int_secciones ">
        <div class="col-md-4 seccion">
            <img src="<?php echo (new views())->url_assets.'img/stepper/1.svg'?>" class="img-seccion">
        </div>
        <div class="col-md-8">
            <h3>Nominas</h3>
            <?php include "templates/nom_nomina/_base/buttons/_1.azul.otro_pago.php"; ?>
        </div>
    </div>
</div>
