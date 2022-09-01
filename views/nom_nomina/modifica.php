<?php /** @var controllers\controlador_nom_nomina$controlador */ ?>
<?php

use config\views;
$url_icons = (new views())->url_icons;
?>

<?php include 'templates/nom_nomina/modifica/secciones.php'; ?>
<div class="col-md-9 formulario">
    <div class="col-lg-12">

        <h3 class="text-center titulo-form">Hola, <?php echo $controlador->datos_session_usuario['adm_usuario_user']; ?> </h3>

        <div class="  form-main" id="form">
            <form method="post" action="<?php echo $controlador->link_nom_nomina_modifica_bd; ?>" class="form-additional">
                <?php echo $controlador->inputs->id; ?>
                <?php echo $controlador->inputs->select->im_registro_patronal_id; ?>
                <?php echo $controlador->inputs->select->em_empleado_id; ?>
                <?php echo $controlador->inputs->select->em_cuenta_bancaria_id; ?>
                <?php echo $controlador->inputs->rfc; ?>
                <?php echo $controlador->inputs->curp; ?>
                <?php echo $controlador->inputs->nss; ?>
                <?php echo $controlador->inputs->folio; ?>
                <?php echo $controlador->inputs->fecha; ?>
                <?php echo $controlador->inputs->fecha_inicio_rel_laboral; ?>
                <?php echo $controlador->inputs->select->cat_sat_tipo_nomina_id; ?>
                <?php echo $controlador->inputs->select->cat_sat_periodicidad_pago_nom_id; ?>
                <?php echo $controlador->inputs->fecha_pago; ?>
                <?php echo $controlador->inputs->fecha_inicial_pago; ?>
                <?php echo $controlador->inputs->fecha_final_pago; ?>
                <?php echo $controlador->inputs->num_dias_pagados; ?>
                <?php echo $controlador->inputs->salario_diario; ?>
                <?php echo $controlador->inputs->salario_diario_integrado; ?>
                <?php echo $controlador->inputs->subtotal; ?>
                <?php echo $controlador->inputs->descuento; ?>
                <?php echo $controlador->inputs->total; ?>
                <div class="buttons col-md-12">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-info btn-success col-md-12 " value="modifica">Guarda</button>
                    </div>
                    <div class="col-md-6 btn-ancho">
                        <button type="submit" class="btn btn-info btn-guarda col-md-12 " name="btn_action_next" value="ubicacion">Siguiente</button>
                    </div>


                    <div class="buttons col-md-12 ">

                        <div class="col-md-6">
                            <a href="index.php?seccion=nom_nomina&accion=nueva_percepcion&registro_id=<?php echo $controlador->nom_nomina_id; ?>&session_id=<?php echo $controlador->session_id; ?>"
                               class="btn btn-info btn-guarda col-md-12"><i class="icon-edit"></i>
                                Nueva Percepcion
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="index.php?seccion=nom_nomina&accion=nueva_deduccion&registro_id=<?php echo $controlador->nom_nomina_id; ?>&session_id=<?php echo $controlador->session_id; ?>"
                               class="btn btn-info btn-guarda col-md-12 "><i class="icon-edit"></i>
                                Nueva Deduccion
                            </a>
                        </div>
                    </div>

                    <div class="buttons col-md-12 ">

                        <div class="col-md-6">
                            <a href="index.php?seccion=nom_nomina&accion=otro_pago&registro_id=<?php echo $controlador->nom_nomina_id; ?>&session_id=<?php echo $controlador->session_id; ?>"
                               class="btn btn-info btn-guarda col-md-12"><i class="icon-edit"></i>
                                Otro pago
                            </a>
                        </div>

                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-12 info-lista">
            <div class="col-lg-12 content">

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
                            <th>Id</th>
                            <th>Codigo</th>
                            <th>Descripcion</th>
                            <th>Importe Gravado</th>
                            <th>Importe Exento</th>
                            <th>Modifica</th>
                            <th>Elimina</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($controlador->percepciones->registros as $percepcion){?>
                            <tr>
                                <td><?php echo $percepcion['nom_par_percepcion_id']; ?></td>
                                <td><?php echo $percepcion['nom_percepcion_codigo']; ?></td>
                                <td><?php echo $percepcion['nom_par_percepcion_descripcion']; ?></td>
                                <td><?php echo $percepcion['nom_par_percepcion_importe_gravado']; ?></td>
                                <td><?php echo $percepcion['nom_par_percepcion_importe_exento']; ?></td>
                                <td><?php echo $percepcion['link_modifica']; ?></td>
                                <td><?php echo $percepcion['link_elimina']; ?></td>
                            </tr>
                        <?php } ?>

                        </tbody>
                    </table>
                    <div class="box-body">
                        * Total registros: <?php echo $controlador->percepciones->n_registros; ?><br />
                    </div>
                </div>

            </div>
        </div>

        <div class="col-md-12 info-lista">
            <div class="col-lg-12 content">

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
                            <th>Id</th>
                            <th>Codigo</th>
                            <th>Descripcion</th>
                            <th>Importe</th>
                            <th>Modifica</th>
                            <th>Elimina</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($controlador->deducciones->registros as $deduccion){?>
                            <tr>
                                <td><?php echo $deduccion['nom_par_deduccion_id']; ?></td>
                                <td><?php echo $deduccion['nom_deduccion_codigo']; ?></td>
                                <td><?php echo $deduccion['nom_par_deduccion_descripcion']; ?></td>
                                <td><?php echo $deduccion['nom_par_deduccion_importe_gravado']; ?></td>
                                <td><?php echo $deduccion['link_modifica']; ?></td>
                                <td><?php echo $deduccion['link_elimina']; ?></td>
                            </tr>
                        <?php } ?>

                        </tbody>
                    </table>
                    <div class="box-body">
                        * Total registros: <?php echo $controlador->deducciones->n_registros; ?><br />
                    </div>
                </div>

            </div>
        </div>

        <div class="col-md-12 info-lista">
            <div class="col-lg-12 content">

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
                            <th>Id</th>
                            <th>Codigo</th>
                            <th>Descripcion</th>
                            <th>Importe Gravado</th>
                            <th>Importe Exento</th>
                            <th>Modifica</th>
                            <th>Elimina</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($controlador->otros_pagos->registros as $otro_pago){?>
                            <tr>
                                <td><?php echo $otro_pago['nom_par_otro_pago_id']; ?></td>
                                <td><?php echo $otro_pago['nom_par_otro_pago_codigo']; ?></td>
                                <td><?php echo $otro_pago['nom_par_otro_pago_descripcion']; ?></td>
                                <td><?php echo $otro_pago['nom_par_otro_pago_importe_gravado']; ?></td>
                                <td><?php echo $otro_pago['nom_par_otro_pago_importe_exento']; ?></td>
                                <td><?php echo $otro_pago['link_modifica']; ?></td>
                                <td><?php echo $otro_pago['link_elimina']; ?></td>
                            </tr>
                        <?php } ?>

                        </tbody>
                    </table>
                    <div class="box-body">
                        * Total registros: <?php echo $controlador->otros_pagos->n_registros; ?><br />
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

