<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_nomina $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_nom_nomina_modifica_bd; ?>">

    <?php echo $controlador->inputs->id; ?>
    <?php echo $controlador->inputs->select->em_registro_patronal_id; ?>
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

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Actualizar</button>
    </div>
</form>

<div class="lista">
    <div class="card">
        <div class="card-header">
            <span class="text-header">Percepciones</span>
        </div>
        <div class="card-body">
            <div class="cont_tabla_sucursal  col-md-12">
                <table class="table">
                    <thead>
                    <tr>
                        <th data-breakpoints="xs sm md" data-type="html" >Id</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Codigo</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Descripcion</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Importe Gravado</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Importe Exento</th>
                        <th data-breakpoints="xs sm md" data-type="html" data-filterable="false">Modifica</th>
                        <th data-breakpoints="xs sm md" data-type="html" data-filterable="false">Elimina</th>
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
            </div>
            <div class="col-sm-3">
                <a href="index.php?seccion=nom_nomina&accion=nueva_percepcion&registro_id=<?php echo $controlador->nom_nomina_id; ?>&session_id=<?php echo $controlador->session_id; ?>"
                   class="btn btn-info btn-guarda col-md-12"><i class="icon-edit"></i>Nueva Percepcion
                </a>
            </div>
        </div>
    </div>
</div>

<div class="lista">
    <div class="card">
        <div class="card-header">
            <span class="text-header">Deducciones</span>
        </div>
        <div class="card-body">
            <div class="cont_tabla_sucursal  col-md-12">
                <table class="table ">
                    <thead>
                    <tr>
                        <th data-breakpoints="xs sm md" data-type="html" >Id</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Codigo</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Descripcion</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Importe</th>
                        <th data-breakpoints="xs sm md" data-type="html" data-filterable="false">Modifica</th>
                        <th data-breakpoints="xs sm md" data-type="html" data-filterable="false">Elimina</th>
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
            </div>
            <div class="col-sm-3">
                <a href="index.php?seccion=nom_nomina&accion=nueva_deduccion&registro_id=<?php echo $controlador->nom_nomina_id; ?>&session_id=<?php echo $controlador->session_id; ?>"
                   class="btn btn-info btn-guarda col-md-12 "><i class="icon-edit"></i>Nueva Deduccion
                </a>
            </div>
        </div>
    </div>
</div>

<div class="lista">
    <div class="card">
        <div class="card-header">
            <span class="text-header">Otros pagos</span>
        </div>
        <div class="card-body">
            <div class="cont_tabla_sucursal  col-md-12">
                <table class="table ">
                    <thead>
                    <tr>
                        <th data-breakpoints="xs sm md" data-type="html" >Id</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Codigo</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Descripcion</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Importe Gravado</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Importe Exento</th>
                        <th data-breakpoints="xs sm md" data-type="html" data-filterable="false">Modifica</th>
                        <th data-breakpoints="xs sm md" data-type="html" data-filterable="false">Elimina</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($controlador->otros_pagos->registros as $otro_pago){?>

                        <tr>
                            <td><?php echo $otro_pago['nom_par_otro_pago_id']; ?></td>
                            <td><?php echo $otro_pago['nom_otro_pago_codigo']; ?></td>
                            <td><?php echo $otro_pago['nom_par_otro_pago_descripcion']; ?></td>
                            <td><?php echo $otro_pago['nom_par_otro_pago_importe_gravado']; ?></td>
                            <td><?php echo $otro_pago['nom_par_otro_pago_importe_exento']; ?></td>
                            <td><?php echo $otro_pago['link_modifica']; ?></td>
                            <td><?php echo $otro_pago['link_elimina']; ?></td>
                        </tr>
                    <?php } ?>

                    </tbody>
                </table>
            </div>
            <div class="col-sm-3">
                <a href="index.php?seccion=nom_nomina&accion=otro_pago&registro_id=<?php echo $controlador->nom_nomina_id; ?>&session_id=<?php echo $controlador->session_id; ?>"
                   class="btn btn-info btn-guarda col-md-12"><i class="icon-edit"></i>Otro pago
                </a>
            </div>
        </div>
    </div>
</div>

<div class="lista">
    <div class="card">
        <div class="card-header">
            <span class="text-header">Cuotas Obrero Patronales</span>
        </div>
        <div class="card-body">
            <div class="cont_tabla_sucursal  col-md-12">
                <table class="table ">
                    <thead>
                    <tr>
                        <th data-breakpoints="xs sm md" data-type="html" >Concepto</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Monto</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($controlador->cuotas_obrero_patronales->registros as $cuota_obrero_patronal){?>
                        <tr>
                            <td><?php echo $cuota_obrero_patronal['nom_tipo_concepto_imss_descripcion']; ?></td>
                            <td><?php echo $cuota_obrero_patronal['nom_concepto_imss_monto']; ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td style="text-align: right">Total:</td>
                        <td><?php echo $controlador->cuota_total; ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


