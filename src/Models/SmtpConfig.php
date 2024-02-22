<?php

namespace Quang4dev\MultiSmtp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @property int $id
 * @property string $transport
 * @property string $host
 * @property string $port
 * @property string $encryption
 * @property string $username
 * @property string $password
 */
class SmtpConfig extends Model
{
    public function getTable(): string
    {
        return 'smtp_configs`';
    }
}
