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


namespace App\Controllers;

use Slim\Container;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use App\Models\Settings;
use App\Models\Events;
use App\Models\System;
use App\Libs\ApiSockets;
use App\Libs\AppException;
use App\Libs\ImageOptions;
use App\Libs\Email;
use App\Libs\Helper;

class ConsoleController
{
    private $db;
    private $di;
    private $config;
    private $port;
    private $context;
    private $events;
    private $clist; // complex list
    private $imgopts;
    private $cam_modes = [];
    private $mail_sender;
    private $mail_timer;
    private $mail_timeout = 60; // seconds
    private $mail_control_time;
    private $complex = false;
    private $write_log = false;
    private $lost_timeout = 30;
    private $lost_control_time;
    private $session_id = false;
    //private $need_lost = false;
    //private $tick_timeout = 3;
    //private $tick_time;

    public function __construct(Container $di)
    {
        $this->di = $di;
        $this->db = $this->di->get('db');
        $this->events = new Events($this->db);
        $this->system = new System($this->db);
        $this->mail_sender = new Email($this->di);
        $this->mail_timer = time();
        $this->mail_control_time = time()+1;
        $this->lost_control_time = time();
        $this->config = $this->di->get('configs');
    }

    public function setComplex($cid)
    {
        if ($cid && is_numeric($cid)) {
            $this->complex = (new Settings($this->db))->getComplexByID($cid);
        }
    }

    public function setSession($sid)
    {
        $this->session_id = $sid;
    }

    private function initZMQ()
    {
        if (!$this->complex) {
             exit(0);
        }
        $this->port = $this->config['zmq_port'];
        $this->context = new \ZMQContext();
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
    }

    /*public function setComplex($complex)
    {
        $this->complex = $complex;
    }*/

    public function wc2ticks()
    {
        $this->initZMQ();
        $subscriber = $this->connect($this->complex['cip'], 'ticker');
        $this->loop($subscriber, 'ticker');
    }

    public function wc2detects()
    {
        $this->initZMQ();
        $subscriber = $this->connect($this->complex['cip'], 'detections');
        $this->loop($subscriber, 'detections');
    }

    public function wc2alarms()
    {
        $this->initZMQ();
        $subscriber = $this->connect($this->complex['cip'], 'alarms');
        $this->loop($subscriber, 'alarms');
    }

    public function wc2images()
    {
        $this->imgopts = new ImageOptions($this->system->getDeparturedList());
        while (true) {
            $detect_images = $this->events->getDetectsWOImage(20);
            if (!$detect_images) {
                sleep($this->config['http_timeout']);
                //echo "next \r\n"; ob_flush(); flush();
                continue;
            }
            $this->image_loop($detect_images);
            usleep(500000);
        }
    }

    public function wc2lost()
    {
        $cid = $this->complex['id'];
        error_log('start' . "\n", 3, BASE_PATH . '/temp/logs/' . $cid . '.lost.log');
        $writeLogAndExit = function($complex_id, $error_message) {
            error_log($error_message . "\n", 3, BASE_PATH . '/temp/logs/' . $complex_id . '.lost.log');
            exit();
        };
        if (!$this->session_id) {
            $data = $this->system->getDeparture($cid, true);
        } else {
            $data = $this->system->getDeparture($cid, false);
        }
        if (!$data) {
            exit(); // complex not departured
        }

        /*$path = BASE_PATH . '/temp/proc/' . $cid . '.lost.tmp';
        $handle = @fopen($path, 'r');
        if (!$handle) {
            $error = 'Cant open <detlost> file';
            $writeLogAndExit($cid, $error);
        }
        $last_detect_file = fgets($handle);
        if (!$handle) {
            $error = 'Last detect file empty';
            $writeLogAndExit($cid, $error);
        }
        fclose($handle);*/

        $error = false;
        $socket = new ApiSockets($this->writelog, 'lost.' . $cid);
        $socket_config = [
            'host' => $this->complex['cip'],
            'port' => $this->complex['cpt'],
            'key' => $this->config['apikey']
        ];
        $lost_socket = $socket->socketWork($socket_config);
        if (!$lost_socket) {
            $error = 'Cant open socket to ' . $this->complex['cip'];
        }
        //$result = $lost_socket->api('lost', ['from' => $last_detect_file]);
        if (!$this->session_id) {
            $result = $lost_socket->api('lost');
        } else {
            $result = $lost_socket->api('session', false, $this->session_id);
        }
        $lost_socket->close();
        if (!$result) {
            $error = 'Complex ' . $this->complex['cip'] . ' not response';
        }
        if ($result['status'] == 'error') {
            $error = 'Complex ' . $this->complex['cip'] . ' return error: ' . $result['message'];
        }
        if ($error) {
            $writeLogAndExit($cid, $error);
        }
        if (@$result['response_status'] == 200 &&
            isset($result['response_body']['detections']) &&
            count($result['response_body']['detections'] > 0)
        ) {
            //$data = $this->events->getDetectionByFile($last_detect_file);
            $res['session'] = $data['session_id'];
            $res['cam_mode'] = $data['cam_mode'];
            //$result['cam_mode'] = $this->system->getDepartureBySession($cid, $result['session'])['cam_mode'];
            foreach ($result['response_body']['detections'] as $dt) {
                $res['objects'] = @json_encode($dt['data']);
                $res['gps_time'] = @$dt['gps']['datetime'];
                $res['latitude'] = @$dt['gps']['latitude'];
                $res['longitude'] = @$dt['gps']['longitude'];
                $res['time_marker'] = $this->getTimeMarker(@$dt['file']);
                $res['file_name'] = @$dt['file'];
                $url_params = ['%det' => 25,
                               '%dir' => 'session/' . $res['session'],
                               '%file' => str_replace('.txt', '', $res['file_name'])
                ];
                $res['url'] = Helper::prepareUrl('image_detect', $url_params);
                $tracks = [];
                foreach ($dt['data'] as $tr) {
                    $tracks[] = (int) $tr['track_id'];
                }
                $tracks = array_filter($tracks);
                $tracks = array_unique($tracks);
                foreach ($tracks as $t) {
                    $this->events->saveDetections($cid, $res, $t);
                }
            }
            //@unlink($path);
        }
        $tmp = count($result['response_body']['detections']);
        error_log('stop ' . $tmp . "\n", 3, BASE_PATH . '/temp/logs/' . $cid . '.lost.log');
        exit();
    }

    private function connect($ip, $channel)
    {
        $subscriber = $this->context->getSocket(\ZMQ::SOCKET_SUB);
        $subscriber->setSockOpt(\ZMQ::SOCKOPT_SUBSCRIBE, $channel);
        $subscriber->connect('tcp://' . $ip . ':'.$this->port);
        return $subscriber;
    }

    private function loop($subscriber, $channel)
    {
        while (true) {
            $string = $subscriber->recvMulti(\ZMQ::MODE_NOBLOCK);
            if ($string) {
                if (is_array($string)) {
                    $string = $string[1];
                }
                $string = json_decode($string, true);
                if ($string) {
                    $func = $channel . 'Save';
                    $this->$func($string);
                    if ($this->write_log) {
                        $mess = var_export($string, true) . "\r\n";
                        $cid = $this->complex['id'];
                        error_log($mess . "\n", 3, BASE_PATH . '/temp/logs/' . $cid . '.' . $channel . '.log');
                    }
                }
            }
            usleep(100000);
            /*if ($channel == 'ticker') {
                $this->linkControl();
            }*/
            if ($channel == 'detections') {
                /*if ($this->need_lost) {
                    //$this->getLostDetection();
                    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                        $shell = require BASE_PATH . '/config/shell.php';
                        $cid = $this->complex['id'];
                        $command = sprintf($this->shell['lost'], $cid);
                        exec($command);
                        sleep(1);
                    }
                    $this->need_lost = false;
                }*/
                if ($this->lost_control_time < (time() - $this->lost_timeout)) {
                    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                        $shell = require BASE_PATH . '/config/shell.php';
                        $cid = $this->complex['id'];
                        $command = sprintf($shell['lost'], $cid);
                        exec($command);
                    }
                    $this->lost_control_time = time();
                }
            }
        }
    }

    /*private function linkControl()
    {
        $cid = $this->complex['id'];
        $path = BASE_PATH . '/temp/proc/' . $cid . '.lost.tmp';
        $has_file = is_file($path)?true:false;
        //$tick = $this->events->getTick($cid);
        $control_time = time() - $this->tick_timeout;
        //if ($tick['time_stamp'] < $control_time) {
        if ($this->tick_time < $control_time) {
            if (!$has_file) {
                $file = $this->events->getLastDetectionFile($cid);
                if (!$file) {
                    return false;
                }
                $handle = @fopen($path, 'w');
                if (!$handle) {
                    error_log('Cant create <detlost> file', 3, BASE_PATH . '/temp/logs/' . $cid . '.lost.log');
                }
                $res = fwrite($handle, $file);
                if ($res === false) {
                    @unlink($path);
                    error_log('Cant write <detlost> file', 3, BASE_PATH . '/temp/logs/' . $cid . '.lost.log');
                }
                fclose($handle);
            }
        }
        $control_time = time() - 1;
        if ($this->tick_time >= $control_time) {
            if ($has_file) {
                $this->need_lost = true;
            }
        }
    }*/

    /*private function getLostDetection()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $shell = require BASE_PATH . '/config/shell.php';
            $cid = $this->complex['id'];
            $command = sprintf($this->shell['lost'], $cid);
            exec($command);
        }
    }*/

    private function tickerSave($data)
    {
        //$this->tick_time = time();
        $cid = $this->complex['id'];
        $this->events->saveTick($cid, $data);
    }

    private function detectionsSave($data)
    {
        $cid = $this->complex['id'];
        $url = @$data['payload']['url'];
        if (!$url) {
            return false;
        }
        $file_name = $this->getFileName($url, false);
        if (!$file_name) {
            return false;
        }
        $session = @$data['payload']['session'];
        $result['objects'] = @json_encode($data['payload']['detections']);
        $result['gps_time'] = @$data['payload']['gps']['datetime'];
        $result['latitude'] = @$data['payload']['gps']['latitude'];
        $result['longitude'] = @$data['payload']['gps']['longitude'];

        if (!$session) {
            // old format
            parse_str(parse_url($url)['query'], $tmp);
            $session = str_replace('session/', '', $tmp['dir']);
            //$result['url'] = "/stream.cgi?raw=1&det={$url['det']}&dir={$url['dir']}&file={$url['file']}";
            $result['url'] = $url;
        } else {
            $result['url'] = Helper::prepareUrl('image_detect', ['%det' => $url['det'], '%dir' => $url['dir'], '%file' => $url['file']]);
        }

        if (!isset($this->cam_modes[$session])) {
            $result['cam_mode'] = $this->system->getDepartureBySession($cid, $session)['cam_mode'];
            if (!$result['cam_mode']) {
                $result['cam_mode'] = $this->system->getDeparture($cid)['cam_mode'];
            }
            $this->cam_modes[$session] = $result['cam_mode'];
        } else {
            $result['cam_mode'] = $this->cam_modes[$session];
        }

        $tracks = [];
        foreach ($data['payload']['detections'] as $dt) {
            $tracks[] = (int) $dt['track_id'];
        }
        $tracks = array_filter($tracks);
        $tracks = array_unique($tracks);
        $result['session'] = $session;
        $result['time_marker'] = $this->getTimeMarker($file_name);
        $result['file_name'] = $file_name . '.txt';
        foreach ($tracks as $t) {
            $res = $this->events->saveDetections($cid, $result, $t);
            if ($res !== true) {
                error_log($res . "\n", 3, BASE_PATH . '/temp/logs/' . $cid . '.savedet.log');
            }
        }
        if ($this->mail_timer < $this->mail_control_time) {
            $this->mail_timer = time();
            $this->mail_sender->send();
            /*echo "send\r\n";
            ob_flush();
            flush();*/
        }
        $this->mail_control_time = time() - $this->mail_timeout;
    }

    private function alarmsSave($data)
    {
        $cid = $this->complex['id'];
        foreach ($data as $d) {
            $d['aux_data'] = json_encode($d['aux_data']);
            $this->events->saveAlarms($cid, $d);
            /*var_dump($data); echo "\r\n";
            ob_flush();
            flush();*/
        }
    }

    private function getTimeMarker($file_name)
    {
        if (!$file_name) {
            return null;
        }
        preg_match('/f_(\d+\.\d+).*/', $file_name, $matches);
        $tmp = str_replace('.', '', $matches[1]);
        return substr($tmp, 0, 17);
    }

    private function getFileName($url, $gen_name = true)
    {
        if (is_array($url) && isset($url['file'])) {
            return $url['file'];
        }
        // for old format
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $result);
        if (isset($result['file'])) {
            $file_name = $result['file'];
        } else {
            if ($gen_name) {
                $file_name = md5(microtime());
            } else {
                $file_name = false;
            }
        }
        return $file_name;
    }

    private function getFolder($cid, $session_id)
    {
        $complex_dir = BASE_PATH . '/public/detects/' . $cid;
        $image_dir = $complex_dir . '/' . $session_id;
        if (!is_dir($image_dir)) {
            $created1 = mkdir($complex_dir, 0755);
            $created2 = mkdir($image_dir, 0755);
            /*if (!$created1 || !$created2) {
                throw new AppException('Detects image dir write denied');
            }*/
        }
        if (!is_dir($image_dir)) {
            throw new AppException('Detects image dir write denied: ' . $image_dir);
        }
        return $image_dir;
    }

    private function image_loop($detect_images)
    {
        $image_port = $this->config['image_port'];
        $ctx = stream_context_create(['http' => ['timeout' => $this->config['http_timeout'], 'header'=>"Connection: close\r\n"]]);
        foreach ($detect_images as $dt) {
            $image_params = $this->imgopts->getImageParams($dt['cid']);
            if (!$image_params['thumb']['percent']) {
                continue;
            }
            $file_name = $this->getFileName($dt['url']);
            $path = $this->getFolder($dt['cid'], $dt['session_id']);

            // get thumb
            $url = 'http://' . $dt['cip'] . ':' . $image_port . $dt['url'] . '&size=' . $image_params['thumb']['percent'];
            $image_string = @file_get_contents($url, false, $ctx);
            if (!$image_string) {
                /*echo "next\r\n";
                ob_flush();
                flush();*/
                continue;
            }
            try {
                $image = ImageResize::createFromString($image_string);
                $image_string = null; unset($image_string);
                $image->gamma(false);
                //$image->quality_jpg = 100;
                /*$image->resizeToHeight(600);
                $image->crop(820, 600, true, ImageResize::CROPCENTER);
                $image->save($path . '/' . $file_name . '-full.jpg', IMAGETYPE_JPEG);
                $image->resize(410, 300);
                $image->save($path . '/' . $file_name . '-prev.jpg', IMAGETYPE_JPEG);
                $image->resize(260, 190);
                $image->crop(260, 90, true, ImageResize::CROPCENTER);
                $image->save($path . '/' . $file_name . '-thumb.jpg', IMAGETYPE_JPEG);*/
                //$image->save($path . '/' . $file_name . '-full.jpg', IMAGETYPE_JPEG);
                //$image->resize(400, 225);
                $image->quality_jpg = 85;
                $image->save($path . '/' . $file_name . '-prev.jpg', IMAGETYPE_JPEG);
                $this->events->updateDetectImage($dt['id'], $file_name);
                $image = null; unset($image);
            } catch (ImageResizeException $e) {
                $error = $e->getMessage() . ' : ' . $e->getLine() . "\r\n";
                throw new AppException($error);
                //ob_flush();
                //flush();
            }
            // get full image
            $url = 'http://' . $dt['cip'] . ':' . $image_port . $dt['url'] . '&size=' . $image_params['image']['percent'];
            $image_string = @file_get_contents($url, false, $ctx);
            if (!$image_string) {
                continue;
            }
            try {
                $image = ImageResize::createFromString($image_string);
                $image_string = null; unset($image_string);
                $image->gamma(false);
                $image->quality_jpg = 80;
                $image->save($path . '/' . $file_name . '-full.jpg', IMAGETYPE_JPEG);
                $this->events->updateDetectFullImage($dt['id']);
                $image = null; unset($image);
            } catch (ImageResizeException $e) {
                $error = $e->getMessage() . ' : ' . $e->getLine() . "\r\n";
                throw new AppException($error);
                /*echo $e->getMessage(), ' : ', $e->getLine(), "\r\n";
                ob_flush();
                flush();*/
            }
        }
    }

    public function wc2test()
    {
        echo 'ok';
        exit(0);
    }
}
