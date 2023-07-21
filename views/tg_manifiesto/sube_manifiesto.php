<?php /** @var \tglobally\tg_nomina\controllers\controlador_tg_tipo_servicio $controlador */ ?>

<form class="row g-3" method="post"
      action="./index.php?seccion=tg_manifiesto&accion=lee_archivo&session_id=<?php echo $controlador->session_id; ?>&registro_id=<?php echo $controlador->registro_id; ?>"
      enctype="multipart/form-data">

    <div class="control-group col-12">
        <label class="control-label" for="archivo">Archivo Nomina</label>
        <div class="controls">
            <input type="file" id="archivo" name="archivo" multiple/>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next" value="Descarga Recibos Conjunto">Descarga Recibos Conjunto</button>
        <button type="submit" class="btn btn-primary" name="btn_action_next" value="Descarga Recibos" style="margin: 0 15px;">Descarga Recibos</button>
        <button type="submit" class="btn btn-primary" name="btn_action_next">Cargar</button>
    </div>

   <!-- <div class="col-4 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next" value="Descarga Recibos">Descarga Recibos</button>
    </div>

    <div class="col-4 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="btn_action_next" value="Descarga Recibos Conjunto">Descarga Recibos Conjunto</button>
    </div>-->

</form>

