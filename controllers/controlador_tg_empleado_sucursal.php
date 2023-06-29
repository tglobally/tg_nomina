<?php
namespace tglobally\tg_nomina\controllers;

use PDO;
use stdClass;
use tglobally\tg_nomina\models\tg_empleado_sucursal;

class controlador_tg_empleado_sucursal extends \tglobally\tg_empleado\controllers\controlador_tg_empleado_sucursal
{
    public function __construct(PDO $link, \gamboamartin\template\html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        parent::__construct($link, $html, $paths_conf);

        $this->modelo = new tg_empleado_sucursal($link);
    }

}
