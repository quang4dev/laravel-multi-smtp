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
    public $mailerPath = '';

    /** @var SmtpCountingEmail */
    private $smtpCounting;
    private $defaultByUsername;

    public function __construct($mailerPath = 'mailers', $defaultByUsername = true)
    {
        $this->mailerPath = $mailerPath;
        $this->defaultByUsername = false;
    }

    /**
     * @return $this|null
     */
    public function getMailer()
    {
        $now = Carbon::now()->format('Y-m-d');
        $this->smtpCounting = SmtpCountingEmail::firstOrCreate(['date' => $now], ['counting' => 0]);
        $this->smtpCounting->increment('counting', 1);

        $emailConfig = $this->findSmtpConfig($this->smtpCounting->counting);
        if ($emailConfig) {

            $mailerFullPath = implode('.', array_filter([
                'mail',
                $this->mailerPath,
                $this->defaultByUsername ? $emailConfig->username : ''
            ], 'strlen'));

            \Config::set("$mailerFullPath.driver", $emailConfig->transport);
            \Config::set("$mailerFullPath.transport", $emailConfig->transport);
            \Config::set("$mailerFullPath.host", $emailConfig->host);
            \Config::set("$mailerFullPath.port", $emailConfig->port);
            \Config::set("$mailerFullPath.encryption", $emailConfig->encryption);
            \Config::set("$mailerFullPath.username", $emailConfig->username);
            \Config::set("$mailerFullPath.password", $emailConfig->password);

            if ($this->defaultByUsername) {
                \Config::set('mail.default', $emailConfig->username);
            }

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
        $this->smtpCounting->increment('counting', 1);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function counDown()
    {
        $this->smtpCounting->decrement('counting', 1);
    }

    /**
     * @return float|int
     */
    public function totalQuota()
    {
        $smtpQuotas = SmtpConfig::get()->pluck('quota')->toArray();
        return array_sum($smtpQuotas);
    }

    private function findSmtpConfig($counting) {
        $smtpQuotas = SmtpConfig::get()->pluck('quota')->toArray();
        $total = array_sum($smtpQuotas);
        $currentIndex = 0;
        $runningTotal = 0;

        if ($counting > $total) {
            \Log::error('Error: SMTP exceeds limited');
            return null;
        }

        foreach ($smtpQuotas as $index => $value) {
            $runningTotal += $value;
            if ($counting <= $runningTotal) {
                $currentIndex = $index;
                break;
            }
        }

        return SmtpConfig::find($currentIndex + 1);
    }

}
