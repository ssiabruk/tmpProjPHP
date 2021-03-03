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

use App\Libs\AppException;

class Languages
{
    private $langs_list = ['uk', 'en', 'ru'];
    private $current_lang;

    public function __construct($current_lang)
    {
        $this->current_lang = $current_lang;
    }

    public function loadLangLabels($lang_file): array
    {
        $lang_path_file = BASE_PATH . '/app/langs/' . $this->current_lang . '/' . $lang_file . '.php';
        if (!is_file($lang_path_file)) {
            throw new AppException('File ' . $lang_file . ' for ' . $this->current_lang . ' lang not found');
        }
        include($lang_path_file);
        return $l;
    }

    public function getCurrentLangCode()
    {
        return $this->current_lang;
    }

    public function hasLang($lang_code)
    {
        return in_array($lang_code, $this->langs_list);
    }
}