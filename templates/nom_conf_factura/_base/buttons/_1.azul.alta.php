<?php /** @var tglobally\tg_nomina\controllers\controlador_nom_conf_factura $controlador */ ?>
<?php /** @var string $seccion */ ?>
<?php use config\views; ?>
<?php include (new views())->ruta_templates."number.php"; ?>
<?php echo($controlador->html_base->menu_lateral('Nueva configuracion')); ?>
