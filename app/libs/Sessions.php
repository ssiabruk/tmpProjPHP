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

use App\Libs\PGSessions;

class Sessions
{
    private $settings;

    public function __construct($db, $settings)
    {
        // initial session settings
        $this->settings = $settings;
        $sessions_handler = new PGSessions($db);
        session_set_save_handler($sessions_handler, true);

        $name = 'wc2session';
        session_name($name);
        session_start();
        //session_regenerate_id(true);
        $this->start();
    }

    private function start()
    {
        $is_lang_set = $this->get('lang');
        if (!$is_lang_set) {
            $this->set('lang', $this->settings['default_lang']);
        }
    }

    /*public function start()
    {
        //session_set_cookie_params($this->settings['session_lifetime'], '/', $_SERVER['HTTP_HOST'], false, true);
        session_start();
        $time = time() + $this->settings['session_lifetime'];
        setcookie(session_name(), session_id(), $time, '/');
        $is_lang_set = $this->get('lang');
        if (!$is_lang_set) {
            $this->set('lang', $this->settings['default_lang']);
        }
    }*/

    public function get(string $key)
    {
        return $_SESSION[$key]??false;
    }

    public function set(string $key, $val)
    {
        $_SESSION[$key] = $val;
    }

    public function forget(string $key)
    {
        if (isset($_SESSION[$key])) {
            $_SESSION[$key] = null;
            unset($_SESSION[$key]);
        }
    }

    public function fix()
    {
        //session_write_close();
        return true;
    }

    public function clear()
    {
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(),'',0,'/');
    }
}
