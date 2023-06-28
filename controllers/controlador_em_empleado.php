<?php
namespace tglobally\tg_nomina\controllers;

use PDO;
use stdClass;

class controlador_em_empleado extends \tglobally\tg_empleado\controllers\controlador_em_empleado {

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass())
    {
        parent::__construct($link, $paths_conf);
    }

}
