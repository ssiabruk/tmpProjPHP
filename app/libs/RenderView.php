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

class RenderView
{
    private $site_url;
    private $path;
    private $folder;
    private $ext;
    private $templ = [];
    private $lang_labes = [];

    public function __construct($settings=null)
    {
        $this->site_url = $settings['site_url'];
        $this->path = $settings['template_path'];
        $this->templ['data'] = null;    // page data
        $this->templ['header'] = null;  // header template
        $this->templ['menu'] = null;  // menu template
        $this->templ['footer'] = null;  // footer template
        $this->templ['css'] = null;     // css files to append
        $this->templ['js'] = null;      // js files to append
        $this->templ['jsurls'] = null;  // js script action urls
    }

    public function setLayout(string $layout): void
    {
        $layout_path = $this->path . '/layouts/' . $layout;
        if (!is_dir($layout_path)) {
            throw new AppException('Layout template folder not found');
        }
        $header_file = $layout_path . '/header.php';
        $menu_file = $layout_path . '/menu.php';
        $footer_file = $layout_path . '/footer.php';
        if (!is_file($header_file) || !is_file($menu_file) || !is_file($footer_file)) {
            throw new AppException('Layout template files not found');
        }
        $this->templ['header'] = $header_file;
        $this->templ['menu'] = $menu_file;
        $this->templ['footer'] = $footer_file;
    }

    public function setLangLabes($labels, $lang): void
    {
        $this->lang_labes = array_merge($this->lang_labes, $labels);
        $this->lang_labes['lang'] = $lang;
    }

    public function setVar($key, $var, $use_bool = false) // set part of page data
    {
        if (!$use_bool) {
            if ($key && $var) {
                $this->templ['data'][$key] = $var;
            }
        } else {
            if ($key && (is_bool($var) || $var)) {
                $this->templ['data'][$key] = $var;
            }
        }
    }

    public function setJsUrl($name, $url) // set one of actions for ajax
    {
        if ($name && $url) {
            $this->templ['jsurls'][$name] = $url;
        }
    }

    public function setCss($css_file, $custom = false) // set one of cssfile to include
    {
        if ($css_file) {
            if ($custom) {
                $this->templ['css'][] = [$css_file];
            } else {
                $this->templ['css'][] = $css_file;
            }
        }
    }

    public function setJsFile($js_file, $custom = false) // set one of jsfile to include
    {
        if ($js_file) {
            if ($custom) {
                $this->templ['js'][] = [$js_file];
            } else {
                $this->templ['js'][] = $js_file;
            }
        }
    }

    public function putCss() // include css filelist to page
    {
        if ($this->templ['css'] && is_array($this->templ['css'])) {
            foreach ($this->templ['css'] as $css) {
                echo PHP_EOL;
                echo "\t";
                $css_url = is_array($css)?$this->site_url . $css[0]:$this->site_url . '/css/' . $css;
                echo '<link rel="stylesheet" href="', $css_url, '.css" type="text/css" />';
            }
        }
        echo PHP_EOL;
    }

    public function putJsUrls() // include js actions
    {
        if ($this->templ['jsurls'] && is_array($this->templ['jsurls'])) {
            echo PHP_EOL;
            echo "\t<script type=\"text/javascript\">";
            echo PHP_EOL;
            foreach ($this->templ['jsurls'] as $key=>$val) {
                echo "\t\t";
                echo 'var ', $key, ' = \'', $val, '\';';
                echo PHP_EOL;
            }
            echo "\t</script>";
        }
        echo PHP_EOL;
    }

    public function putJsFiles() // include js filelist to page
    {
        if ($this->templ['js'] && is_array($this->templ['js'])) {
            foreach ($this->templ['js'] as $js) {
                echo PHP_EOL;
                echo "\t";
                $js_url = is_array($js)?$this->site_url . $js[0]:$this->site_url . '/js/' . $js;
                echo '<script type="text/javascript" src="', $js_url, '.js" /></script>';
            }
        }
        echo PHP_EOL;
    }

    public function render($template, $return = false, $use_header = true): ?string
    {
        $template_file = $this->path . '/' . $template . '.php';
        if (!is_file($template_file)) {
            throw new AppException('Template file not found: ' . $template_file);
        }

        $site_url = $this->site_url;

        if ($this->templ['data']) {
            extract($this->templ['data']);
        }
        if ($this->lang_labes) {
            $l = $this->lang_labes;
        }
        if (!isset($title)) {
            $title = 'POSHUK electron-optical complex';
        }

        ob_start();
        if ($use_header) {
            include($this->templ['header']);
            echo PHP_EOL;
            include($this->templ['menu']);
            echo PHP_EOL, PHP_EOL;
        }

        include($template_file);

        if ($use_header) {
            echo PHP_EOL, PHP_EOL;
            include($this->templ['footer']);
        }
        $output = ob_get_clean();

        if ($return) {
            return $output;
        }
        echo $output;
        return null;
    }
}
