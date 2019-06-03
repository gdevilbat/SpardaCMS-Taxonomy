<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\CoreController;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy as Taxonomy_m;
use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms as Terms_m;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;

use Validator;
use Auth;
use View;
use DB;

class TaxonomyController extends CoreController
{
    protected $module = 'taxonomy';
    protected $mod_dir = 'Taxonomy';
    protected $taxonomy = '';

    public function __construct()
    {
        parent::__construct();
        $this->taxonomy_m = new Taxonomy_m;
        $this->taxonomy_repository = new Repository(new Taxonomy_m);
        $this->terms_m = new Terms_m;
        $this->terms_repository = new Repository(new Terms_m);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view($this->module.'::admin.'.$this->data['theme_cms']->value.'.content.'.$this->mod_dir.'.master', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $terms = $this->terms_repository->all();
        $this->data['terms'] = $terms;
        $this->data['parents'] = $terms;
        $this->data['suggestion_name'] = $this->taxonomy_m->groupBy('taxonomy')->pluck('taxonomy');
        $this->data['method'] = method_field('POST');
        if(isset($_GET['code']))
        {
            $this->data['taxonomy'] = $this->taxonomy_m->with(['term', 'parent'])->where('id', decrypt($_GET['code']))->first();
            $this->data['parents'] = $this->terms_m->with('taxonomies')->whereDoesntHave('taxonomies', function($query){
                                                    $query->where('id', decrypt($_GET['code']));
                                                })
                                                ->get();
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-taxonomy', $this->data['taxonomy']);
        }

        return view($this->module.'::admin.'.$this->data['theme_cms']->value.'.content.'.$this->mod_dir.'.form', $this->data);
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
            $data = $request->except('_token', '_method', 'id');
            $taxonomy = $this->taxonomy_repository->findOrFail(decrypt($request->input('id')));
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

    public function serviceMaster(Request $request)
    {
        $column = ['id', 'term', 'taxonomy', 'parent', 'created_at'];

        $length = !empty($request->input('length')) ? $request->input('length') : 10 ;
        $column = !empty($request->input('order.0.column')) ? $column[$request->input('order.0.column')] : 'id' ;
        $dir = !empty($request->input('order.0.dir')) ? $request->input('order.0.dir') : 'DESC' ;
        $searchValue = $request->input('search')['value'];

        $query = $this->taxonomy_m->with(['term', 'parent'])->orderBy($column, $dir);

        if(!empty($this->taxonomy))
        {
            $query = $query->where('taxonomy', $this->taxonomy);
        }

        $recordsTotal = $query->count();
        $filtered = $query;

        if($searchValue)
        {
            $filtered->where(function($query) use ($searchValue){
                         $query->where(DB::raw("CONCAT(taxonomy,'-',created_at)"), 'like', '%'.$searchValue.'%')
                             ->orWhereHas('term', function($query) use ($searchValue){
                                $query->where(DB::raw("CONCAT(name,'-',slug)"), 'like', '%'.$searchValue.'%');
                             })
                             ->orWhereHas('parent', function($query) use ($searchValue){
                                $query->where(DB::raw("CONCAT(name,'-',slug)"), 'like', '%'.$searchValue.'%');
                             });
                    });
        }

        $filteredTotal = $filtered->count();

        $this->data['length'] = $length;
        $this->data['column'] = $column;
        $this->data['dir'] = $dir;
        $this->data['taxonomies'] = $filtered->offset($request->input('start'))->limit($length)->get();

        /*=========================================
        =            Parsing Datatable            =
        =========================================*/
            
            $data = array();
            $i = 0;
            foreach ($this->data['taxonomies'] as $key_user => $taxonomy) 
            {
                if(Auth::user()->can('read-taxonomy', $taxonomy))
                {
                    $data[$i][0] = $taxonomy->id;
                    $data[$i][1] = $taxonomy->term->name;
                    $data[$i][2] = $taxonomy->taxonomy;

                    if(!empty($taxonomy->parent))
                    {
                        $data[$i][3] = '<span class="badge badge-danger">'.$taxonomy->parent->name.'</span>';
                    }
                    else
                    {
                        $data[$i][3] = '-';
                    }

                    $data[$i][4] = $taxonomy->created_at->toDateTimeString();
                    $data[$i][5] = $this->getActionTable($taxonomy);
                    $i++;
                }
            }
        
        /*=====  End of Parsing Datatable  ======*/
        
        return ['data' => $data, 'draw' => (integer)$request->input('draw'), 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $filteredTotal];
    }

    private function getActionTable($taxonomy)
    {
        $view = View::make($this->module.'::admin.'.$this->data['theme_cms']->value.'.content.'.$this->mod_dir.'.service_master', [
            'taxonomy' => $taxonomy
        ]);

        $html = $view->render();
       
       return $html;
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('taxonomy::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('taxonomy::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request)
    {
        $query = $this->taxonomy_m->findOrFail(decrypt($request->input('id')));
        $this->authorize('delete-taxonomy', $query);

        try {
            if($query->delete())
            {
                return redirect()->back()->with('global_message', array('status' => 200,'message' => 'Successfully Delete Taxonomy!'));
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('global_message', array('status' => 200,'message' => 'Failed Delete Taxonomy, It\'s Has Been Used!'));
        }
    }
}
