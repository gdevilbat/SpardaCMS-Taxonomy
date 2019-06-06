<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities;

use Illuminate\Database\Eloquent\Model;

class TermMeta extends Model
{
    protected $fillable = [];
    protected $table = 'termmeta';
    protected $casts = [
        'meta_value' => 'array',
    ];
}
