<?php /** @var \tglobally\tg_nomina\controllers\controlador_org_sucursal $controlador */ ?>
<?php

use config\views;
$url_icons = (new views())->url_icons;
?>

<?php include 'templates/org_sucursal/modifica/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="./index.php?seccion=org_sucursal&accion=modifica_bd&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>" class="form-additional">
                <?php echo $controlador->inputs->id; ?>
                <?php echo $controlador->inputs->codigo; ?>
                <?php echo $controlador->inputs->codigo_bis; ?>
                <?php echo $controlador->inputs->descripcion; ?>
                <?php echo $controlador->inputs->descripcion_select; ?>
                <?php echo $controlador->inputs->alias; ?>

                <div class="buttons col-md-12 ">
                    <div class="row mt-3">
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-info btn-success col-md-12 " value="modifica">Modifica</button>
                        </div>
                        <div class="col-sm-6">
                            <a href="index.php?seccion=org_sucursal&accion=lista&session_id=<?php echo $controlador->session_id; ?>"
                               class="btn btn-info btn-guarda col-md-12 "><i class="icon-edit"></i>Lista
                            </a>
                        </div>
                    </div>

            </form>
        </div>


    </div>
</div>

