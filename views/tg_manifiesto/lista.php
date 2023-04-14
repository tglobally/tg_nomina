<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_manifiesto $controlador */ ?>
<?php

use config\views;
$url_icons = (new views())->url_icons;
?>

<?php include 'templates/tg_manifiesto/lista/secciones.php'; ?>
<?php /*echo $controlador->template_lista; */?>


<div class="col-md-9 info-lista">
    <div class="col-lg-12 content">
        <h3 style="
font-size: 20px;
font-style: normal;
font-weight: normal;
line-height: 24px;
color: #304463;" >Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user'];  ?></h3>
        <div class="lista">
            <div class="card">

                <div class="card-body">
                    <div class="cont_tabla_sucursal  col-md-12">
                        <table class="table table-striped datatable">
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


