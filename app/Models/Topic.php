<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = ['nid', 'title', 'alias', 'sku', 'description', 'slug', 'status'];
}
