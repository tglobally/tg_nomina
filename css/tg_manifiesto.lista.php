<?php
/** @var string $url_template */
use config\views;

$ruta_template_base = (new views())->ruta_template_base;
include $ruta_template_base.'assets/css/_base_css.php';
?>
<style>


    .card{
        border-radius: 0.5rem !important;
        box-shadow: 1px 1px 5px #191919;
    }

    .table thead{
        background-color: #000098;
        color: white;
        font-weight: bold;
    }

    .dataTables_filter input{
        border-bottom: 1px solid gray;
        box-shadow: 0 4px 3px -3px gray;
        border-radius: 0;
        height: 34px;
    }

    .dataTables_filter input:focus{
        border-bottom: 1px solid gray;
        box-shadow: 0 4px 3px -3px gray;
        border-radius: 0;
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