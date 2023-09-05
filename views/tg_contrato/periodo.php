<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_manifiesto $controlador */ ?>

<form class="row g-3" method="post" action="./index.php?seccion=tg_manifiesto&accion=periodo_alta_bd&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>">

    <?php echo $controlador->inputs->codigo; ?>
    <?php echo $controlador->inputs->tg_manifiesto_id; ?>
    <?php echo $controlador->inputs->descripcion; ?>
    <?php echo $controlador->inputs->nom_periodo_id; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Registrar</button>
    </div>
</form>

<div class="lista">
    <div class="card">
        <div class="card-header">
            <span class="text-header">Periodos</span>
        </div>
        <div class="card-body">
            <div class="cont_tabla_sucursal  col-md-12">
                <table class="table ">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Codigo</th>
                        <th>Descripcion</th>
                        <th>Periodo Nomina</th>
                        <th>Modifica</th>
                        <th>Elimina</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($controlador->periodos->registros as $periodo){?>
                        <tr>
                            <td><?php echo $periodo['tg_manifiesto_periodo_id']; ?></td>
                            <td><?php echo $periodo['tg_manifiesto_periodo_codigo']; ?></td>
                            <td><?php echo $periodo['tg_manifiesto_periodo_descripcion']; ?></td>
                            <td><?php echo $periodo['nom_periodo_descripcion']; ?></td>
                            <td><?php echo $periodo['link_modifica']; ?></td>
                            <td><?php echo $periodo['link_elimina']; ?></td>
                        </tr>
                    <?php } ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>




