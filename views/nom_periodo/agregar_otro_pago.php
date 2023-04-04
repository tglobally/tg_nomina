<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_periodo $controlador */ ?>
<?php

use config\views;
$url_icons = (new views())->url_icons;
?>

<?php include 'templates/nom_periodo/nominas/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="<?php echo $controlador->link_nom_periodo_agregar_otro_pago_bd; ?>" class="form-additional">

                <input id="agregar_otro_pago" name="agregar_otro_pago" type="hidden" value="<?php echo  implode(",", $controlador->nominas_seleccionadas); ?>">
                <?php echo $controlador->inputs->nom_otro_pago_id; ?>
                <?php echo $controlador->inputs->importe_gravado; ?>
                <?php echo $controlador->inputs->importe_exento; ?>
                <?php echo $controlador->inputs->descripcion; ?>

                <div class="buttons col-md-12">
                    <div class="col-md-6 btn-ancho">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " > Alta</button>
                    </div>
                    <div class="col-md-6 btn-ancho">
                        <a href="<?php echo $controlador->link_nom_periodo_nominas; ?>"  class="btn btn-info btn-guarda col-md-12 ">Regresar</a>
                    </div>

                </div>
            </form>
        </div>


    </div>
</div>

