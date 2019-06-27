<div class="col">
    <div class="btn-group">
        <a href="javascript:void(0)" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action
        </a>
        <div class="dropdown-menu dropdown-menu-left">
            @can('update-taxonomy', $term)
                <button class="dropdown-item" type="button">
                    <a class="m-link m-link--state m-link--info" href="{{action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@create').'?code='.encrypt($term->id)}}"><i class="fa fa-edit"> Edit</i></a>
                </button>
            @endcan
            @can('delete-taxonomy', $term)
                <form action="{{action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@destroy')}}" method="post" accept-charset="utf-8">
                    {{method_field('DELETE')}}
                    {{csrf_field()}}
                    <input type="hidden" name="id" value="{{encrypt($term->id)}}">
                </form>
                <button class="dropdown-item confirm-delete" type="button"><a class="m-link m-link--state m-link--accent" data-toggle="modal" href="#small"><i class="fa fa-trash"> Delete</i></a></button>
            @endcan
        </div>
    </div>
</div>