<?php

/*
 *  POSHUK electron-optical complex
 *
 *  @author       Alex Grey
 *  @copyright    Copyright © 2020 Alex Grey (alex@grey.kiev.ua)
 *  @license      https://opensource.org/licenses/GPL-3.0
 *  @since        Version 1.0
 *
 */

// need for correct percents and resolutions

$images_persents = [
    'file' => [
        'image' => 55,
        'thumb' => 16
    ],
    '3mp' => [
        'image' => 55,
        'thumb' => 16
    ],
    '6mp_long' => [
        'image' => 55,
        'thumb' => 16
    ],
    '6mp_wide' => [
        'image' => 55,
        'thumb' => 16
    ],
    '12mp_b2' => [
        'image' => 55,
        'thumb' => 16
    ],
    '12mp_b3' => [
        'image' => 55,
        'thumb' => 16
    ],
    '20mp_b2' => [
        'image' => 55,
        'thumb' => 16
    ],
    '20mp_jpg_b2' => [
        'image' => 55,
        'thumb' => 16
    ]
];

$images_resizes = [
    'file' => [
        'image' => ['width' => 820,'height' => 600],
        'thumb' => ['width' => 260,'height' => 190]
    ],
    '3mp' => [
        'image' => ['width' => 820,'height' => 600],
        'thumb' => ['width' => 260,'height' => 190]
    ],
    '6mp_long' => [
        'image' => ['width' => 820,'height' => 600],
        'thumb' => ['width' => 260,'height' => 190]
    ],
    '6mp_wide' => [
        'image' => ['width' => 820,'height' => 600],
        'thumb' => ['width' => 260,'height' => 190]
    ],
    '12mp_b2' => [
        'image' => ['width' => 820,'height' => 600],
        'thumb' => ['width' => 260,'height' => 190]
    ],
    '12mp_b3' => [
        'image' => ['width' => 820,'height' => 600],
        'thumb' => ['width' => 260,'height' => 190]
    ],
    '20mp_b2' => [
        'image' => ['width' => 820,'height' => 600],
        'thumb' => ['width' => 260,'height' => 190]
    ],
    '20mp_jpg_b2' => [
        'image' => ['width' => 820,'height' => 600],
        'thumb' => ['width' => 260,'height' => 190]
    ]
];

return ['images_persents' => $images_persents, 'images_resizes' => $images_resizes];
