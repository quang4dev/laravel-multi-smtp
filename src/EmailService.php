<?php

namespace Quang4dev\MultiSmtp;


use Carbon\Carbon;
use Predis\Client;
use Quang4dev\MultiSmtp\Models\SmtpConfig;
use Quang4dev\MultiSmtp\Models\SmtpCountingEmail;

class EmailService
{
    /**
     * @return string
     * @throws \Exception
     */
    private function getMailer()
    {
        $now = Carbon::now()->format('Y-m-d');
        /** @var SmtpCountingEmail $smtpCounting */
        $smtpCounting = SmtpCountingEmail::firstOrCreate([$now]);
        $smtpCounting->counting += 1;

        $id = $smtpCounting->counting / 500;
        $id = ceil($id);
        $emailConfig = SmtpConfig::find($id);
        if ($emailConfig) {
            $config = [
                'transport'  => $emailConfig->transport,
                'host'       => $emailConfig->host,
                'port'       => $emailConfig->port,
                'encryption' => $emailConfig->encryption,
                'username'   => $emailConfig->username,
                'password'   => $emailConfig->password,
            ];

            \Config::set("mail.mailers.$emailConfig->username", $config);
            \Config::set('mail.default', $emailConfig->username);
            $smtpCounting->save();

            return $emailConfig->username;
        }

        throw new \Exception('Email config not found');
    }
}
