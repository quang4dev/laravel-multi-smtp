<?php

namespace Quang4dev\MultiSmtp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @property int $id
 * @property string $domain_name
 */
class SmtpDisallowDomains extends Model
{
    public function getTable(): string
    {
        return 'smtp_disallow_domains';
    }
}
