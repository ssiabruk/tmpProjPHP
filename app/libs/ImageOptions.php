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


namespace App\Libs;

use App\Models\System;

class ImageOptions
{
    private $image_modes;
    private $departures; // complexes in sky now

    public function __construct($departures = false)
    {
        $this->image_modes = require BASE_PATH . '/config/images.php';
        $this->departures = $departures;
    }

    public function getImageParams($cid)
    {
        $result = [];
        $mode = $this->departures[$cid]['cam_mode'];
        $result['thumb']['percent'] = $this->image_modes['images_persents'][$mode]['thumb']??16;
        $result['thumb']['sizes'] = $this->image_modes['images_resizes'][$mode]['thumb']??['width' => 260,'height' => 190];
        $result['image']['percent'] = $this->image_modes['images_persents'][$mode]['image']??50;
        $result['image']['sizes'] = $this->image_modes['images_resizes'][$mode]['image']??['width' => 820,'height' => 600];
        return $result;
    }

    public function getImageParamsByMode($mode)
    {
        $result = [];
        $result['thumb']['percent'] = $this->image_modes['images_persents'][$mode]['thumb']??16;
        $result['thumb']['sizes'] = $this->image_modes['images_resizes'][$mode]['thumb']??['width' => 260,'height' => 190];
        $result['image']['percent'] = $this->image_modes['images_persents'][$mode]['image']??50;
        $result['image']['sizes'] = $this->image_modes['images_resizes'][$mode]['image']??['width' => 820,'height' => 600];
        return $result;
    }
}
