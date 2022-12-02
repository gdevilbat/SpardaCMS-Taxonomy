<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities;

use Illuminate\Database\Eloquent\Model;

use Str;

class Terms extends Model
{
    protected $fillable = [];
    protected $table = 'terms';
    protected $primaryKey = 'id_terms';

    protected $appends = [
        'primary_key',
        'encrypted_id',
    ];

    /**
     * Set the user's Slug.
     *
     * @param  string  $value
     * @return void
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Str::slug($value, '-');
    }

    public function group()
    {
    	return $this->belongsTo('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms', 'term_group');
    }

    public function taxonomies()
    {
        return $this->hasMany('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy', 'term_id');
    }

    public function termMeta()
    {
        return $this->hasMany('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermMeta', 'term_id');
    }

    public function getPrimaryKeyAttribute()
    {
        return $this->getPrimaryKey();
    }

    public function getEncryptedIdAttribute()
    {
        return encrypt($this->getKey());
    }

    final static function getTableName()
    {
        return with(new Static)->getTable();
    }

    final static function getTableWithPrefix()
    {
        return with(new Static)->getConnection()->getTablePrefix().with(new Static)->getTable();
    }

    final static function getPrimaryKey()
    {
        return with(new Static)->getKeyName();
    }
}
