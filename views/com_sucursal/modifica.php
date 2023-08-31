<?php /** @var \tglobally\tg_nomina\controllers\controlador_com_cliente $controlador */ ?>

<form class="row g-3" method="post" action="<?php echo $controlador->link_modifica_bd; ?>">

    <?php echo $controlador->inputs->com_tipo_sucursal_id; ?>
    <?php echo $controlador->inputs->codigo; ?>
    <?php echo $controlador->inputs->nombre_contacto; ?>
    <?php echo $controlador->inputs->com_cliente_id; ?>
    <?php echo $controlador->inputs->tg_tipo_provision_id; ?>
    <?php echo $controlador->inputs->dp_pais_id; ?>
    <?php echo $controlador->inputs->dp_estado_id; ?>
    <?php echo $controlador->inputs->dp_municipio_id; ?>
    <?php echo $controlador->inputs->dp_cp_id; ?>
    <?php echo $controlador->inputs->dp_colonia_postal_id; ?>
    <?php echo $controlador->inputs->dp_calle_pertenece_id; ?>
    <?php echo $controlador->inputs->numero_exterior; ?>
    <?php echo $controlador->inputs->numero_interior; ?>

    <?php echo $controlador->inputs->telefono_1; ?>
    <?php echo $controlador->inputs->telefono_2; ?>
    <?php echo $controlador->inputs->telefono_3; ?>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Actualizar</button>
    </div>
</form>


