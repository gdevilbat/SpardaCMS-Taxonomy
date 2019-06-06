<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities;

use Illuminate\Database\Eloquent\Model;

class TermTaxonomy extends Model
{
    protected $fillable = [];
    protected $table = 'term_taxonomy';

    public function term()
    {
    	return $this->belongsTo('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms', 'term_id');
    }

    public function parent()
    {
    	return $this->belongsTo('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms', 'parent_id');
    }

    public function taxonomyChildrens()
    {
        return $this->hasManyThrough(
                        '\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy',
                        '\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms',
                        'id',
                        'parent_id',
                        'term_id',
                        'id'
        );
    }

    public function taxonomyParents()
    {
        return $this->hasManyThrough(
                        '\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy',
                        '\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms',
                        'id',
                        'term_id',
                        'parent_id',
                        'id'
        );
    }
}
