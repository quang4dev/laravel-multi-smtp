<?php

namespace Quang4dev\MultiSmtp;


use Carbon\Carbon;
use Quang4dev\MultiSmtp\Models\SmtpConfig;
use Quang4dev\MultiSmtp\Models\SmtpCountingEmail;

/**
 * Class EmailService
 *
 * @package Quang4dev\MultiSmtp
 *
 * @property string $host
 * @property string $from
 * @property string $username
 * @property string $counting
 */
class EmailService
{
    public $host;
    public $from;
    public $username;

    private $smtpCounting;

    /**
     * @return EmailService
     * @throws \Exception
     */
    public function getMailer()
    {
        $now = Carbon::now()->format('Y-m-d');
        $this->smtpCounting = SmtpCountingEmail::firstOrCreate(['date' => $now], ['counting' => 0]);

        $emailConfig = $this->findSmtpConfig($this->smtpCounting->counting + 1);
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

            $this->username = $emailConfig->username;
            $this->from = $emailConfig->from;
            $this->host = $emailConfig->host;
            $this->counting = $this->smtpCounting->counting;
            return $this;
        }

        throw new \Exception('Email config not found');
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function countUp()
    {
        $this->smtpCounting->counting += 1;
        $this->smtpCounting->save();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function counDown()
    {
        $this->smtpCounting->counting -= $this->smtpCounting->counting ?? 1;
        $this->smtpCounting->save();
    }

    private function findSmtpConfig($param) {
        $smtpQuotas = SmtpConfig::get()->pluck('quota')->toArray();
        $total = array_sum($smtpQuotas);
        $currentIndex = 0;
        $runningTotal = 0;

        if ($param > $total) {
            return "Error: Parameter exceeds total";
        }

        foreach ($smtpQuotas as $index => $value) {
            $runningTotal += $value;
            if ($param <= $runningTotal) {
                $currentIndex = $index;
                break;
            }
        }

        return SmtpConfig::find($currentIndex + 1);
    }

}
