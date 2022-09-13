<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_periodo $controlador */ ?>
<?php

use config\views;
$url_icons = (new views())->url_icons;
?>

<?php include 'templates/nom_periodo/nominas/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="lista">
            <div class="card">
                <div class="card-header">
                    <span class="text-header">Nominas</span>
                </div>
                <div class="card-body">
                    <div class="cont_tabla_sucursal  col-md-12">
                        <table class="table">
                            <thead>
                            <tr>
                                <th data-breakpoints="xs sm md" data-type="html" >Id</th>
                                <th data-breakpoints="xs sm md" data-type="html" >Codigo</th>
                                <th data-breakpoints="xs sm md" data-type="html" >Codigo Bis</th>
                                <th data-breakpoints="xs sm md" data-type="html" >Descripcion</th>
                                <th data-breakpoints="xs sm md" data-type="html" >Descripcion Select</th>
                                <th data-breakpoints="xs sm md" data-type="html" >Alias</th>
                                <th data-breakpoints="xs sm md" data-type="html" data-filterable="false">Modifica</th>
                                <th data-breakpoints="xs sm md" data-type="html" data-filterable="false">Elimina</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($controlador->nominas->registros as $nomina){?>
                                <tr>
                                    <td><?php echo $nomina['nom_nomina_id']; ?></td>
                                    <td><?php echo $nomina['nom_nomina_codigo']; ?></td>
                                    <td><?php echo $nomina['nom_nomina_codigo_bis']; ?></td>
                                    <td><?php echo $nomina['nom_nomina_descripcion']; ?></td>
                                    <td><?php echo $nomina['nom_nomina_descripcion_select']; ?></td>
                                    <td><?php echo $nomina['nom_nomina_alias']; ?></td>
                                    <td><?php echo $nomina['link_modifica']; ?></td>
                                    <td><?php echo $nomina['link_elimina']; ?></td>
                                </tr>
                            <?php } ?>

                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-3">
                        <a href="index.php?seccion=nom_periodo&accion=lista&session_id=<?php echo $controlador->session_id; ?>"
                           class="btn btn-info btn-guarda col-md-12"><i class="icon-edit"></i>Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>

