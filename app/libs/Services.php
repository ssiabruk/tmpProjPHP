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

use App\Models\Events;
use App\Libs\AppException;

class Services
{
    private $linux = true;
    private $shell;
    private $events;
    private $tick_timeout = 3;
    private $tick_restart_timeout = 30;
    private $detect_timeout = 10;
    private $detect_restart_timeout = 30;
    private $alarm_restart_timeout = 30;
    //private $types = ['ticks', 'detects', 'images', 'alarms'];

    public function __construct(Events $events)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //throw new AppException('For Linux only');
            $this->linux = false;
        }
        $this->shell = require BASE_PATH . '/config/shell.php';
        $this->events = $events;
    }

    /*public function globalRestart()
    {
        $cids = [];
        $pattern = BASE_PATH . '/var/tmp/*.tmp';
        $files = glob($pattern);
        foreach ($files as $f) {
            $cids[] = explode('.', $f)[0];
        }
        $cids = array_unique($cids, SORT_NUMERIC);
        foreach ($cids as $c) {
            $this->complexRestart($c);
        }
    }*/

    public function globalStart(array $ids)
    {
        if (!$this->linux) {
            return false;
        }

        $result = true;
        foreach ($ids as $i) {
            $res = $this->complexStart($i);
            if (!$res) {
                $result = false;
            }
        }
        return $result;
    }

    public function globalStop(array $ids)
    {
        if (!$this->linux) {
            return false;
        }

        foreach ($ids as $i) {
            $this->complexStop($i);
        }
        $cids = [];
        $pattern = BASE_PATH . '/temp/proc/*.tmp';
        $files = glob($pattern);
        if (!$files) {
            return true;
        }
        // file pattern example: 25.alerts.tmp -> <cid>.<type>.tmp
        foreach ($files as $f) {
            $cids[] = explode('.', $f)[0];
        }
        $cids = array_unique($cids, SORT_NUMERIC);
        foreach ($cids as $c) {
            $this->complexStop($c);
        }
        return true;
    }

    public function complexStart($cid)
    {
        if (!$this->linux) {
            return false;
        }

        $this->start($cid, 'ticks');
        //$this->start($cid, 'detects');
        //$this->start($cid, 'images');
        $this->start($cid, 'alarms');
        return true;
    }

    public function complexStop($cid)
    {
        if (!$this->linux) {
            return false;
        }

        //$this->stop($cid, 'ticks');
        //$this->stop($cid, 'detects');
        //$this->stop($cid, 'images');
        //$this->stop($cid, 'alarms');
        $servs = ['ticks', 'detects', 'images', 'alarms'];
        foreach($servs as $s) {
            while($res = $this->countProc($cid, $s)) {
                $pid = $this->parsePid($res);
                $this->kill($pid);
                $this->clearPid($cid, $s);
            }
        }
    }

    public function startTicks($cid)
    {
        if (!$this->linux) {
            return false;
        }

        $tick = $this->events->getTick($cid);
        $control_time = time() - $this->tick_timeout;
        if ($tick['time_stamp'] < $control_time) {
            $pid_data = $this->loadPid($cid, 'ticks');
            $pid_time = $pid_data['time'];
            $control_time = time() - $this->tick_restart_timeout;
            if ($pid_time < $control_time) {
                return $this->start($cid, 'ticks');
            }
        }
        return true;
    }

    public function startDetects($cid)
    {
        if (!$this->linux) {
            return false;
        }

        $detect_time = $this->events->getLastDetectTimeByCid($cid);
        $control_time = time() - $this->tick_timeout;
        if ($detect_time < $control_time) {
            $pid_data = $this->loadPid($cid, 'detects');
            $pid_time = $pid_data['time'];
            $control_time = time() - $this->detect_restart_timeout;
            if ($pid_time < $control_time) {
                $this->start($cid, 'images');
                return $this->start($cid, 'detects');
            }
        }
        return true;
    }

    public function startAlarms($cid)
    {
        if (!$this->linux) {
            return false;
        }

        $control_time = time() - $this->alarm_restart_timeout;
        $pid_time = $this->loadPid($cid, 'alarms')['time'];
        if ($pid_time < $control_time) {
            return $this->start($cid, 'alarms');
        }
        return true;
    }

    public function stopDetects($cid)
    {
        if (!$this->linux) {
            return false;
        }

        $this->stop($cid, 'images');
        return $this->stop($cid, 'detects');
    }

    public function getFinishLost($cid, $sid)
    {
        if (!$this->linux) {
            return false;
        }
        if (!$sid) {
            return false;
        }
        $command = sprintf($this->shell['stop'], $cid, $sid);
        exec($command);
    }

    private function start($cid, $type)
    {
        if (!$cid || !is_numeric($cid)) {
            return false;
        }

        $this->stop($cid, $type);
        $command = sprintf($this->shell['services'][$type], $cid);
        exec($command);
        $pid = $this->parsePid($this->countProc($cid, $type));
        if (!is_numeric($pid) || !isset($pid)) {
            while($res = $this->countProc($cid, $s)) {
                $pid = $this->parsePid($res);
                $this->kill($pid);
            }
            return false;
        }
        $res = $this->savePid($cid, $type, $pid);
        if (!$res) {
            $this->kill($pid);
            $this->clearPid($cid, $type);
            return false;
        }
        return true;
    }

    private function stop($cid, $type)
    {
        $pid_data = $this->loadPid($cid, $type);
        $tmp = array_filter($pid_data);
        if (!$tmp) {
            $result = $this->countProc($cid, $type);
        } else {
            $result = $this->countProc($pid_data['pid']);
        }
        $this->kill($this->parsePid($result));
        $this->clearPid($cid, $type);
        return true;
    }

    /*private function restart($cid, $type)
    {
        $this->stop($cid, $type);
        $this->start($cid, $type);
    }*/

    private function kill(int $pid)
    {
        if (!$pid) {
            return false;
        }

/*        $res = posix_kill($pid, SIGKILL);
        if (!$res) {
            $result = $this->countProc($pid);
            if ($result) {
                $res2 = posix_kill($pid, SIGKILL);
                if (!$res2) {
                    $result2 = $this->countProc($pid);
                    if ($result2) {
                        return false;
                    }
                }
            }
        }*/
        $command = $this->shell['kill'] . ' ' . $pid;
        exec($command);
        return true;
    }

    private function countProc($id, $type = false)
    {
        if ($type) {
            $command = $this->shell['proc'] . ' "cli.php --c ' . $type . ' --n ' . $id . '"'; // transfer to shell.php
        } else {
            $command = $this->shell['proc'] . ' ' . $id;
        }
        exec($command, $result);
        if (count($result)>1) {
            array_walk($result, function(&$item, $key){
                $tmp = strpos($item, 'grep');
                if ($tmp !== false) {
                    $item = '';
                }
            });
            $result = array_filter($result, 'strlen');
            return end($result);
        }
        return false;
    }

    private function parsePid($string)
    {
        $pid = false;
        $s = trim($string);
        preg_match('/^(\d)+(\s)/', $s, $match);
        if (!is_array($match) || !$match) {
            return false;
        }
        $res = trim($match[0]);
        if (is_numeric($res) && $res) {
            $pid = $res;
        }
        return $pid;
    }

    private function loadPid($cid, $type)
    {
        $pid = ['pid' => 0, 'time' => 0];
        $handle = @fopen(BASE_PATH . '/temp/proc/' . $cid . '.' . $type . '.tmp', 'r');
        if ($handle) {
            $pid_data = @explode(':', fgets($handle));
            if (is_array($pid_data)) {
                $pid = ['pid' => $pid_data[0], 'time' => $pid_data[1]];
            }
            fclose($handle);
        }
        return $pid;
    }

    private function savePid($cid, $type, $pid)
    {
        $path = BASE_PATH . '/temp/proc/' . $cid . '.' . $type . '.tmp';
        if (is_file($path)) {
            return false;
        }
        $handle = @fopen($path, 'w');
        if (!$handle) {
            return false;
        }
        $res = fwrite($handle, $pid . ':' . time());
        if (!$res) {
            fclose($handle);
            return false;
        }
        fclose($handle);
        return true;
    }

    private function clearPid($cid, $type)
    {
        @unlink(BASE_PATH . '/temp/proc/' . $cid . '.' . $type . '.tmp');
    }
}
