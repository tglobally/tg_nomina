<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_nomina $controlador */ ?>
<?php

use config\views;
$url_icons = (new views())->url_icons;
?>

<?php include 'templates/nom_nomina/lista/secciones.php'; ?>

<div class="col-md-9 info-lista">
    <div class="col-lg-12 content">
        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?></h3>

        <div class="col-md-12 mt-3 table-responsive-sm">

            <div class="filters">
                <div class="filter col-md-4 acciones_filter">
                    <a class="icon_xls_lista">
                        <img src="<?php echo $url_icons; ?>icon_xls.png">
                    </a>
                    <a class="icon_atras_lista">
                        <img src="<?php echo $url_icons; ?>icon_pag_atras.svg">
                    </a>
                    <p class="paginador">3 de 35</p>
                    <a class="icon_adelante_lista">
                        <img src="<?php echo $url_icons; ?>icon_pag_adelante.svg">
                    </a>
                </div>
                <div class="search col-md-8 input_search">
                    <input type="text form-control input">
                    <img class="input_icon" src="<?php echo $url_icons; ?>icon_lupa.svg">
                </div>
            </div>

            <table class="table table-dark">
                <thead>
                <tr>
                    <th data-breakpoints="xs sm md" data-type="html" >Id</th>
                    <th data-breakpoints="xs sm md" data-type="html" >Codigo</th>
                    <th data-breakpoints="xs sm md" data-type="html" >Descripcion</th>
                    <th data-breakpoints="xs sm md" data-type="html" >Fecha inicial pago</th>
                    <th data-breakpoints="xs sm md" data-type="html" >Fecha final pago</th>
                    <th data-breakpoints="xs sm md" data-type="html" >Codigo empleado</th>
                    <th data-breakpoints="xs sm md" data-type="html" >Empleado</th>
                    <th data-breakpoints="xs sm md" data-type="html" >Empresa</th>
                    <th data-breakpoints="xs md" class="control"  data-type="html" data-filterable="false">Genera XML</th>
                    <th data-breakpoints="xs md" class="control"  data-type="html" data-filterable="false">Modifica</th>
                    <th data-breakpoints="xs md" class="control"  data-type="html" data-filterable="false">Elimina</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($controlador->registros as $registro){?>
                    <tr>
                        <td><?php echo $registro->nom_nomina_id; ?></td>
                        <td><?php echo $registro->nom_nomina_codigo; ?></td>
                        <td><?php echo $registro->nom_nomina_descripcion; ?></td>
                        <td><?php echo $registro->nom_nomina_fecha_inicial_pago; ?></td>
                        <td><?php echo $registro->nom_nomina_fecha_final_pago; ?></td>
                        <td><?php echo $registro->em_empleado_codigo; ?></td>
                        <td><?php echo $registro->em_empleado_nombre.' '.$registro->em_empleado_ap.' '.$registro->em_empleado_am; ?></td>
                        <td><?php echo $registro->org_empresa_id; ?></td>
                        <td><a class="btn btn-warning " href="<?php echo $registro->link_genera_xml; ?>">Genera XML</a></td>
                        <td><a class="btn btn-warning " href="<?php echo $registro->link_modifica; ?>">Modifica</a></td>
                        <td><a class="btn btn-danger " href="<?php echo $registro->link_elimina_bd; ?>">Elimina</a></td>
                    </tr>
                <?php } ?>

                </tbody>
            </table>
        </div>

    </div>
</div>
