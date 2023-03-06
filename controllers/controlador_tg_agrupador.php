<?php
namespace tglobally\tg_nomina\controllers;

use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\tg_agrupador_html;
use tglobally\tg_nomina\models\tg_agrupador;
use PDO;
use stdClass;

class controlador_tg_agrupador extends system {

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new tg_agrupador(link: $link);
        $html_ = new tg_agrupador_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);
        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, paths_conf: $paths_conf);

        $this->titulo_lista = 'Agrupador';
    }

}
