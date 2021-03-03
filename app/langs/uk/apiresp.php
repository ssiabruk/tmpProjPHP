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


$l['start']['ok'] = 'Комплекс запущено';
$l['start']['400'] = 'Режим не підтримується';
$l['start']['409'] = 'Комплекс вже працює в іншому режимі';
//$l['start']['Service is already started.'] = 'Комплекс вже працює в іншому режимі';
//$l['start']['400 Bad Request: The browser (or proxy) sent a request that this server could not understand.'] = 'Режим не підтримується';

$l['stop']['ok'] = 'Комплекс зупинено';
$l['stop']['409'] = 'Комплекс не запущено';

$l['shutdown']['ok'] = 'Комплекс вимкнено';
$l['test']['ok'] = 'Комплекс активний';
$l['reboot']['ok'] = 'Комплекс перезавантажено';

$l['status']['grabber'] = 'Режим запису';
$l['status']['detector'] = 'Режим детектора';
$l['status']['active'] = 'Активний';
$l['status']['inactive'] = 'Неактивний';
$l['status']['failed'] = 'Відключений';
$l['status']['activating'] = 'Запуск';
$l['status']['deactivating'] = 'Зупинка';

$l['status']['file'] = 'Демо';
/*$l['status']['3mp'] = '3 мегапіксели';
$l['status']['6mp_long'] = '6 мегапікселів з довгою експозицією';
$l['status']['6mp_wide'] = '6 мегапікселів широкоформатна';
$l['status']['12mp_b2'] = '12 мегапікселів №2';
$l['status']['12mp_b3'] = '12 мегапікселів №3';
$l['status']['20mp_b2'] = '20 мегапікселів №2';
$l['status']['20mp_jpg_b2'] = '20 мегапікселів №2 (jpg)';*/
$l['status']['12mp_png'] = '12 мегапікселів';
$l['status']['12mp_raw'] = '12 мегапікселів';
$l['status']['20mp_png'] = '20 мегапікселів';
$l['status']['20mp_raw'] = '20 мегапікселів';

$l['status']['record'] = 'Запис';
$l['status']['detect'] = 'Детектор';
$l['status']['recdet'] = 'Все разом';
$l['status']['preset'] = 'Режим роботи';

$l['tele']['disk'] = 'Диск';
$l['tele']['disk_unit'] = 'ГБайт';
$l['tele']['disk_total'] = 'Всього';
$l['tele']['disk_used'] = 'Зайнято';
$l['tele']['disk_free'] = 'Вільно';

$l['tele']['temp'] = 'Температура';
$l['tele']['temp_camera'] = 'Камера';
$l['tele']['temp_mcpu'] = 'CPU';
$l['tele']['temp_gpu'] = 'GPU';
//$l['tele']['temp_bcpu'] = 'Процесор 3';
$l['tele']['temp_cpu'] = 'CPU';

$l['tele']['gps'] = 'Позіціонування';
$l['tele']['gps_sensor'] = 'Датчик';
$l['tele']['gps_connect_1'] = 'Активний';
$l['tele']['gps_connect_0'] = 'Не активний';

$l['tele']['host_time'] = 'Час комплексу';
