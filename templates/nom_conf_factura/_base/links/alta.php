<?php /** @var tglobally\tg_nomina\controllers\controlador_nom_conf_factura $controlador */ ?>
<?php /** @var string $seccion */
use config\generales;
?>
<a href="index.php?seccion=nom_conf_factura&accion=alta&session_id=<?php echo (new generales())->session_id; ?>">
    <?php include "templates/$controlador->seccion/_base/buttons/_1.azul.alta.php"; ?>
</a>