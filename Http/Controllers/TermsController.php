<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\CoreController;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms as Terms_m;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;

use Validator;
use Auth;
use View;
use DB;

class TermsController extends CoreController
{
    public function __construct()
    {
        parent::__construct();
        $this->terms_m = new Terms_m;
        $this->terms_repository = new Repository(new Terms_m, resolve(\Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository::class));
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('taxonomy::admin.'.$this->data['theme_cms']->value.'.content.Terms.master', $this->data);
    }

    public function serviceMaster(Request $request)
    {
        $column = [\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getPrimaryKey(), 'name', 'slug', 'parent_name', 'created_at'];

        $length = !empty($request->input('length')) ? $request->input('length') : 10 ;
        $column = !empty($request->input('order.0.column')) ? $column[$request->input('order.0.column')] : \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getPrimaryKey() ;
        $dir = !empty($request->input('order.0.dir')) ? $request->input('order.0.dir') : 'DESC' ;
        $searchValue = $request->input('search')['value'];

        $query = $this->terms_m->leftJoin(Terms_m::getTableName().' as group', 'group.'.Terms_m::getPrimaryKey(), '=', Terms_m::getTableName().'.term_group')
                               ->with('group')
                               ->orderBy($column, $dir)
                               ->select(Terms_m::getTableName().'.*', 'group.name as parent_name');

        $recordsTotal = $query->count();
        $filtered = $query;

        if($searchValue)
        {
            $filtered->where(DB::raw("CONCAT(".Terms_m::getTableName().".name,'-',".Terms_m::getTableName().".slug,'-',".Terms_m::getTableName().".created_at)"), 'like', '%'.$searchValue.'%');
        }

        $filteredTotal = $filtered->count();

        $this->data['length'] = $length;
        $this->data['column'] = $column;
        $this->data['dir'] = $dir;
        $this->data['terms'] = $filtered->offset($request->input('start'))->limit($length)->get();

        /*=========================================
        =            Parsing Datatable            =
        =========================================*/
            
            $data = array();
            $i = 0;
            foreach ($this->data['terms'] as $key_user => $term) 
            {
                if(Auth::user()->can('read-taxonomy', $term))
                {
                    $data[$i][0] = $term->getKey();
                    $data[$i][1] = $term->name;
                    $data[$i][2] = $term->slug;

                    if(!empty($term->group))
                    {
                        $data[$i][3] = '<span class="badge badge-danger">'.$term->group->name.'</span>';
                    }
                    else
                    {
                        $data[$i][3] = '-';
                    }

                    $data[$i][4] = $term->created_at->toDateTimeString();
                    $data[$i][5] = $this->getActionTable($term);
                    $i++;
                }
            }
        
        /*=====  End of Parsing Datatable  ======*/

        return ['data' => $data, 'draw' => (integer)$request->input('draw'), 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $filteredTotal];
    }

    private function getActionTable($term)
    {
        $view = View::make('taxonomy::admin.'.$this->data['theme_cms']->value.'.content.Terms.service_master', [
            'term' => $term
        ]);

        $html = $view->render();
       
       return $html;
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $this->data['method'] = method_field('POST');
        $this->data['groups'] = $this->terms_m->all();
        if(isset($_GET['code']))
        {
            $this->data['term'] = $this->terms_m->with('group')->where(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getPrimaryKey(), decrypt($_GET['code']))->first();
            $this->data['groups'] = $this->terms_m->where(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getPrimaryKey(), '!=', decrypt($_GET['code']))->get();
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-taxonomy', $this->data['term']);
        }

        return view('taxonomy::admin.'.$this->data['theme_cms']->value.'.content.Terms.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:191',
        ]);

        if($request->isMethod('POST'))
        {
            $validator->addRules([
                'slug' => 'max:191|unique:'.$this->terms_m->getTable().',slug'
            ]);
        }
        else
        {
            $validator->addRules([
                'slug' => 'max:191|unique:'.$this->terms_m->getTable().',slug,'.decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getPrimaryKey())).','.\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getPrimaryKey()
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
            $term = new $this->terms_m;
        }
        else
        {
            $data = $request->except('_token', '_method', \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getPrimaryKey());
            $term = $this->terms_repository->findOrFail(decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getPrimaryKey())));
            $this->authorize('update-taxonomy', $term);
        }

        foreach ($data as $key => $value) 
        {
            $term->$key = $value;
        }

        if($request->isMethod('POST'))
        {
            $term->created_by = Auth::id();
            $term->modified_by = Auth::id();
        }
        else
        {
            $term->modified_by = Auth::id();
        }

        if($term->save())
        {
            if($request->isMethod('POST'))
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add Term!'));
            }
            else
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update Term!'));
            }
        }
        else
        {
            if($request->isMethod('POST'))
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Add Term!'));
            }
            else
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Update Term!'));
            }
        }
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
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request)
    {
        $query = $this->terms_m->findOrFail(decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getPrimaryKey())));
        $this->authorize('delete-taxonomy', $query);

        try {
            if($query->delete())
            {
                return redirect()->back()->with('global_message', array('status' => 200,'message' => 'Successfully Delete Term!'));
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('global_message', array('status' => 200,'message' => 'Failed Delete Term, It\'s Has Been Used!'));
        }
    }
}
