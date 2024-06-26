<?php

namespace App\Models;

use CodeIgniter\Model;

class Setting extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'blog_name',
        'blog_email',
        'blog_phone',
        'blog_logo',
        'blog_favicon',
    ];
}