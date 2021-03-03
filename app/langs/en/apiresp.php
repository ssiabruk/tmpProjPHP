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


$l['start']['ok'] = 'The complex is running';
$l['start']['400'] = 'Mode not supported';
$l['start']['409'] = 'The complex is already operating in a different mode';
//$l['start']['Service is already started.'] = 'The complex is already operating in a different mode';
//$l['start']['400 Bad Request: The browser (or proxy) sent a request that this server could not understand.'] = 'Mode not supported';

$l['stop']['ok'] = 'Complex stopped';
$l['stop']['409'] = 'Complex not running';

$l['shutdown']['ok'] = 'Complex off';
$l['test']['ok'] = 'The complex is active';
$l['reboot']['ok'] = 'Complex rebooted';

$l['status']['grabber'] = 'Recording mode';
$l['status']['detector'] = 'Detector mode';
$l['status']['active'] = 'Active';
$l['status']['inactive'] = 'Inactive';
$l['status']['failed'] = 'Disconnected';
$l['status']['activating'] = 'Activating';
$l['status']['deactivating'] = 'Deactivating';

$l['status']['file'] = 'Demo';
/*$l['status']['3mp'] = '3 megapixels';
$l['status']['6mp_long'] = '6 megapixels with long exposure';
$l['status']['6mp_wide'] = '6 megapixels widescreen';
$l['status']['12mp_b2'] = '12 megapixels #2';
$l['status']['12mp_b3'] = '12 megapixels #3';
$l['status']['20mp_b2'] = '20 megapixels #2';
$l['status']['20mp_jpg_b2'] = '20 megapixels #2 (jpg)';*/
$l['status']['12mp_png'] = '12 megapixels';
$l['status']['12mp_raw'] = '12 megapixels';
$l['status']['20mp_png'] = '20 megapixels';
$l['status']['20mp_raw'] = '20 megapixels';

$l['status']['record'] = 'Record';
$l['status']['detect'] = 'Detector';
$l['status']['recdet'] = 'All together';
$l['status']['preset'] = 'Mode of operation';

$l['tele']['disk'] = 'HDD';
$l['tele']['disk_unit'] = 'GB';
$l['tele']['disk_total'] = 'Total';
$l['tele']['disk_used'] = 'Used';
$l['tele']['disk_free'] = 'Free';

$l['tele']['temp'] = 'Temperature';
$l['tele']['temp_camera'] = 'Camera';
$l['tele']['temp_mcpu'] = 'CPU';
$l['tele']['temp_gpu'] = 'GPU';
//$l['tele']['temp_bcpu'] = 'Processor 3';
$l['tele']['temp_cpu'] = 'CPU';

$l['tele']['gps'] = 'Positioning';
$l['tele']['gps_sensor'] = 'Sensor';
$l['tele']['gps_connect_1'] = 'Active';
$l['tele']['gps_connect_0'] = 'Not active';

$l['tele']['host_time'] = 'Host time';
