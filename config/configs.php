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

//define('DS', DIRECTORY_SEPARATOR);

function siteURL()
{
    if (php_sapi_name() !== 'cli') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)?'https://':'http://';
        $domain = $_SERVER['HTTP_HOST'];
        return $protocol.$domain;
    }
    return 'localhost';
}

return [
    'settings' => [
        // Framework settings
        'displayErrorDetails' => true, // set to false at production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
    ],
    'configs' => [
        'environment' => 'development', // production|development (turns on demo mode)
        'client_id' => 'X1520',
        'site_url' => siteURL(),
        'map_url' => '/map/tiles',
        'cache_lifetime' => 86400,
        'session_lifetime' => 86400,
        'default_lang' => 'en',
        'ip_range' => '172.16.0.0/16',
        'apikey' => '1234567890',
        'zmq_port' => 5555,
        'image_port' => 8181,
        'stream_port' => 8181,
        'http_timeout' => 5,
        'tick_timeout' => 3,
        'allow_map_access' => true,

        // OS settings
        'os' => [
            'remote_storage_folder' => '/mnt/hgfs/sessions',
            'user' => 'nvidia'
        ],

        // View Renderer settings
        'view' => [
            'template_path' => BASE_PATH . '/app/views',
            'site_url' => siteURL()
        ],

        // Database settings
        'db' => [
            'host' => 'localhost',
            'user' => 'webclient',
            'passwd' => '84a0d71668def3b41108215d03e5492b',
            'dbname' => 'wc2storage'
        ],

        'mail' => [
            'host' => '172.16.88.201',
            'port' => 25,
            'user' => '',
            'passwd' => '',
            'from' => 'info@emlsolutions.com.ua',
            'nickname' => 'Poshuk',
            'auth' => false,
            'secure' => false
        ]
    ]
];
