<?php /** @var \tglobally\tg_empleado\controllers\controlador_em_empleado $controlador */ ?>

<form class="row g-3" method="post"
      action="./index.php?seccion=em_empleado&accion=lee_archivo&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>"
      enctype="multipart/form-data">

    <div class="control-group col-12">
        <label class="control-label" for="archivo">Archivo Empleados</label>
        <div class="controls">
            <input type="file" id="archivo" name="archivo" multiple/>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next">Cargar</button>
    </div>
</form>


