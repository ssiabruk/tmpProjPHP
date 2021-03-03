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


return [
    'services' => [
        'ticks' =>   '/usr/bin/php /var/www/html/app/cli.php --c ticks --n %d 2>/dev/null >/dev/null &',
        'detects' => '/usr/bin/php /var/www/html/app/cli.php --c detects --n %d 2>/dev/null >/dev/null &',
        'images' =>  '/usr/bin/php /var/www/html/app/cli.php --c images --n %d 2>/dev/null >/dev/null &',
        'alarms' =>  '/usr/bin/php /var/www/html/app/cli.php --c alarms --n %d 2>/dev/null >/dev/null &',
    ],
    'proc' => 'ps ax | grep -- ',
    'kill' => 'kill -9 ',
    'lost' => '/usr/bin/php /var/www/html/app/cli.php --c lost --n %d 2>/dev/null >/dev/null &',
    'stop' => '/usr/bin/php /var/www/html/app/cli.php --c lost --n %d --s %s 2>/dev/null >/dev/null &',
    'test' => '/usr/bin/php /var/www/html/app/cli.php --c test'
];
