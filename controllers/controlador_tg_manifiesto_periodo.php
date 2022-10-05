<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace tglobally\tg_nomina\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\tg_manifiesto_periodo_html;
use models\tg_manifiesto_periodo;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use stdClass;

class controlador_tg_manifiesto_periodo extends system
{

    public array $keys_selects = array();

    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new tg_manifiesto_periodo(link: $link);
        $html_ = new tg_manifiesto_periodo_html(html: $html);
        $obj_link = new links_menu($this->registro_id);
        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, paths_conf: $paths_conf);

        $this->titulo_lista = 'Manifiesto Periodo';
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta = parent::alta(header: false);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $r_alta, header: $header, ws: $ws);
        }

        $inputs = (new tg_manifiesto_periodo_html(html: $this->html_base))->genera_inputs(controler: $this,
            keys_selects: $this->keys_selects);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

}
