<?php
/** @var string $url_template */
use config\views;

$ruta_template_base = (new views())->ruta_template_base;
include $ruta_template_base.'assets/css/_base_css.php';
?>
<style>
.acciones_filter{
    display: flex;
    justify-content: center;
}

.info {

    display: flex;
    justify-content: center;
    align-items: center;
}

.titulo-form{
    color: #0B0595;
    margin-bottom: 20px;
}

h3{
    font-weight: bold;
}

.content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.colum_accion{
    display: flex;
    justify-content: center;
}

.filters {
    margin: 20px 25px;
    padding-bottom: 25px;
}


input {
    border: 0;
    outline: 0;
    width: 100%;
    padding: 5px 22px;
    background-color: #F1F2F6;
    border-radius: 15px;
}

.icon_inicio_lista{
    padding: 0 15px;
}

.icon_inicio_lista img{
    width: 35px;
}

.icon_back_lista{
    padding: 0 15px;
}

.icon_back_lista img{
    width: 35px;
}

.icon_recargar_lista{
    padding: 0 15px;
}

.icon_recargar_lista img{
    width: 35px;
}

.icon_xls_lista{
    padding: 0px 15px;
}

.icon_xls_lista img{
    width: 30px;
}


.icon_atras_lista{
    padding: 0 15px;
}

.icon_atras_lista img{
    width: 35px;
}

.icon_adelante_lista{
    padding: 0 15px;
}

.icon_adelante_lista img{
    width: 35px;
}

.icon_modifica_lista{
    padding: 0 15px;
}

.icon_modifica_lista img{
    width: 35px;
}

.icon_elimina_lista{
    padding: 0 15px;
}

.icon_elimina_lista img{
    width: 35px;
}

.icon_descargar_lista{
    padding: 0 15px;
}

.icon_descargar_lista img{
    width: 35px;
}

.input_icon{
    color: #191919;
    position: absolute;
    width: 20px;
    height: 20px;
    left: 30px;
    top: 50%;
    transform: translateY(-50%);
}

.input_search{
    position: relative;
}

.paginador{
    width: 30px;
    display: contents;
}


.table>thead>tr>th{
    border: 0 !important;
}

input:focus {outline:none!important;}

table thead {
    background-color: #F1F2F6;
}



</style>