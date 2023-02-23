<?php
/** @var tglobally\tg_nomina\controllers\controlador_nom_conf_nomina $controlador */

use gamboamartin\errores\errores;
use tglobally\template_tg\menu_lateral;
?>

<div class="col-md-3 secciones">

    <div class="col-md-12 int_secciones ">
        <?php echo $controlador->menu_lateral; ?>
        <?php
        $data_template = (new menu_lateral())->contenido_menu_lateral(aplica_link:false,controlador:$controlador, titulo: 'Configuraciones');
        if(gamboamartin\errores\errores::$error){
            return (new errores())->error(mensaje: 'Error al integrar include', data: $data_template);
        }
        ?>

    </div>
</div>
