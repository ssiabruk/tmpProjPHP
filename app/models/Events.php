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


namespace App\Models;

class Events
{
    private $cs_start = 'recdet';
    private $db;
    private $alarm_time;

    public function __construct($db)
    {
        $this->db = $db;
        $this->alarm_time = 10;
    }

    public function saveTick($cid, $data)
    {
        if (!isset($data['host_time']) && !isset($data['gps_time']) && !isset($data['latitude']) && !isset($data['longitude'])) {
            return false;
        }
        $st = $this->db->prepare('DELETE FROM ticker WHERE cid = ?');
        try {
            $st->execute([$cid]);
        } catch(\PDOException $e) {
            echo $e->getMessage();
            return false;
        }
        $time = time();
        $sql = 'INSERT INTO ticker (cid, time_stamp, host_time, gps_time, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$cid, $time, $data['host_time'], $data['gps_time'], $data['latitude'], $data['longitude']]);
        } catch(\PDOException $e) {
            return false;
        }
        return true;
    }

    public function getTick($cid) {
        $st = $this->db->prepare('SELECT * FROM ticker WHERE cid = ? ORDER BY time_stamp DESC LIMIT 1');
        try {
            $st->execute([$cid]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAllTick() {
        $st = $this->db->prepare('SELECT * FROM ticker GROUP BY cid ORDER BY MAX(time_stamp)');
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    public function saveDetections($cid, $data, $track)
    {
        $time = time();
        $sql = 'INSERT INTO detections
                (cid, time_stamp, time_marker, url, detect_objects, gps_time, latitude, longitude,
                cam_mode, file_name, session_id, track_id, image, imgfull)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT DO NOTHING';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([
                $cid, $time, $data['time_marker'], $data['url'], $data['objects'], $data['gps_time'],
                $data['latitude'], $data['longitude'], $data['cam_mode'], $data['file_name'], $data['session'],
                $track, NULL, NULL
            ]);
        } catch(\PDOException $e) {
            return $e->getMessage();
            //return false;
        }
        return true;
    }

    public function getDetectionById($detect_id)
    {
        $sql = 'SELECT
                complexlist.id,
                complexlist.cid,
                complexlist.cip,
                detections.time_stamp,
                detections.url,
                detections.detect_objects,
                detections.latitude,
                detections.longitude,
                detections.cam_mode,
                detections.image,
                detections.imgfull,
                detections.session_id,
                detections.id AS dtid
                FROM detections LEFT JOIN complexlist ON
                detections.cid = complexlist.id
                WHERE detections.id = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$detect_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    /*public function getDetectionByFile($file_name)
    {
        $sql = 'SELECT
                detections.cid,
                detections.time_stamp,
                detections.url,
                detections.detect_objects,
                detections.latitude,
                detections.longitude,
                detections.cam_mode,
                detections.image,
                detections.imgfull,
                detections.session_id,
                detections.id AS dtid
                FROM detections
                WHERE detections.file_name = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$file_name]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC);
    }*/

    public function getDetectionsListBySession($session_id)
    {
        /*$sql = 'SELECT DISTINCT ON (track_id) track_id, image, id
                FROM detections WHERE session_id = ? AND image IS NOT NULL';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$session_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);*/
        $sql = 'SELECT MAX(id) AS id, track_id, max(time_stamp) AS ts
                FROM detections WHERE session_id = ? AND image IS NOT NULL GROUP BY track_id ORDER BY ts DESC';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$session_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        $res = $st->fetchAll(\PDO::FETCH_COLUMN, 0);
        if (!$res) {
            return false;
        }
        $cond = implode(',', $res);
        /*$sql = 'SELECT DISTINCT ON (track_id) track_id, image, id
                FROM detections WHERE detections.id IN (' . $cond . ')';*/
        $sql = 'SELECT track_id, image, id, latitude, longitude
                FROM detections WHERE detections.id IN (' . $cond . ')';
        $st = $this->db->prepare($sql);
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getDetections($complex_id, $limit = false, $image_only = false, $last_sess_only = true)
    {
        //$portion_time = time()-1;
        $inner_sql1 = $inner_sql2 = $inner_sql3 = $inner_sql4 = '';

        if ($last_sess_only) {
            $sql = 'SELECT session_id FROM departures
                    WHERE complex_id = ? AND status = ? ORDER BY action_timestamp DESC LIMIT 1';
            $st = $this->db->prepare($sql);
            try {
                $st->execute([$complex_id, $this->cs_start]);
            } catch(\PDOException $e) {
                //echo $e->getMessage(); die;
            }
            $session_id = $st->fetch(\PDO::FETCH_COLUMN, 0);
            $inner_sql1 = " AND session_id = '{$session_id}' ";
        }
        //var_dump($session_id); die;

        if ($limit && is_numeric($limit)) {
            $inner_sql2 = ' LIMIT ' . $limit;
        }

        /*if ($portion) {
            if ($last_sess_only) {
                $inner_sql3 = " AND ts > {$portion_time} ";
            } else {
                $inner_sql3 = " WHERE ts > {$portion_time} ";
            }
        } */

        if ($image_only) {
            $inner_sql4 = ' AND image IS NOT NULL ';
        }

        $sql = 'SELECT MAX(id) AS id, track_id, MAX(time_stamp) AS ts FROM detections WHERE 1=1 ' . $inner_sql1 . $inner_sql4;
        $sql.= 'GROUP BY track_id ORDER BY ts DESC' . $inner_sql2;
        $st = $this->db->prepare($sql);
        //var_dump($sql); die;
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
        }
        $res = $st->fetchAll(\PDO::FETCH_COLUMN, 0);
        if (!$res) {
            return false;
        }
        $cond = implode(',', $res);

        $sql = 'SELECT
                complexlist.id,
                complexlist.cid,
                complexlist.cip,
                complexlist.colour,
                detections.time_stamp,
                detections.url,
                detections.detect_objects,
                detections.latitude,
                detections.longitude,
                detections.image,
                detections.session_id,
                detections.id AS dtid
                FROM detections
                LEFT JOIN complexlist ON detections.cid = complexlist.id
                WHERE detections.id IN (' . $cond . ')';// . $inner_sql4;

        $st = $this->db->prepare($sql);
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);

        /*$inner_sql = $inner_sql2 = ' ';
        if ($image_only) {
            $inner_sql = ' WHERE detections.image IS NOT NULL ';
        }
        if ($use_track) {
            $inner_sql2 = ' DISTINCT ON (detections.track_id) track_id, ';
        }
        $sql = 'SELECT ' . $inner_sql2 . '
                complexlist.id,
                complexlist.cid,
                complexlist.cip,
                complexlist.colour,
                detections.time_stamp,
                detections.url,
                detections.detect_objects,
                detections.latitude,
                detections.longitude,
                detections.image,
                detections.session_id,
                detections.id AS dtid
                FROM detections LEFT JOIN complexlist ON
                detections.cid = complexlist.id'
                . $inner_sql .
                'ORDER BY detections.track_id DESC';
        if ($limit && is_numeric($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }*/
    }

    /*public function getLastDetectionTime()
    {
        $sql = 'SELECT MAX(time_stamp) AS ts FROM detections';
        $st = $this->db->prepare($sql);
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return false;
        }
        return $st->fetch(\PDO::FETCH_COLUMN, 0);
    }*/

    public function getLastDetectionFile($cid)
    {
        $sql = 'SELECT file_name FROM detections WHERE cid = ? ORDER BY id DESC LIMIT 1';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$cid]);
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return false;
        }
        return $st->fetch(\PDO::FETCH_COLUMN, 0);
    }

    public function getDetectsWOImage($limit = false)
    {
        /*$sql = 'SELECT detections.id, detections.cid, detections.url, detections.session_id, complexlist.cip
                FROM detections
                LEFT JOIN complexlist ON detections.cid = complexlist.id
                WHERE image IS NULL';*/ // ORDER BY detections.id DESC';
        /*$sql = 'SELECT
                DISTINCT ON (detections.track_id) track_id,
                detections.id, detections.cid, detections.url, detections.session_id, complexlist.cip
                FROM detections
                LEFT JOIN complexlist ON detections.cid = complexlist.id
                WHERE image IS NULL ORDER BY detections.track_id';
        if ($limit) {
            $sql.= ' LIMIT ' . $limit;
        }*/
        /*$sql = 'SELECT MAX(id) AS id, track_id, MAX(time_stamp) AS ts FROM detections
                WHERE image IS NULL GROUP BY track_id ORDER BY ts DESC';*/
        $sql = 'SELECT MAX(id) AS id, track_id, MAX(time_stamp) AS ts FROM detections
                WHERE image IS NULL AND track_id NOT IN (
                    SELECT track_id FROM detections WHERE image IS NOT NULL
                ) GROUP BY track_id ORDER BY ts DESC';
        if ($limit && is_numeric($limit)) {
            $sql.= ' LIMIT ' . $limit;
        }
        $st = $this->db->prepare($sql);
        $st->execute();
        $res = $st->fetchAll(\PDO::FETCH_COLUMN, 0);
        if (!$res) {
            return false;
        }
        $cond = implode(',', $res);
        $sql = 'SELECT track_id, detections.id, detections.cid, detections.url, detections.session_id, complexlist.cip
                FROM detections
                LEFT JOIN complexlist ON detections.cid = complexlist.id
                WHERE detections.id IN (' . $cond . ')';
        $st = $this->db->prepare($sql);
        $st->execute();
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updateDetectImage($detect_id, $image_name)
    {
        $st = $this->db->prepare('UPDATE detections SET image = ? WHERE id = ?');
        try {
            $st->execute([$image_name, $detect_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return true;
    }

    public function updateDetectFullImage($detect_id)
    {
        $st = $this->db->prepare('UPDATE detections SET imgfull = ? WHERE id = ?');
        try {
            $st->execute(['yes', $detect_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return false;
        }
        return true;
    }

    public function getLastDetectTimeByCid($current_complex_id)
    {
        $sql = 'SELECT time_stamp FROM detections WHERE cid = ? ORDER BY id DESC LIMIT 1';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$current_complex_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC)['time_stamp'];
    }

    public function clearDetects()
    {
        $st = $this->db->prepare('DELETE FROM detections');
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
    }

    public function saveAlarms($cid, $data)
    {
        $time = time();
        $sql = 'INSERT INTO alarms (cid, time_stamp, code, aname, message, aux_data) VALUES (?, ?, ?, ?, ?, ?)';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$cid, $time, $data['code'], $data['name'], $data['message'], $data['aux_data']]);
        } catch(\PDOException $e) {
            return false;
        }
        return true;
    }

    /*public function getAlarms() {
        $control_time = time() - 15;
        $sql = 'SELECT code FROM alarms WHERE time_stamp > ? GROUP BY code';
        $st = $this->db->prepare($sql);
        //var_dump($control_time);
        try {
            $st->execute([$control_time]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }*/

    public function getCurrentAlarms() {
        $control_time = time() - $this->alarm_time;
        /*$sql = 'SELECT
                DISTINCT alarms.code AS code,
                complexlist.id,
                complexlist.cid AS ccid,
                complexlist.colour,
                alarms.*
                FROM alarms
                LEFT JOIN complexlist ON alarms.cid = complexlist.id
                WHERE alarms.time_stamp > ? ORDER BY alarms.time_stamp DESC LIMIT 1 OFFSET 0';*/
        $sql = 'SELECT cid, code, MAX(time_stamp) AS ts FROM alarms WHERE time_stamp > ? GROUP BY cid, code';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$control_time]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        $res = $st->fetchAll(\PDO::FETCH_ASSOC);
        $cond = [];
        foreach ($res as $r) {
            $cond[] = '(code = ' . $r['code'] . ' AND time_stamp = ' . $r['ts'] . ' AND alarms.cid = ' . $r['cid'] . ')';
        }
        $cond = implode(' OR ', $cond);
        $sql = 'SELECT
                alarms.code,
                alarms.message,
                complexlist.id,
                complexlist.cid AS ccid,
                complexlist.colour
                FROM alarms
                LEFT JOIN complexlist ON alarms.cid = complexlist.id
                WHERE ' . $cond;
        $st = $this->db->prepare($sql);
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getComplexAlarms($cid)
    {
        $control_time = time() - $this->alarm_time;
        $sql = 'SELECT
                alarms.code,
                alarms.message,
                complexlist.id,
                complexlist.cid AS ccid,
                complexlist.colour
                FROM alarms
                LEFT JOIN complexlist ON alarms.cid = complexlist.id
                WHERE time_stamp > ? AND alarms.cid = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$control_time, $cid]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        $res = $st->fetchAll(\PDO::FETCH_ASSOC);
        if (!$res) {
            return false;
        }
        $result = [];
        foreach ($res as $r) {
            $result[$r['code']] = $r;
        }
        return array_values($result);
    }
}
