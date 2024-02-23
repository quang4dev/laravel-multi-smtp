<?php

namespace Quang4dev\MultiSmtp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @property int $id
 * @property string $date
 * @property int $counting
 */
class SmtpCountingEmail extends Model
{

    protected $fillable = ['date', 'counting'];
    public $timestamps = false;

    public function getTable()
    {
        return 'smtp_counting_emails';
    }
}
