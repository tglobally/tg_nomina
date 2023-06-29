<?php /** @var \gamboamartin\empleado\controllers\controlador_em_empleado $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>


<?php include 'templates/em_empleado/modifica/secciones.php'; ?>
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
                                <th data-breakpoints="xs sm md" data-type="html">Id</th>
                                <th data-breakpoints="xs sm md" data-type="html">Codigo</th>
                                <th data-breakpoints="xs sm md" data-type="html">Descripcion</th>
                                <th data-breakpoints="xs sm md" data-type="html">Descripcion Select</th>
                                <th data-breakpoints="xs sm md" data-type="html">Alias</th>

                                <th data-breakpoints="xs md" class="control"  data-type="html">Modifica</th>
                                <th data-breakpoints="xs md" class="control"  data-type="html">Elimina</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($controlador->anticipos->registros as $anticipo){?>
                                <tr>
                                    <td><?php echo $anticipo['em_anticipo_id']; ?></td>
                                    <td><?php echo $anticipo['em_anticipo_codigo']; ?></td>
                                    <td><?php echo $anticipo['em_anticipo_descripcion']; ?></td>
                                    <td><?php echo $anticipo['em_anticipo_descripcion_select']; ?></td>
                                    <td><?php echo $anticipo['em_anticipo_alias']; ?></td>
                                    <td><?php echo $anticipo['link_modifica']; ?></td>
                                    <td><?php echo $anticipo['link_elimina']; ?></td>
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



</main>





