<?php

namespace tglobally\tg_nomina\controllers;

use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Reporte_Template
{
    const REPORTE_GENERAL = [
        "A:AO" => [
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ]
        ],
        "C" => [
            'alignment' => [
                'horizontal' => 'left',
                'vertical' => 'center',
            ]
        ],
        "I:AO" => [
            'numberFormat' => [
                'formatCode' => "$#,##0.00;-$#,##0.00",
            ],
        ],
        "A1:A3" => [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'startColor' => array('rgb' => '0070C0')
            ),
            'alignment' => [
                'horizontal' => 'right',
                'vertical' => 'center',
            ]
        ],
        "D1:D3" => [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'startColor' => array('rgb' => '0070C0')
            ),
            'alignment' => [
                'horizontal' => 'right',
                'vertical' => 'center',
            ]
        ],
        "B1:B3" => [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ]
        ],
        "E1:E3" => [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ]
        ],


        "A4:AO4" => [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'startColor' => array('rgb' => '0070C0')
            ),
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ]
        ],

    ];

    const REPORTE_NOMINA = [

        "A:BA" => [
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ]
        ],
        "C" => [
            'alignment' => [
                'horizontal' => 'left',
                'vertical' => 'center',
            ]
        ],

        "N:AZ" => [
            'numberFormat' => [
                'formatCode' => "$#,##0.00;-$#,##0.00",
            ],
        ],
        "A1:A4" => [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'startColor' => array('rgb' => '0070C0')
            ),
            'alignment' => [
                'horizontal' => 'right',
                'vertical' => 'center',
            ]
        ],
        "Z4:AK4" => [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'startColor' => array('rgb' => '002060')
            ),
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ]
        ],

        "B1:B4" => [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ]
        ],
        "A5:BA5" => [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => array(
                'fillType' => Fill::FILL_SOLID,
                'startColor' => array('rgb' => '0070C0')
            ),
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ]
        ],


    ];



}