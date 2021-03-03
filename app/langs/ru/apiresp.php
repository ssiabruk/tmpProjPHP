<?php

/*
 *  POSHUK electron-optical complex
 *  API response messages
 *
 *  @author       Alex Grey
 *  @copyright    Copyright © 2019 Alex Grey (alex@grey.kiev.ua)
 *  @license      https://opensource.org/licenses/GPL-3.0
 *  @since        Version 1.0
 *
 */


$l['start']['ok'] = 'Комплекс запущен';
$l['start']['400'] = 'Режим не поддерживается';
$l['start']['409'] = 'Комплекс уже работает в другом режиме';
//$l['start']['Service is already started.'] = 'Комплекс уже работает в другом режиме';
//$l['start']['400 Bad Request: The browser (or proxy) sent a request that this server could not understand.'] = 'Режим не поддерживается';

$l['stop']['ok'] = 'Комплекс остановлен';
$l['stop']['409'] = 'Комплекс не запущен';

$l['shutdown']['ok'] = 'Комплекс выключен';
$l['test']['ok'] = 'Комплекс активный';
$l['reboot']['ok'] = 'Комплекс перезагружен';

$l['status']['grabber'] = 'Режим записи';
$l['status']['detector'] = 'Режим детектора';
$l['status']['active'] = 'Активный';
$l['status']['inactive'] = 'Неактивный';
$l['status']['failed'] = 'Отключен';
$l['status']['activating'] = 'Запуск';
$l['status']['deactivating'] = 'Остановка';

$l['status']['file'] = 'Демо';
/*$l['status']['3mp'] = '3 мегапиксела';
$l['status']['6mp_long'] = '6 мегапикселей с длинной экспозицией';
$l['status']['6mp_wide'] = '6 мегапикселей широкоформатная';
$l['status']['12mp_b2'] = '12 мегапикселей №2';
$l['status']['12mp_b3'] = '12 мегапикселей №3';
$l['status']['20mp_b2'] = '20 мегапикселей №2';
$l['status']['20mp_jpg_b2'] = '20 мегапикселей №2 (jpg)';*/
$l['status']['12mp_png'] = '12 мегапикселей';
$l['status']['12mp_raw'] = '12 мегапикселей';
$l['status']['20mp_png'] = '20 мегапикселей';
$l['status']['20mp_raw'] = '20 мегапикселей';

$l['status']['record'] = 'Запись';
$l['status']['detect'] = 'Детектор';
$l['status']['recdet'] = 'Все вместе';
$l['status']['preset'] = 'Режим работы';

$l['tele']['disk'] = 'Диск';
$l['tele']['disk_unit'] = 'ГБайт';
$l['tele']['disk_total'] = 'Всего';
$l['tele']['disk_used'] = 'Занято';
$l['tele']['disk_free'] = 'Свободно';

$l['tele']['temp'] = 'Температура';
$l['tele']['temp_camera'] = 'Камера';
$l['tele']['temp_mcpu'] = 'CPU';
$l['tele']['temp_gpu'] = 'GPU';
//$l['tele']['temp_bcpu'] = 'Процессор 3';
$l['tele']['temp_cpu'] = 'CPU';

$l['tele']['gps'] = 'Позиционирования';
$l['tele']['gps_sensor'] = 'Датчик';
$l['tele']['gps_connect_1'] = 'Активный';
$l['tele']['gps_connect_0'] = 'Не активный';

$l['tele']['host_time'] = 'Время комплекса';
