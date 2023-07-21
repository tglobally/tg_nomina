<?php /** @var \controllers\controlador_adm_session $controlador */

$usuario = $controlador->datos_session_usuario['adm_usuario_nombre'] . " ";
$usuario .= $controlador->datos_session_usuario['adm_usuario_ap'] . " ";
$usuario .= $controlador->datos_session_usuario['adm_usuario_am'];
?>

<div class=" w-100 d-flex justify-content-center align-items-center">
    <div class="col-md-12">
        <div class="clearfix text-center">
            <h4 class="pt-3 text-uppercase">Hola, <span style="color: #000098"><?php echo $usuario ?></span></h4>
            <p class="text-medium-emphasis">Selecciona una opción del menú que deseas utilizar</p>
        </div>
    </div>
</div>
