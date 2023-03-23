<?php
/** @var string $url_template */
use config\views;

$ruta_template_base = (new views())->ruta_template_base;
include $ruta_template_base.'assets/css/_base_css.php';


?>

<style>
    body .pagination li.page-item a:hover, body .pagination li.page-item a.active, body .pagination-carousel li a:hover, body .pagination-carousel li.active a, .header .top-bar .pull-right, body .color-secondary, body .btn.color-secondary {
        background-color: #ffffff;
    }

</style>




