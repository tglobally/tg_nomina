<?php /** @var \tglobally\tg_nomina\controllers\controlador_adm_session $controlador */

use config\views;
$url_assets = (new views())->url_assets;
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="cont_text_inicio">
                <h1 class="h-side-title page-title page-title-big text-color-primary">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?></h1>
                <h1 class="h-side-title page-title text-color-primary">Da click en la sección que deseas utilizar</h1>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="catalogos">
        <?php foreach ($controlador->links_catalogos as $indice => $valor): ?>
            <a href="<?php echo $valor["link"];?>">
                <div class="cont_imagen_accion">
                    <img src="<?php echo $url_assets; ?>img/inicio/imagen_2.jpg">
                </div>
                <div class="cont_text_accion">
                    <h4 class="text_seccion"><?php echo $valor["titulo"];?></h4>
                    <h4 class="text_accion"><?php echo $valor["subtitulo"];?></h4>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>