<?php
/** @var string $url_template */
use config\views;

$ruta_template_base = (new views())->ruta_template_base;
include $ruta_template_base.'assets/css/_base_css.php';
?>
<style>
    body .pagination li.page-item a:hover, body .pagination li.page-item a.active, body .pagination-carousel li a:hover, body .pagination-carousel li.active a, .header .top-bar .pull-right, body .color-secondary, body .btn.color-secondary {
        background-color: white;
    }

    tr.group,
    tr.group:hover {
        background-color: #ddd !important;
    }

    .tablas_nominas{
        padding: 0 !important;
    }

    .tablas_nominas  .col-md-12{
        padding-left: 0px;
        padding-right: 0px;
    }

    .tabla_titulo{
        padding: 0.75rem 1.25rem;
        margin-bottom: 0;
        background-color: rgba(113, 107, 107, 0.12);
    }

    #nominas_percepciones{
        width: auto !important;
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



    .footable.table th, .footable-details.table th {
        font-family: Helvetica;
    }
    .footable.table td, .footable-details.table td {
        font-family: Helvetica;
    }
    .footable .footable-filtering .input-group .form-control:last-child, .footable .footable-filtering .input-group-addon:last-child, .footable .footable-filtering .input-group-btn:last-child > .btn, .footable .footable-filtering .input-group-btn:last-child > .btn-group > .btn, .footable .footable-filtering .input-group-btn:last-child > .dropdown-toggle, .footable .footable-filtering .input-group-btn:first-child > .btn:not(:first-child), .footable .footable-filtering .input-group-btn:first-child > .btn-group:not(:first-child) > .btn {
        background-color: #0B0595;
        border: 1px solid #0B0595
    }
</style>
