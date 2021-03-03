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


namespace App\Libs;

//define('ZMQ_TCP_KEEPALIVE_FIX', 14400);

class Helper
{
    private static $urls_list = null;

    public static function xss($str)
    {
        $str = trim($str);
        $str = str_replace("\t", ' ', $str);
        $str = htmlentities($str, ENT_QUOTES | ENT_IGNORE, 'UTF-8');
        return $str;
    }

    public static function xss_array(array $data)
    {
        $result = [];
        foreach ($data as $key => $val) {
            $result[$key] = self::xss($val);
        }
        return $result;
    }

    public static function checkIpRange($ip, $range)
    {
        /* code by https://gist.github.com/tott */
        if (strpos($range, '/') == false) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }

    public static function listModes($lang)
    {
        $file = BASE_PATH . '/config/modes.json';
        $string = file_get_contents($file);
        $res = json_decode($string, true);
        $result = [];
        foreach ($res as $key => $val) {
            //$result[$key] = $val[$lang];
            $tmp = [];
            foreach ($val as $k=>$v) {
                if ($k == 'file') $k = 'recdet'; // dirty hack
                $tmp[] = ['mode'=>$k, 'param'=>$v['param'], 'title'=>$v[$lang]];
            }
            $result[$key] = $tmp;
        }
        return $result;
    }

    public static function getSessions($socket, $type = false, $uuid = false)
    {
        if (!$type) {
            return false;
        }
        switch($type){
            case 'all':
                $result = $socket->api('sessions');
                break;
            case 'specific':
                $result = $socket->api('session', false, $uuid);
                break;
            case 'current':
                $result = $socket->api('current');
                break;
            default:
                $result = false;
                break;
        }
        return $result;
    }

    public static function parseDeviceResult($data, $lang_labels)
    {
        if (@$data['response_status'] != 200) {
            return ['status'=>'error', 'message'=>'error_complex_data'];
        }

        $disk = '<strong>' . $lang_labels['tele']['disk'] . '</strong>';
        $disk_total = $lang_labels['tele']['disk_total'] . ': ';
        $disk_used = $lang_labels['tele']['disk_used'] . ': ';
        $disk_free = $lang_labels['tele']['disk_free'] . ': ';

        $disk_unit = '<span class="grey">' . $lang_labels['tele']['disk_unit'] . '</span>';
        $disk_total .= round($data['response_body']['disk']['total']/1024) . ' ' . $disk_unit;
        $disk_used .= round($data['response_body']['disk']['used']/1024) . ' ' . $disk_unit;
        $disk_free .= round($data['response_body']['disk']['free']/1024) . ' ' . $disk_unit;

        $temp = '<strong>' . $lang_labels['tele']['temp'] . '</strong>';
        $temp_camera = $lang_labels['tele']['temp_camera'] . ': ';
        $temp_cpu1 = $lang_labels['tele']['temp_mcpu'] . ': ';
        $temp_cpu2 = $lang_labels['tele']['temp_gpu'] . ': ';
        $temp_cpu3 = $lang_labels['tele']['temp_cpu'] . ': ';
        //$temp_cpu4 = $lang_labels['tele']['temp_bcpu'] . ': ';

        $deg = ' <span class="grey">&deg;C</span>';
        $temp_camera .= round($data['response_body']['temp']['camera'], 2) . $deg;
        $temp1 = $data['response_body']['temp']['jetson']['MCPU-therm']??false;
        $temp2 = $data['response_body']['temp']['jetson']['GPU-therm']??false;
        $temp3 = $data['response_body']['temp']['jetson']['CPU-therm']??false;
        //$temp4 = $data['response_body']['temp']['jetson']['BCPU-therm']??false;
        $temp_cpu_all = '';
        if ($temp1) {
            $temp_cpu1 .= round($temp1, 2) . $deg;
            $temp_cpu_all .= $temp_cpu1 . '<br />';
        }
        if ($temp2) {
            $temp_cpu2 .= round($temp2, 2) . $deg;
            $temp_cpu_all .= $temp_cpu2 . '<br />';
        }
        if ($temp3) {
            $temp_cpu3 .= round($temp3, 2) . $deg;
            $temp_cpu_all .= $temp_cpu3 . '<br />';
        }
        /*if ($temp4) {
            $temp_cpu4 .= round($temp4, 2) . $deg;
            $temp_cpu_all .= $temp_cpu4 . '<br />';
        }*/

        $gps = '<strong>' . $lang_labels['tele']['gps'] . '</strong>';
        $gps_sensor = $lang_labels['tele']['gps_sensor'] . ': ';
        $gps_connect = $data['response_body']['hardware']['gps']['connected'];
        $gps_connect_status = $lang_labels['tele']['gps_connect_' . $gps_connect];
        $coords = $gps_time = '';
        if ($data['tick']['latitude'] && $data['tick']['longitude']) {
            $coords = '<br />' . $data['tick']['latitude'] . ', ' . $data['tick']['longitude'];
        }
        if ($data['tick']['gps_time']) {
            $gps_time = '<br />' . date('d/m/Y (H:i:s)', strtotime($data['tick']['gps_time']));
        }

        $result = '<div class="col-6 mb-3">' . $disk . '<br />' . $disk_total . '<br />' . $disk_used . '<br />' . $disk_free . '</div>';
        $result.= '<div class="col-6 mb-3">' . $temp . '<br />' . $temp_camera . '<br />' . $temp_cpu_all . '</div>';
        $result.= '<div class="col-6 mb-3">' . $gps . '<br />' . $gps_sensor . $gps_connect_status . $coords . $gps_time . '</div>';
        if ($data['tick']['host_time']) {
            $time = '<strong>' . $lang_labels['tele']['host_time'] . '</strong>';
            $host_time = date('d/m/Y (H:i:s)', strtotime($data['tick']['host_time']));
            $result.= '<div class="col-6 mb-3">' . $time . '<br />' . $host_time . '</div>';
        }

        return [
            'status'=>'success',
            'message'=>$result
        ];
    }

    public static function parseControlResult($command, $data, $lang_labels)
    {
        //$test = print_r($data, true);
        //$log_file_name = 'API-' . date('Y-m-d-H-i-s') . microtime(true) . '.log';
        //error_log($test, 3, BASE_PATH . '/var/logs/' . $log_file_name);
        if (!$data) {
            return ['status'=>'error', 'message'=>'error_complex_data'];
        }
        $message_api_index = array_values($data['response_body'])[0];
        $message = $lang_labels[$command][$message_api_index]??$message_api_index;
        if (@$data['response_status'] == 400) {
            return [
                'status'=>'error',
                'message'=>$lang_labels[$command]['400']
            ];
        }
        if (@$data['response_status'] == 409) {
            return [
                'status'=>'error',
                'message'=>$lang_labels[$command]['409']
            ];
        }
        if (@$data['response_status'] == 503) {
            return [
                'status'=>'error',
                'message'=>'Critical error!'
            ];
        }
        if (@$data['response_status'] == 500) {
            return [
                'status'=>'error',
                'message'=>'INTERNAL SERVER ERROR'
            ];
        }
        if (@$data['response_status'] != 200) {
            return [
                'status'=>'error',
                'message'=>$message
            ];
        }
        return [
            'status'=>'success',
            'message'=>$message
        ];
    }

    public static function parseInfoResult($data)
    {
        if (@$data['response_status'] != 200) {
            return [
                'status'=>'error'
            ];
        }
        $prod_id = $data['response_body']['product_id']??'Not available';
        return [
            'status'=>'success',
            'cid'=>$prod_id
        ];
    }

    public static function parseStatusResult($data, $lang_labels)
    {
        if (@$data['response_status'] != 200) {
            return [
                'status'=>'error'
            ];
        }

        $grabber_status = $lang_labels['status'][$data['response_body']['grabber']['active']]??'NONE';
        $grabber = $lang_labels['status']['grabber'] . ': ' . $grabber_status;

        $detector_status = $lang_labels['status'][$data['response_body']['detector']['active']]??'NONE';
        $detector = $lang_labels['status']['detector'] . ': ' . $detector_status;

        $preset_task = $lang_labels['status'][$data['response_body']['preset']['task']]??'NULL';
        $preset_mode = $lang_labels['status'][$data['response_body']['preset']['mode']]??'NULL';
        //$preset_mode = $lang_labels['status'][$data['mode']]??'NULL';
        $preset = $lang_labels['status']['preset'] . ': ' . $preset_task . ', ' . $preset_mode;

        return [
            'status'=>'success',
            'message'=>$grabber . '<br />' . $detector . '<br />' . $preset
        ];
    }

    public static function parseCurrentSession($data)
    {
        if (@$data['response_status'] != 200) {
            return 'false';
        }
        return $data['response_body']['uuid'];
    }

    public static function checkServicesStatus()
    {
        // for diagnostic module
    }

    /*public static function servicesRestart()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $command1 = BASE_PATH . '/stopAllServices';
            $command2 = BASE_PATH . '/startAllServices';
            exec($command1);
            usleep(500000);
            exec($command2);
            usleep(500000);
            return true;
        } else
            return false;
    }*/

    /*public static function servicesZMQfix()
    {
        $restart_time = 0;
        $current_time = time();
        $handle = @fopen(BASE_PATH . '/logs/client.tmp', 'r');
        if ($handle) {
            $restart_time = fgets($handle);
        }
        fclose($handle);
        if (($current_time - $restart_time) > ZMQ_TCP_KEEPALIVE_FIX){
            Helper::servicesRestart();
            $handle = @fopen(BASE_PATH . '/logs/client.tmp', 'w');
            if ($handle) {
                $restart_time = time();
                fwrite($handle, $restart_time);
            }
            fclose($handle);
        }
    }*/

    public static function prepareUrl($url_id, $params)
    {
        if (!self::$urls_list) {
            $file = BASE_PATH . '/config/urls.json';
            $string = file_get_contents($file);
            self::$urls_list = json_decode($string, true);
        }
        if (!isset(self::$urls_list[$url_id])) {
            return '';
        }
        $url = self::$urls_list[$url_id];
        return str_replace(array_keys($params), array_values($params), $url);
    }

    /*public static function parseLostDetects($data)
    {
        if (!$data) {
            return false;
        }
    }*/
}
