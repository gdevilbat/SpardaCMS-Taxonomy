<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities;

use Illuminate\Database\Eloquent\Model;

class TermRelationships extends Model
{
    protected $fillable = [];
    protected $table = 'term_relationships';
    protected $primaryKey = 'id_term_relationships';
}
