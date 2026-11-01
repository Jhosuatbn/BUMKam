<?php

return [
    'zona_waktu' => env('KREDIT_TIMEZONE', 'Asia/Jakarta'),
    'denda' => [
        'masa_tenggang_hari' => (int) env('KREDIT_DENDA_MASA_TENGGANG_HARI', 7),
        'tarif_harian' => (float) env('KREDIT_DENDA_TARIF_HARIAN', 0.001),
        'batas_maksimum' => (float) env('KREDIT_DENDA_BATAS_MAKSIMUM', 0.05),
    ],
];
