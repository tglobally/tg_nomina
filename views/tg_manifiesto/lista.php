<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_manifiesto $controlador */ ?>
<?php

use config\views;
$url_icons = (new views())->url_icons;
?>

<?php include 'templates/tg_manifiesto/lista/secciones.php'; ?>

<div class="col-md-9 info-lista">
    <div class="col-lg-12 content">
        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?></h3>

        <div class="lista">
            <div class="card">

                <div class="card-body">
                    <div class="cont_tabla_sucursal  col-md-12">
                        <table class="table ">
                            <thead>
                            <tr>
                                <th data-breakpoints="xs sm md" data-type="html" >Id</th>
                                <th data-breakpoints="xs sm md" data-type="html" >Codigo</th>
                                <th data-breakpoints="xs sm md" data-type="html" >Codigo bis</th>
                                <th data-breakpoints="xs sm md" data-type="html" >Descripcion</th>
                                <th data-breakpoints="xs md" class="control"  data-type="html" data-filterable="false">Sube Manifiesto</th>
                                <th data-breakpoints="xs md" class="control"  data-type="html" data-filterable="false">Ve Nominas</th>
                                <th data-breakpoints="xs md" class="control"  data-type="html" data-filterable="false">Modifica</th>
                                <th data-breakpoints="xs md" class="control"  data-type="html" data-filterable="false">Elimina</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($controlador->registros as $registro){?>
                                <tr>
                                    <td><?php echo $registro->tg_manifiesto_id; ?></td>
                                    <td><?php echo $registro->tg_manifiesto_codigo; ?></td>
                                    <td><?php echo $registro->tg_manifiesto_codigo_bis?></td>
                                    <td><?php echo $registro->tg_manifiesto_descripcion; ?></td>
                                    <td><a class="btn btn-info " href="<?php echo $registro->link_sube_manifiesto; ?>">Sube Manifiesto</a></td>
                                    <td><a class="btn btn-info " href="<?php echo $registro->link_ve_nominas; ?>">Ve Nominas</a></td>
                                    <td><a class="btn btn-warning " href="<?php echo $registro->link_modifica; ?>">Modifica</a></td>
                                    <td><a class="btn btn-danger " href="<?php echo $registro->link_elimina_bd; ?>">Elimina</a></td>
                                </tr>
                            <?php } ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
