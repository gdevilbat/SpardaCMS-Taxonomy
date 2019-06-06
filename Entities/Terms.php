<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities;

use Illuminate\Database\Eloquent\Model;

use Str;

class Terms extends Model
{
    protected $fillable = [];
    protected $table = 'terms';

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

    public function termMetas()
    {
        return $this->hasMany('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermMeta', 'term_id');
    }
}
