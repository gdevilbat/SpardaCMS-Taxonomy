<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities;

use Illuminate\Database\Eloquent\Model;

class TermTaxonomy extends Model
{
    protected $fillable = [];
    protected $table = 'term_taxonomy';
    protected $primaryKey = 'id_term_taxonomy';

    public function term()
    {
    	return $this->belongsTo('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms', 'term_id');
    }

    public function parent()
    {
        return $this->belongsTo(TermTaxonomy::class, 'parent_id');
    }

    public function childrens()
    {
    	return $this->hasMany(TermTaxonomy::class, 'parent_id');
    }

    public function allTaxonomyParents()
    {
        return $this->parent()->with('allTaxonomyParents.term');
    }

    public function allTaxonomyChildrens()
    {
        return $this->childrens()->with('allTaxonomyChildrens.term');
    }

    public function getFullSlugAttribute()
    {
        $slug = $this->getParentSlug($this->term->slug, $this);
        $slug = explode("/", $slug);
        $slug = collect($slug)->reverse()->toArray();
        $slug = implode('/', $slug);

        return $slug;
    }

    public function getParentSlug($slug, $object)
    {
        $child_slug = null;

        $object = $object->load(['taxonomyParents' => function($query){
            $query->where('taxonomy', $this->taxonomy);
        }, 'taxonomyParents.term']);

        if($object->taxonomyParents->count() >0)
        {
            $child_slug = '/'.$this->getParentSlug($object->taxonomyParents->first()->term->slug, $object->taxonomyParents->first());
        }

        return $slug.$child_slug;
    }

    public static function getTableName()
    {
        return with(new Static)->getTable();
    }

    public static function getPrimaryKey()
    {
        return with(new Static)->getKeyName();
    }
}
