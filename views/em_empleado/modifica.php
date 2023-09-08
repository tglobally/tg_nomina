<?php
use config\generales;


$views = new generales();

$url = $views->path_base."/views/em_empleado/views/";

?>


<div class="tab-content rounded-bottom">
    <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1016">
        <nav>
            <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-home-tab" data-coreui-toggle="tab"
                        data-coreui-target="#nav-home" type="button" role="tab" aria-controls="nav-home"
                        aria-selected="true">Datos Generales
                </button>
                <button class="nav-link" id="nav-profile-tab" data-coreui-toggle="tab" data-coreui-target="#nav-profile"
                        type="button" role="tab" aria-controls="nav-profile" aria-selected="false" tabindex="-1">Cuenta Bancaria
                </button>
                <button class="nav-link" id="nav-contact-tab" data-coreui-toggle="tab" data-coreui-target="#nav-contact"
                        type="button" role="tab" aria-controls="nav-contact" aria-selected="false" tabindex="-1">Cliente Relacionado
                </button>
                <button class="nav-link" id="nav-provisiones-tab" data-coreui-toggle="tab" data-coreui-target="#nav-provisiones"
                        type="button" role="tab" aria-controls="nav-provisiones" aria-selected="false" tabindex="-1">Provisiones
                </button>
                <button class="nav-link" id="nav-percepciones-tab" data-coreui-toggle="tab" data-coreui-target="#nav-percepciones"
                        type="button" role="tab" aria-controls="nav-percepciones" aria-selected="false" tabindex="-1">Percepciones
                </button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade active show" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                <?php include $url."modifica.php";?>
            </div>
            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                <?php include $url."cuenta_bancaria.php";?>
            </div>
            <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                <?php include $url."asigna_sucursal.php";?>
            </div>
            <div class="tab-pane fade" id="nav-provisiones" role="tabpanel" aria-labelledby="nav-contact-tab">
                <?php include $url."asigna_provision.php";?>
            </div>
            <div class="tab-pane fade" id="nav-percepciones" role="tabpanel" aria-labelledby="nav-contact-tab">
                <?php include $url."asigna_percepcion.php";?>
            </div>
        </div>
    </div>
</div>




