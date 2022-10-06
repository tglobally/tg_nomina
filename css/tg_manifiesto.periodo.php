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

    .buttons {
        margin-bottom: 2.25rem;
    }

    .lista{
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .text-header {
        font-family: Helvetica;
        font-weight: 700!important;
        color: #000098;
    }

    .card {
        position: relative;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-orient: vertical;
        -webkit-box-direction: normal;
        -ms-flex-direction: column;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 1px solid rgba(0,0,0,.125);
        border-radius: 0.25rem;
    }

    .card-header:first-child {
        border-radius: calc(0.25rem - 1px) calc(0.25rem - 1px) 0 0;
    }

    .card-header {
        padding: 0.75rem 1.25rem;
        margin-bottom: 0;
        background-color: rgba(0,0,0,.03);
        border-bottom: 1px solid rgba(0,0,0,.125);
    }

    .card-body {
        -webkit-box-flex: 1;
        -ms-flex: 1 1 auto;
        flex: 1 1 auto;
        padding: 1.25rem;
    }

    .card-title {
        margin-bottom: 0.75rem;
    }
</style>

