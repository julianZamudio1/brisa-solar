<?php
// GENERADO por site/scripts/construir-tema.mjs a partir de src/lib/calculadora.ts. No editar a mano.

return [
    'inicial' => [
        'viable' => true,
        'consumoBimestralKwh' => 734,
        'tarifaDac' => false,
        'paneles' => 6,
        'potenciaKwp' => 3.3,
        'generacionBimestralKwh' => 879,
        'cobertura' => 1,
        'reciboNuevo' => 60,
        'ahorroBimestral' => 2440,
        'ahorroAnual' => 14640,
        'inversion' => [
            'estimada' => 64500,
            'minima' => 58000,
            'maxima' => 71000,
        ],
        'retornoAnios' => 4.4,
        'ahorro25Anios' => 569000,
        'co2EvitadoToneladasAnio' => 2.3,
    ],
    'hsp' => [
        'norte' => 6,
        'centro' => 5.6,
        'sur' => 5.1,
    ],
    'supuestos' => [
        'rendimiento' => 0.78,
        'potenciaPanelW' => 550,
        'diasBimestre' => 61,
        'incrementoTarifaAnual' => 0.04,
        'degradacionAnual' => 0.005,
        'vidaUtilAnios' => 25,
        'factorEmisionKgPorKwh' => 0.438,
        'reciboMinimoRecomendado' => 400,
    ],
    'tarifa' => [
        'domestica' => [
            'basico' => [
                'kwh' => 150,
                'precio' => 1.3,
            ],
            'intermedio' => [
                'kwh' => 130,
                'precio' => 1.58,
            ],
            'excedente' => [
                'precio' => 4.62,
            ],
            'limiteDacBimestral' => 500,
            'dac' => [
                'cargoFijo' => 300,
                'precio' => 6.4,
            ],
            'minimoBimestral' => 60,
        ],
        'negocio' => [
            'cargoFijo' => 240,
            'precio' => 5.5,
        ],
    ],
];
