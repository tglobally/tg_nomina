<?php /** @var \tglobally\tg_nomina\controllers\controlador_nom_periodo $controlador */ ?>

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
                        <th data-breakpoints="xs sm md" data-type="html" >Fecha inicial pago</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Fecha final pago</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Codigo empleado</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Empleado</th>
                        <th data-breakpoints="xs sm md" data-type="html" >Empresa</th>
                        <th data-breakpoints="xs sm md" data-type="html" data-filterable="false">Modifica</th>
                        <th data-breakpoints="xs sm md" data-type="html" data-filterable="false">Elimina</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($controlador->nominas->registros as $nomina){?>
                        <tr>
                            <td><?php echo $nomina['nom_nomina_id']; ?></td>
                            <td><?php echo $nomina['nom_nomina_codigo']; ?></td>
                            <td><?php echo $nomina['nom_nomina_fecha_inicial_pago']; ?></td>
                            <td><?php echo $nomina['nom_nomina_fecha_final_pago']; ?></td>
                            <td><?php echo $nomina['em_empleado_codigo']; ?></td>
                            <td><?php echo $nomina['em_empleado_nombre'].' '.$nomina['em_empleado_ap'].' '.$nomina['em_empleado_am']; ?></td>
                            <td><?php echo $nomina['org_empresa_id']; ?></td>
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

