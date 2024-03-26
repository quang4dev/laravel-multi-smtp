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
 * @property int $counting
 * @property int $quota
 */
class EmailService
{
    public $host;
    public $from;
    public $username;
    public $quota;

    private $smtpCounting;

    /**
     * @return $this|null
     */
    public function getMailer()
    {
        $now = Carbon::now()->format('Y-m-d');
        $this->smtpCounting = SmtpCountingEmail::firstOrCreate(['date' => $now], ['counting' => 0]);

        $emailConfig = $this->findSmtpConfig($this->smtpCounting->counting + 1);
        if ($emailConfig) {

            \Config::set("mail.mailers.$emailConfig->username", [
                'transport'  => $emailConfig->transport,
                'host'       => $emailConfig->host,
                'port'       => $emailConfig->port,
                'encryption' => $emailConfig->encryption,
                'username'   => $emailConfig->username,
                'password'   => $emailConfig->password,
            ]);
            \Config::set('mail.default', $emailConfig->username);

            \Config::set("mail.from", [
                'address' => $emailConfig->from,
                'name' => $emailConfig->from_name,
            ]);

            $this->username = $emailConfig->username;
            $this->from = $emailConfig->from;
            $this->host = $emailConfig->host;
            $this->counting = $this->smtpCounting->counting;
            $this->quota = $this->totalQuota();

            return $this;
        }

        \Log::error('Error: SMTP config not found!');
        return null;
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

    /**
     * @return float|int
     */
    public function totalQuota()
    {
        $smtpQuotas = SmtpConfig::get()->pluck('quota')->toArray();
        return array_sum($smtpQuotas);
    }

    private function findSmtpConfig($param) {
        $smtpQuotas = SmtpConfig::get()->pluck('quota')->toArray();
        $total = array_sum($smtpQuotas);
        $currentIndex = 0;
        $runningTotal = 0;

        if ($param > $total) {
            \Log::error('Error: SMTP exceeds limited');
            return null;
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
