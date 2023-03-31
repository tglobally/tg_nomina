<?php
/** @var string $url_template */
use config\views;

$ruta_template_base = (new views())->ruta_template_base;
include $ruta_template_base.'assets/css/_base_css.php';

$url_template = (new views())->url_assets;
$url_template .= 'css/';
?>
<style>
    @import "<?php echo $url_template; ?>winter-flat.css";
    .form-control{
        border-radius: 10px !important;
    }
    .color-secondary{
        background: #f8f8f8 !important;
    }
</style>






