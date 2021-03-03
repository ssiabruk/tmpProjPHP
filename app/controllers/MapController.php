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


namespace App\Controllers;

use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use App\Controllers\Controller;
use App\Models\Events;
use App\Models\Settings;
use App\Models\Storage;
use App\Models\System;
use App\Libs\AppException;
use App\Libs\Helper;
use App\Libs\Services;
use App\Libs\ImageOptions;

class MapController extends Controller
{
    //private $user;
    private $events;
    private $imgopts;

    public function init()
    {
        //$this->view = $this->di->get('view');
        //$this->user = $this->sessions->get('user');
        //$this->view->setVar('user', $this->user);
        $this->events = new Events($this->db);
        $this->view->setVar('active_menu_item', 'map');
    }

    public function index($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('map');
        $this->view->setVar('title', $lang_labels['map_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $this->view->setJsUrl('actionMap', $this->site_url . '/map/works');
        $this->view->setJsUrl('actionMapRefresh', $this->site_url . '/map/refresh');
        $this->view->setJsUrl('actionLastDetects', $this->site_url . '/map/detects');
        $this->view->setJsUrl('actionCheck', $this->site_url . '/system/streamcheck');
        $this->view->setJsUrl('actionNotifyOff', $this->site_url . '/map/notifyoff');
        $this->view->setJsUrl('departClient', $this->site_url . '/map/departed');

        $settings = new Settings($this->db);
        $complex_list = $settings->getListComplex(true);
        $this->view->setVar('clist', $complex_list);
        $this->setActiveComplex($complex_list);

        $active_complex = $this->sessions->get('active_cid');
        $this->view->setVar('active_complex', $active_complex);
        /*$has_detection = $this->events->getDetections($active_complex, 10, true, true)?true:false;
        $this->view->setVar('has_detection', $has_detection);*/

        $map_url = $this->di->get('configs')['map_url'];
        $this->view->setJsUrl('mapUrl', $map_url);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');
        $this->view->setCss('colors');

        $this->view->setCss('/map/leaflet', true);
        $this->view->setJsFile('/map/leaflet', true);
        $this->view->setCss('/map/fullscreen/Control.FullScreen', true);
        $this->view->setJsFile('/map/fullscreen/Control.FullScreen', true);
        $this->view->setJsFile('/map/leaflet.textpath', true);
        //$this->view->setCss('/map/MarkerCluster', true);
        //$this->view->setCss('/map/MarkerCluster.Default', true);
        //$this->view->setJsFile('/map/leaflet.markercluster', true);

        $this->view->setJsFile('cabinet/mapconf');
        $this->view->setJsFile('cabinet/map');
        $this->view->setJsFile('cabinet/detects');
        $this->view->setJsFile('cabinet/video');
        $this->view->setCss('cabinet/map');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/map');
    }

    public function departed($request, $response)
    {
        $active_complex_id = $this->sessions->get('active_cid');
        if (!$active_complex_id) {
            return $this->prnJson(null, null, $response);
        }
        $settings = new Settings($this->db);
        $complex = $settings->getComplexByID($active_complex_id);
        if (!$complex) {
            return $this->prnJson(null, null, $response);
        }
        (new Services($this->events))->startTicks($active_complex_id);
        $tmp = $this->events->getTick($active_complex_id);
        $result = [
            'cid' => $active_complex_id,
            'cc' =>  $complex['colour'],
            'lat' => $tmp['latitude'],
            'lon' => $tmp['longitude']
        ];
        $result = json_encode($result);
        return $this->prnJson($result, 'success', $response);
    }

    public function mapworks($request, $response)
    {
        $active_complex = $this->sessions->get('active_cid');
        $detections = $this->events->getDetections($active_complex, 100, true);
        if (!$detections) {
            //$lang_labels = $this->lang->loadLangLabels('map');
            //return $this->prnJson($lang_labels['no_detections'], 'error', $response);
            return json_encode([]);
        }
        $detections = array_reverse($detections);
        $result = [];
        $logged_in = (isset($this->user['login']) && $this->user['login']);
        foreach ($detections as $d) {
            $popup = '';
            if ($d['image']) {
                $popup .= '<img src="'.$this->site_url.'/detects/'.$d['id'].'/'.$d['session_id'].'/'.$d['image'].'-prev.jpg" /><br />';
            }
            if ($logged_in) {
                $popup.= '<a href="' . $this->site_url . '/map/viewdetect/' . $d['dtid'] . '" target="_blank">';
            } else {
                $popup.= '<a href="' . $this->site_url . '/main/detect/' . $d['dtid'] . '" target="_blank">';
            }
            $popup.= $d['cid'] . '<br />' . date('d/m/Y H:i:s', $d['time_stamp']) . '</a>';
            $result[] = [
                'dtid' => $d['dtid'],
                //'id' => $d['id'],
                //'cid' => $d['cid'],
                'cc' => $d['colour'],
                //'ts' => $d['timestamp'],
                //'time' => date('d-m-Y H:i:s', $d['timestamp']),
                //'url' => $d['url'],
                //'objects' => $d['detect_objects'],
                'lat' => $d['latitude'],
                'lon' => $d['longitude'],
                'popup' => $popup
            ];
        }
        $result = json_encode($result);
        return $this->prnJson($result, 'success', $response);
    }

    public function detects($request, $response)
    {
        $output = '';
        $active_complex_id = $this->sessions->get('active_cid');
        $detections = $this->events->getDetections($active_complex_id, 5, true);
        $was_departed = (new System($this->db))->getDeparture($active_complex_id, true)['status'];
        if ($was_departed === 'recdet') {
            $services = (new Services($this->events))->startDetects($active_complex_id);
            /*if (!$services) {
                $output = 'Services failure';
            }*/
        }
        if (isset($this->user['login']) && $this->user['login']) {
            $path = 'map/viewdetect';
            $target = '';
        } else {
            $path = 'main/detect';
            $target = ' target="_blank"';
        }
        if ($detections) {
            foreach ($detections as $dt) {
            $img = $dt['image']?'detects/'.$dt['id'].'/'.$dt['session_id'].'/'.$dt['image'].'-prev.jpg':'images/noimageyet.png';
            $output.= <<<EOT
                    <div class="detect-area">
                        <div class="detect-desc">
                            <a href="{$this->site_url}/{$path}/{$dt['dtid']}"{$target}>{$dt['cid']}</a>
                        </div>
                        <div class="detect-img">
                            <a href="#" class="detect-image" data-lat="{$dt['latitude']}" data-lon="{$dt['longitude']}">
                                <img src="{$this->site_url}/{$img}" class="img-fluid" />
                            </a>
                        </div>
                    </div>
EOT;
            }
        } else {
            if (!$output) {
                $lang_labels = $this->lang->loadLangLabels('map');
                $output = '<h4>' . $lang_labels['no_detections'] . '</h4>';
            }
        }
        return $this->prnJson($output, 'success', $response);
    }

    public function viewDetect($request, $response)
    {
        $detect_id = $request->getAttribute('detect');
        if (!$detect_id || !is_numeric($detect_id)) {
            exit();
        }
        $lang_labels = $this->lang->loadLangLabels('map');
        $this->view->setVar('title', $lang_labels['map_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $this->view->setJsUrl('actionGetImage', $this->site_url . '/map/getimage');

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);

        $detection = $this->events->getDetectionById($detect_id);
        if (!$detection) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('Page not found');
        }
        $this->view->setVar('dtid', $detect_id);
        if ($detection['imgfull']) {
            $detection['image'] = $this->site_url.'/detects/'.$detection['id'].'/'.$detection['session_id'].'/'.$detection['image'].'-full.jpg"';
        } else {
            $detection['image'] = $this->site_url . '/images/noimageyet.png';
        }

        $this->view->setCss('jquery.guillotine');
        $this->view->setJsFile('jquery.guillotine');

        $this->view->setVar('detect', $detection);
        $this->view->setCss('/map/leaflet', true);
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');
        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        //$this->view->setJsFile('/map/leaflet', true);
        $this->view->setJsFile('cabinet/getimage');

        //$this->view->setJsFile('cabinet/map');
        //$this->view->setJsFile('cabinet/detects');
        $this->view->setCss('cabinet/map');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/mapviewd');
    }

    public function getImage($request, $response)
    {
        $allowed_types = ['local', 'complex', 'remote'];
        $req_data = $request->getParsedBody();
        $detect_id = Helper::xss($req_data['dtid']);
        $storage_type = Helper::xss($req_data['stype']);
        if (!is_numeric($detect_id) || !$detect_id) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        if (!in_array($storage_type, $allowed_types)) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        switch ($storage_type) {
            case 'local': $detection = $this->events->getDetectionById($detect_id);
                break;
            case 'complex':
                    $storage = new Storage($this->db);
                    $detection = $storage->getComplexTrackById($detect_id);
                    $settings = new Settings($this->db);
                    $complex = $settings->getComplexByID($detection['cid']);
                    $detection['cip'] = $complex['cip'];
                break;
            default: $detection = false;
        }
        if (!$detection) {
            //throw new AppException('Detection not found');
            return $this->prnJson('Detection not found', 'error', $response);
        }
        if (!@$detection['url']) {
            $lang_map = $this->lang->loadLangLabels('map');
            return $this->prnJson($lang_map['image_notfound'], 'error', $response);
        };
        $data = $this->imageWork($detection, $storage_type);
        if (!$data) {
            $lang_map = $this->lang->loadLangLabels('map');
            return $this->prnJson($lang_map['image_notfound'], 'error', $response);
        }
        return $response->withJson($data);
    }

    public function refresh($request, $response)
    {
        $current_complex_id = $this->sessions->get('active_cid');
        $last_detect_time = $this->events->getLastDetectTimeByCid($current_complex_id);
        if (!$last_detect_time) {
            return $response->withJson([1]);
        }
        $current_time = time();
        $period = $current_time - $last_detect_time;
        if ($period > 3) {
            return $response->withJson([2]);
        }
        return $response->withJson(['result' => 'success']);
    }

    public function notifyOff($request, $response)
    {
        $post_data = $request->getParsedBody();
        $timeoff = Helper::xss($post_data['timeoff']);
        if (!is_numeric($timeoff)) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        if ($timeoff < 1 || $timeoff > 50) {
            $timeoff = 30;
        }
        $settings = new Settings($this->db);
        $timeoff_ts = $timeoff * 60 + time();
        $settings->setNotifyBlockingTime($timeoff_ts);
        $lang_settings = $this->lang->loadLangLabels('settings');
        $message = str_replace('%', $timeoff, $lang_settings['notify_off_time']);
        return $this->prnJson($message, 'success', $response);
    }

    private function imageWork($dt, $storage_type)
    {
        $data = false;
        if ($dt['imgfull']) {
            $res = $this->getExistImage($dt, $storage_type);
            if ($res) {
                return $res;
            }
        }
        $this->imgopts = new ImageOptions();
        $image_params = $this->imgopts->getImageParamsByMode($dt['cam_mode']);
        $image_port = $this->di->get('configs')['image_port'];
        $ctx = stream_context_create(['http' => ['timeout' => $this->di->get('configs')['http_timeout'], 'header'=>"Connection: close\r\n"]]);
        $url = 'http://' . $dt['cip'] . ':' . $image_port . $dt['url'] . '&size=' . $image_params['image']['percent'];
        $image_string = @file_get_contents($url, false, $ctx);
        if (!$image_string) {
            return false;
        }
        $query = parse_url($dt['url'], PHP_URL_QUERY);
        parse_str($query, $result);
        $file_name = $result['file']??md5(microtime());
        try {
            $image = ImageResize::createFromString($image_string);
            $image_string = null; unset($image_string);
            $image->gamma(false);
            $image->quality_jpg = 90;
            $image->resizeToHeight($image_params['image']['sizes']['height']);
            // uncomment, when get real image size coefficients
            //$image->crop($image_params['image']['sizes']['width'], $image_params['image']['sizes']['height'], true, ImageResize::CROPCENTER);
            if ($storage_type == 'local') {
                $path = $this->makeImagesPath($dt, 'local');
                if (!$path) {
                    return false;
                }
                $image->save($path . '/' . $file_name . '-full.jpg', IMAGETYPE_JPEG);
                $this->events->updateDetectFullImage($dt['dtid']);
                $data = [
                    'url' => $this->site_url.'/detects/'.$dt['id'].'/'.$dt['session_id'].'/'.$dt['image'].'-full.jpg',
                    'result' => 'success'
                ];
            }
            if ($storage_type == 'complex') {
                $path_cache = $this->makeImagesPath($dt, 'complex');
                if (!$path_cache) {
                    return false;
                }
                $image->save($path_cache . '/' . $dt['imgfull'] . '.jpg', IMAGETYPE_JPEG);
                $data = [
                    'url' => $this->site_url.'/detects/'.$dt['cid'].'/'.$dt['session_id'].'/cache/'.$dt['imgfull'].'.jpg',
                    'result' => 'success'
                ];
            }
            $image = null; unset($image);
        } catch (ImageResizeException $e) {
            // echo $e->getMessage(); die;
            return false;
        }
        return $data;
    }

    private function getExistImage($dt, $storage_type)
    {
        if ($storage_type == 'local') {
            $data = [
                'url' => $this->site_url.'/detects/'.$dt['id'].'/'.$dt['session_id'].'/'.$dt['image'].'-full.jpg',
                'result' => 'success'
            ];
            return $data;
        }
        if ($storage_type == 'complex') {
            $path = BASE_PATH . '/public/detects/' . $dt['cid'] . '/' . $dt['session_id'] . '/cache/';
            $has_image = is_file($path . $dt['imgfull'] . '.jpg');
            if ($has_image) {
                $data = [
                    'url' => $this->site_url.'/detects/'.$dt['cid'].'/'.$dt['session_id'].'/cache/'.$dt['imgfull'].'.jpg',
                    'result' => 'success'
                ];
                return $data;
            }
        }
        return false;
    }

    private function makeImagesPath($dt, $storage_type)
    {
        if ($storage_type == 'local') {
            $path = BASE_PATH . '/public/detects/' . $dt['id'] . '/' . $dt['session_id'];
            if (!is_dir($path)) {
                return false;
            }
            return $path;
        }
        if ($storage_type == 'complex') {
            $path_cache = BASE_PATH . '/public/detects/' . $dt['cid'] . '/' . $dt['session_id'] . '/cache/';
            if (!is_dir($path_cache)) {
                $path_session = BASE_PATH . '/public/detects/' . $dt['cid'] . '/' . $dt['session_id'];
                if (!is_dir($path_session)) {
                    $path_complex = BASE_PATH . '/public/detects/' . $dt['cid'];
                    if (!is_dir($path_complex)) {
                        mkdir($path_complex, 0755);
                    }
                    mkdir($path_session, 0755);
                }
                mkdir($path_cache, 0755);
            }
            if (!is_dir($path_cache)) {
                return false;
            }
            return $path_cache;
        }
        return false;
    }

    private function setActiveComplex($complex_list)
    {
        $current_complex = $this->sessions->get('active_cid');
        if (!$current_complex) {
            $current_complex = $complex_list[0]['id'];
            $this->sessions->set('active_cid', $current_complex);
        }
    }

    /*public function clear($request, $response)
    {
        $this->events->clearDetects();
        return $response->withJson(['result' => 'success']);
    }*/
}
