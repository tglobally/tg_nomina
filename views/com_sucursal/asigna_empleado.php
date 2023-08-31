<?php /** @var \tglobally\tg_nomina\controllers\controlador_com_cliente $controlador */ ?>

<form class="row g-3" method="post" action="./index.php?seccion=com_sucursal&accion=rel_empleado_sucursal_bd&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>">

    <?php echo $controlador->inputs->select->com_sucursal_id; ?>
    <?php echo $controlador->inputs->select->em_empleado_id; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Relacionar</button>
    </div>
</form>


<div class="col-lg-12 row-12">
    <table id="em_empleado" class="table table-striped" >
        <thead>
        <tr>
            <th>Id</th>
            <th>Descripcion</th>
            <th>RFC</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($controlador->em_empleados as $em_empleado){ ?>
            <tr>
                <td><?php echo $em_empleado['em_empleado_id']; ?></td>
                <td><?php echo $em_empleado['em_empleado_descripcion']; ?></td>
                <td><?php echo $em_empleado['em_empleado_rfc']; ?></td>

                <td>
                    <?php foreach ($em_empleado['acciones'] as $link){ ?>
                        <div class="col-md-3"><?php echo $link; ?></div>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>

    </table>
</div>