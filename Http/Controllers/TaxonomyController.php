<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Foundation\AbstractTaxonomy;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy as Taxonomy_m;
use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms as Terms_m;

use Validator;
use Auth;

class TaxonomyController extends AbstractTaxonomy
{
    public function __construct()
    {
        parent::__construct();
        $this->module = 'taxonomy';
        $this->mod_dir = 'Taxonomy';
        $this->taxonomy = '';

    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'taxonomy' => 'required|max:191',
            'term_id' => 'required',
        ]);

        if($request->has('parent_id') && !empty($request->input('parent_id')))
        {
             $validator->addRules([
                    'parent_id' => [
                        function ($attribute, $value, $fail) use ($request) {
                            if ($request->input('taxonomy') != Taxonomy_m::where(Taxonomy_m::getPrimaryKey(), $request->input('parent_id'))->firstOrFail()->taxonomy) {
                                $fail($attribute.' Must Have Same Taxonomy Name with Parent');
                            }
                        },
                ],      
            ]);
        }

        if($request->isMethod('POST'))
        {
            $validator->addRules([
                'term_id' => Rule::unique($this->taxonomy_m->getTable())->where(function($query) use ($request){
                    return $query->where('taxonomy', $request->input('taxonomy'));
                })
            ]);
        }
        else
        {
            $validator->addRules([
                'term_id' => Rule::unique($this->taxonomy_m->getTable())->where(function($query) use ($request){
                    return $query->where('taxonomy', $request->input('taxonomy'));
                })->ignore(decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey())), \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey())
            ]);
        }

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        if($request->isMethod('POST'))
        {
            $data = $request->except('_token', '_method');
            $taxonomy = new $this->taxonomy_m;
        }
        else
        {
            $data = $request->except('_token', '_method', \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey());
            $taxonomy = $this->taxonomy_repository->findOrFail(decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey())));
            $this->authorize('update-taxonomy', $taxonomy);
        }

        foreach ($data as $key => $value) 
        {
            $taxonomy->$key = $value;
        }

        if($request->isMethod('POST'))
        {
            $taxonomy->created_by = Auth::id();
            $taxonomy->modified_by = Auth::id();
        }
        else
        {
            $taxonomy->modified_by = Auth::id();
        }

        if($taxonomy->save())
        {
            if($request->isMethod('POST'))
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add Taxonomy!'));
            }
            else
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update Taxonomy!'));
            }
        }
        else
        {
            if($request->isMethod('POST'))
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Add Taxonomy!'));
            }
            else
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Update Taxonomy!'));
            }
        }
    }

    public function getParentQuery()
    {
        return $this->taxonomy_m;
    }

    public function getSuggestionTag()
    {
        return Terms_m::whereHas('taxonomies', function($query){
                    $query->where('taxonomy', 'tag');
                })
                ->pluck('name');
    }
}
