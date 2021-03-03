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

use Slim\Container;
use App\Models\Settings;
use App\Libs\Languages;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    private $di;
    private $mailer;
    private $settings;
    private $client_id;
    private $lang_labels;

    public function __construct(Container $di)
    {
        $this->di = $di;
        $this->settings = new Settings($this->di->get('db'));
        $this->mailer = new PHPMailer();
        $this->mailer->isSMTP();
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'utf-8';
        //$this->mailer->SMTPDebug = 2;

        $config = $this->di->get('configs');

        if ($config['mail']['auth']) {
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $config['mail']['user'];
            $this->mailer->Password = $config['mail']['passwd'];
        } else {
            $this->mailer->SMTPAuth = false;
        }
        if ($config['mail']['secure']) {
            $this->mailer->SMTPSecure = 'tls';
            $this->mailer->SMTPAutoTLS = true;
        } else {
            $this->mailer->SMTPSecure = false;
            $this->mailer->SMTPAutoTLS = false;
        }

        $this->mailer->Host = $config['mail']['host'];
        $this->mailer->Port = $config['mail']['port'];
        $this->mailer->setFrom($config['mail']['from'], $config['mail']['nickname']);
        $this->mailer->addReplyTo($config['mail']['from'], $config['mail']['nickname']);
        $this->client_id = $config['client_id'];

        $this->loadLangLabels();
    }

    /*public function setConfig($config)
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $config['user'];
        $this->mailer->Password = $config['passwd'];
        $this->mailer->Port = $config['port'];
        $this->mailer->setFrom($config['from'], $config['nickname']);
    }*/

    public function send()
    {
        $current_time = time();
        $block_time = $this->settings->getNotifyBlockingTime();
        if ($block_time > $current_time) {
            return false;
        }
        $recipients = $this->settings->getContacts('emails');
        if (!$recipients) {
            return false;
        }
        foreach ($recipients as $r) {
            $this->mailer->addAddress($r['contact']);
        }
        $this->mailer->Subject = $this->lang_labels['notify_subj'];
        $body = $this->lang_labels['notify_body'] . '<br /><br />' . $this->client_id;
        $this->mailer->msgHTML($body);
        $res = $this->mailer->send();
        if(!$res) {
            $error = $this->mailer->ErrorInfo . "\r\n";
            error_log($error, 3, BASE_PATH . '/temp/logs/email-errors.log');
            return false;
        }
        return true;
    }

    private function loadLangLabels()
    {
        $mail_lang = $this->settings->getNotifyLang();
        $lang = new Languages($mail_lang);
        $this->lang_labels = $lang->loadLangLabels('emails');
    }
}