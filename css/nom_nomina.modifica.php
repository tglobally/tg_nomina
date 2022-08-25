<?php
/** @var string $url_template */
use config\views;

$ruta_template_base = (new views())->ruta_template_base;
include $ruta_template_base.'assets/css/_base_css.php';
include 'nom_nomina.lista.php';

?>
<style>
.cont_tabla_sucursal{
    margin-top: 20px;
}

.table-dark thead tr{
    background-color: #F1F2F6;
    font-family: Helvetica;
}

.table-dark thead tr th{
    font-family: Helvetica;
}

.table-dark thead tr td{
    font-family: Helvetica;
}

</style>