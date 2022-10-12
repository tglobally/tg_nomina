<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\tg_manifiesto_otro_pago_html;
use models\tg_manifiesto_otro_pago;
use PDO;
use stdClass;

class controlador_tg_manifiesto_otro_pago extends system {

    public array $keys_selects = array();

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new tg_manifiesto_otro_pago(link: $link);
        $html_ = new tg_manifiesto_otro_pago_html(html: $html);
        $obj_link = new links_menu($this->registro_id);
        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, paths_conf: $paths_conf);

        $this->titulo_lista = 'Manifiesto Otro Pago';
    }
}
