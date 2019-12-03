<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Foundation;

use Illuminate\Http\Request;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Contract\InterfaceTaxonomy;
use Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\CoreController;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy as Taxonomy_m;
use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms as Terms_m;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;

use DB;
use View;
use Auth;
use Validator;

/**
 * Class EloquentCoreRepository
 *
 * @package Gdevilbat\SpardaCMS\Modules\Core\Repositories\Eloquent
 */
abstract class AbstractTaxonomy extends CoreController implements InterfaceTaxonomy
{
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
        return view($this->getModule().'::admin.'.$this->data['theme_cms']->value.'.content.'.$this->getModDir().'.master', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $this->data['terms'] = $this->terms_repository->all();
        $this->data['suggestion_name'] = $this->taxonomy_m->groupBy('taxonomy')->pluck('taxonomy');
        $this->data['method'] = method_field('POST');
        if(isset($_GET['code']))
        {
            $this->data['taxonomy'] = $this->taxonomy_m->with(['term', 'parent'])->where(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey(), decrypt($_GET['code']))->first();
            $this->data['parents'] = $this->getParentQuery()
                                                ->where(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey(), '!=', decrypt($_GET['code']))
                                                ->get();
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-taxonomy', $this->data['taxonomy']);
        }
        else
        {
            $this->data['parents'] = $this->getParentQuery()->get();
        }

        return view($this->getModule().'::admin.'.$this->data['theme_cms']->value.'.content.'.$this->getModDir().'.form', $this->data);
    }

    public function serviceMaster(Request $request)
    {
        $column = [\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey(), 'name', 'taxonomy', 'parent_name', 'created_at'];

        $length = !empty($request->input('length')) ? $request->input('length') : 10 ;
        $column = !empty($request->input('order.0.column')) ? $column[$request->input('order.0.column')] : \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey() ;
        $dir = !empty($request->input('order.0.dir')) ? $request->input('order.0.dir') : 'DESC' ;
        $searchValue = $request->input('search')['value'];

        $query = $this->taxonomy_m->leftJoin(Terms_m::getTableName(), Terms_m::getTableName().'.'.Terms_m::getPrimaryKey(), '=', Taxonomy_m::getTableName().'.term_id')
                                  ->leftJoin(Taxonomy_m::getTableName().' as child', Taxonomy_m::getTableName().'.parent_id', '=', 'child.'.Taxonomy_m::getPrimaryKey())
                                  ->leftJoin(Terms_m::getTableName().' as parent', 'child.term_id', '=', 'parent.'.Terms_m::getPrimaryKey())
                                  ->with(['term', 'parent.term'])
                                  ->select(Taxonomy_m::getTableName().'.*', Terms_m::getTableName().'.name', 'parent.name as parent_name')
                                  ->orderBy($column, $dir);

        if(!empty($this->taxonomy))
        {
            $query = $query->where(Taxonomy_m::getTableName().'.taxonomy', $this->getTaxonomy());
        }

        $recordsTotal = $query->count();
        $filtered = $query;

        if($searchValue)
        {
            $filtered->where(function($query) use ($searchValue){
                         $query->where(DB::raw("CONCAT(".Taxonomy_m::getTableName().".taxonomy,'-',".Taxonomy_m::getTableName().".created_at)"), 'like', '%'.$searchValue.'%')
                             ->orWhereHas('term', function($query) use ($searchValue){
                                $query->where(DB::raw("CONCAT(".Terms_m::getTableName().".name,'-',".Terms_m::getTableName().".slug)"), 'like', '%'.$searchValue.'%');
                             })
                             ->orWhereHas('parent.term', function($query) use ($searchValue){
                                $query->where(DB::raw("CONCAT(".Terms_m::getTableName().".name,'-',".Terms_m::getTableName().".slug)"), 'like', '%'.$searchValue.'%');
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
                    $data[$i][0] = $taxonomy->getKey();
                    $data[$i][1] = $taxonomy->term->name;
                    $data[$i][2] = $taxonomy->taxonomy;

                    if(!empty($taxonomy->parent))
                    {
                        $data[$i][3] = '<span class="badge badge-danger">'.$taxonomy->parent->term->name.'</span>';
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

    public function getActionTable($taxonomy)
    {
        $view = View::make($this->getModule().'::admin.'.$this->data['theme_cms']->value.'.content.'.$this->getModDir().'.service_master', [
            'taxonomy' => $taxonomy
        ]);

        $html = $view->render();
       
       return $html;
    }


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request)
    {
        $query = $this->taxonomy_m->findOrFail(decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey())));
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

    public function getModule()
    {
        return $this->module;
    }

    public function getTaxonomy()
    {
        return $this->taxonomy;
    }

    public function getModDir()
    {
        return $this->mod_dir;
    }
}
