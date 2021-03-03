<?php

/*
 *  POSHUK electron-optical complex
 *
 *  @author       Alex Grey
 *  @copyright    Copyright © 2019 Alex Grey (alex@grey.kiev.ua)
 *  @license      https://opensource.org/licenses/GPL-3.0
 *  @since        Version 1.0
 *
 */

return [
    'sessions' => [
        'uri' => '/api/v1/sessions/',
        'method' => 'get'
    ],
    'session' => [
        'uri' => '/api/v1/sessions',
        'method' => 'get'
        // add params
    ],
    'current' => [
        'uri' => '/api/v1/sessions/.',
        'method' => 'get'
    ],
    'lost' => [
        'uri' => '/api/v1/sessions/.',
        'method' => 'get',
        'params' => [
            'from' => '?*?',
            'to' => '?*?'
        ]
    ],
    'test' => [
        'uri' => '/api/v1/health',
        'method' => 'get'
    ],
    'info' => [
        'uri' => '/api/v1/info/searcher',
        'method' => 'get'
    ],
    'status' => [
        'uri' => '/api/v1/status/searcher',
        'method' => 'get'
    ],
    'device' => [
        'uri' => '/api/v1/status/device',
        'method' => 'get'
    ],
    'serial' => [
        'uri' => '/api/v1/info/device',
        'method' => 'get'
    ],
    'start' => [
        'uri' => '/api/v1/start/searcher',
        'method' => 'post',
        'params' => [
            'task' => [
                'record',
                //'detect',
                'recdet'
            ],
            'mode' => '?*?' // special value for external resource
            /*'mode' => [
                '20mp_jpg_b2',
                '20mp_b2',
                '12mp_b3',
                '12mp_b2',
                '6mp_long',
                '6mp_wide',
                '3mp',
                'file',
            ]*/
        ]
    ],
    'stop' => [
        'uri' => '/api/v1/stop/searcher',
        'method' => 'post'
    ],
    'reboot' => [
        'uri' => '/api/v1/shutdown',
        'method' => 'post',
        'params' => [
            'reboot' => '1'
        ]
    ],
    /*'shutdown' => [
        'uri' => '/api/v1/shutdown',
        'method' => 'post'
    ]*/
];
