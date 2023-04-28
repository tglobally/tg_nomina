<?php
namespace tglobally\tg_nomina\controllers;

use PDO;
use stdClass;
use tglobally\template_tg\html;

class controlador_nom_nomina_documento extends \gamboamartin\nomina\controllers\controlador_nom_nomina_documento {

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){
        $html_base = new html();
        parent::__construct( link: $link, html: $html_base);
    }
}
