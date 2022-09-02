<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_periodo $controlador */?>
<?php use config\views; ?>

<div class="col-md-3 secciones">

    <div class="col-md-12 int_secciones ">
        <div class="col-md-4 seccion">
            <img src="<?php echo (new views())->url_assets.'img/stepper/1.svg'?>" class="img-seccion">
        </div>
        <div class="col-md-8">
            <h3>Periodos</h3>
            <?php include "templates/nom_periodo/_base/buttons/1.azul.php"; ?>
        </div>
    </div>
</div>
