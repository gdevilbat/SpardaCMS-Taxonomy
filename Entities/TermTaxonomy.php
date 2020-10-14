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

        $object = $object->load(['parent' => function($query){
            $query->where('taxonomy', $this->taxonomy);
        }, 'parent.term']);

        if(!empty($object->parent))
        {
            $child_slug = '/'.$this->getParentSlug($object->parent->term->slug, $object->parent);
        }

        return $slug.$child_slug;
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
