<?php /** @var \tglobally\tg_empleado\controllers\controlador_em_empleado $controlador */ ?>
<?php include 'templates/em_empleado/anticipo/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="<?php echo $controlador->link_em_abono_anticipo_alta_bd; ?>&em_anticipo_id=<?php echo $controlador->em_anticipo_id; ?>" class="form-additional">

                <?php echo $controlador->inputs->em_anticipo_id; ?>
                <?php echo $controlador->inputs->em_tipo_abono_anticipo_id; ?>
                <?php echo $controlador->inputs->descripcion; ?>
                <?php echo $controlador->inputs->cat_sat_forma_pago_id; ?>
                <?php echo $controlador->inputs->fecha; ?>
                <?php echo $controlador->inputs->monto; ?>

                <div class="buttons col-md-12">
                    <div class="col-md-6 btn-ancho">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " name="btn_action_next" value="anticipo" >Alta Abono</button>
                    </div>
                    <div class="col-md-6 btn-ancho">
                        <a href="index.php?seccion=em_empleado&accion=anticipo&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>"  class="btn btn-info btn-guarda col-md-12 ">Regresar</a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="lista">
        <div class="card">
            <div class="card-header">
                <span class="text-header">Abonos</span>
            </div>
            <div class="card-body">
                <div class="cont_tabla_sucursal  col-md-12">
                    <table class="table ">
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th>Codigo</th>
                            <th>Descripcion</th>
                            <th>Monto</th>
                            <th>Forma Pago</th>
                            <th>Fecha</th>
                            <th>Modifica</th>
                            <th>Elimina</th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($controlador->abonos->registros as $abono){?>
                            <tr>
                                <td><?php echo $abono['em_abono_anticipo_id']; ?></td>
                                <td><?php echo $abono['em_abono_anticipo_codigo']; ?></td>
                                <td><?php echo $abono['em_abono_anticipo_descripcion']; ?></td>
                                <td><?php echo $abono['em_abono_anticipo_monto']; ?></td>
                                <td><?php echo $abono['cat_sat_forma_pago_descripcion']; ?></td>
                                <td><?php echo $abono['em_abono_anticipo_fecha']; ?></td>
                                <td><?php echo $abono['link_modifica']; ?></td>
                                <td><?php echo $abono['link_elimina']; ?></td>
                            </tr>
                        <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

