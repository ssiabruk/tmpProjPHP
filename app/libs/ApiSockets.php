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

class ApiSockets
{
    private $ip;
    private $port;
    private $secret;
    private $socket;
    private $error;
    private $commands;
    private $timeout;
    private $writelog;
    private $logname;

    /**
     * Construct API Socket
     *
     * @param string $writelog = 'on|off'
     * @param string $logname
     * @param int    $custom_timeout
     */
    public function __construct($writelog, $logname, $custom_timeout = 3)
    {
        $this->timeout = $custom_timeout;
        $this->commands = require BASE_PATH . '/config/api.php';
        /*$this->ip = $config['host'];
        $this->port = $config['port'];
        $this->secret = $config['key'];*/
        $this->error = NULL;
        $this->writelog = $writelog;
        $this->logname = $logname;
    }

    /**
     * Init new ApiSocket
     *
     * @param string $socket_config
     * @return ApiSockets
     * @throws AppException
     */
    public function socketWork($socket_config)
    {
        if (!$socket_config && count($socket_config) != 3) {
            return false;
        }
        if(count(array_filter($socket_config)) != count($socket_config)) {
            return false;
        }

        $this->ip = $socket_config['host'];
        $this->port = $socket_config['port'];
        $this->secret = $socket_config['key'];

        $result = $this->open();
        if (!$result) {
            $this->close();
            //$error = $this->getError();
            //var_dump(base64_encode($error)); die;
            //throw new AppException('Complex ' . $complex['cip'] . ' take error: ' . $error, 'error_complex_connect');
            return false;
        }
        return $this;
    }

    /**
     * Open new active socket
     *
     * @return bool
     */
    private function open()
    {
        $this->socket = @fsockopen($this->ip, $this->port, $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            $this->error = $errno . ' : ' . $errstr;
            return false;
        }
        return true;
    }

    /**
     * Close active socket
     */
    public function close()
    {
        @fclose($this->socket);
    }

    /**
     * Get ApiSocket error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Exec API command
     *
     * @param string $command
     * @param array $params
     * @param string $query_string
     * @return array | error string
     */
    public function api($command, $params = false, $query_string = false)
    {
        if (!isset($this->commands[$command])) {
            return 'err.command.notfound';
        }
        if ($params) {
            foreach ($params as $key => $val) {
                if (is_array($this->commands[$command]['params'][$key])) {
                    $has_params = in_array($val, $this->commands[$command]['params'][$key]);
                } else {
                    if ($this->commands[$command]['params'][$key] == '?*?') {
                        $has_params = true;
                    } else {
                        $has_params = ($this->commands[$command]['params'][$key] == $val);
                    }
                }
                if (!$has_params) {
                    return 'err.params.notfound';
                }
            }
        }
        $method = $this->commands[$command]['method'];
        $request_url = $this->commands[$command]['uri'];
        if ($query_string) {
            $request_url = $request_url . '/' . $query_string;
        }
        $result = $this->$method($request_url, $params);
        error_log($command.'('.$request_url.','.$params.') ='.$result, 3, BASE_PATH . '/temp/logs/' . $this->logname . '.log');
        return $result;
    }

    /**
     * GET request to complex
     *
     * @param string $uri
     * @param array $params
     * @param string $query_string
     * @return array
     */
    private function get($uri, $params)
    {
        if ($params) {
            $request_params = '';
            foreach ($params as $key => $val){
                $request_params .= urlencode($key) . '=' . urlencode($val) . '&';
            }
            $request_params = substr($request_params, 0, -1);
            $uri = $uri . '?' . $request_params;
        }
        $host = $this->ip . ':' . $this->port;
        $out = "GET $uri HTTP/1.1\r\n";
        $out .= 'Host: ' . $host . "\r\n";
        //$out .= "Content-type: application/json\r\n";
        $out .= "Accept: application/json\r\n";
        $out .= "Accept-Encoding: gzip, compress, deflate\r\n";
        $out .= "X-Requested-With: XMLHttpRequest\r\n";
        $out .= 'X-Api-Key: ' . $this->secret . "\r\n";
        $out .= "Connection: close\r\n\r\n";
        fwrite($this->socket, $out);
        $result = '';
        while (!feof($this->socket)) {
            $result .= fgets($this->socket, 128);
        }
        $res = @explode("\r\n\r\n", $result, 2);
        if (!is_array($res) || count($res) < 2) {
            return false;
        }
        list($headers, $body) = $res;
        if (strpos($headers, 'gzip') !== false) {
            $body = gzdecode($body);
        }
        if (strpos($headers, 'compress') !== false) {
            $body = gzuncompress($body);
        }
        if (strpos($headers, 'deflate') !== false) {
            $body = gzinflate($body);
        }

        if ($this->writelog == 'on') {
            $answ = $headers . "\n" . $body;
            $mess = "---request---\n" . date('d-m-Y H:i:s') . "\n" . $out . "\n" . "---response---\n" . $answ ."\n\n\n\n";
            error_log($mess, 3, BASE_PATH . '/temp/logs/' . $this->logname . '.log');
        }

        $http_status = explode(' ', explode("\r\n", $headers, 2)[0])[1];
        $body = json_decode($body, true);
        if (json_last_error()) {
            return false;
        }
        return ['response_status'=>$http_status, 'response_body'=>$body];
    }

    /**
     * POST request to complex
     *
     * @param string $uri
     * @param array $params
     * @param string $query_string
     * @return array
     */
    private function post($uri, $params)
    {
        $out = "POST $uri HTTP/1.1\r\n";
        $out .= 'Host: ' . $this->ip . ':' . $this->port . "\r\n";
        if ($params) {
            $request_params = @json_encode($params);
            if (json_last_error()) {
                return false;
            }
            $out .= 'Content-length: ' . strlen($request_params) . "\r\n";
            $out .= "Content-type: application/json\r\n";
        } else {
            //$out .= 'Content-length: 0' . "\r\n";
        }
        //$out .= "Content-type: application/json\r\n";
        $out .= "Accept: application/json\r\n";
        $out .= "X-Requested-With: XMLHttpRequest\r\n";
        $out .= 'X-Api-Key: ' . $this->secret . "\r\n";
        $out .= "Connection: close\r\n\r\n";
        if ($params) {
            $out .= $request_params . "\r\n\r\n";
        }
        fwrite($this->socket, $out);
        $result = '';
        while (!feof($this->socket)) {
            $result .= fgets($this->socket, 128);
        }
        $res = @explode("\r\n\r\n", $result, 2);
        if (!is_array($res) || count($res) < 2) {
            return false;
        }
        @list($headers, $body) = $res;
        if (strpos($headers, 'gzip') !== false) {
            $body = gzdecode($body);
        }
        if (strpos($headers, 'compress') !== false) {
            $body = gzuncompress($body);
        }
        if (strpos($headers, 'deflate') !== false) {
            $body = gzinflate($body);
        }

        if ($this->writelog == 'on') {
            $answ = $headers . "\n" . $body;
            $mess = "---request---\n" . date('d-m-Y H:i:s') . "\n" . $out . "\n" . "---response---\n" . $answ ."\n\n\n\n";
            error_log($mess, 3, BASE_PATH . '/temp/logs/' . $this->logname . '.log');
        }

        $http_status = explode(' ', explode("\r\n", $headers, 2)[0])[1];
        $body = json_decode($body, true);
        if (json_last_error()) {
            return false;
        }
        return ['response_status'=>$http_status, 'response_body'=>$body];
    }
}
