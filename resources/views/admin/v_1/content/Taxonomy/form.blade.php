@extends('core::admin.'.$theme_cms->value.'.templates.parent')

@section('title_dashboard', 'Taxonomy')

@section('breadcrumb')
        <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
            <li class="m-nav__item m-nav__item--home">
                <a href="#" class="m-nav__link m-nav__link--icon">
                    <i class="m-nav__link-icon la la-home"></i>
                </a>
            </li>
            <li class="m-nav__separator">-</li>
            <li class="m-nav__item">
                <a href="" class="m-nav__link">
                    <span class="m-nav__link-text">Home</span>
                </a>
            </li>
            <li class="m-nav__separator">-</li>
            <li class="m-nav__item">
                <a href="" class="m-nav__link">
                    <span class="m-nav__link-text">Taxonomy</span>
                </a>
            </li>
        </ul>
@endsection

@section('content')

<div class="row">
    <div class="col-sm-12">

        <!--begin::Portlet-->
        <div class="m-portlet m-portlet--tab">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon m--hide">
                            <i class="fa fa-gear"></i>
                        </span>
                        <h3 class="m-portlet__head-text">
                            Taxonomy Form
                        </h3>
                    </div>
                </div>
            </div>

            <!--begin::Form-->
            <form class="m-form m-form--fit m-form--label-align-right" action="{{action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@store')}}" method="post">
                <div class="m-portlet__body">
                    <div class="col-md-5 offset-md-4">
                        @if (!empty(session('global_message')))
                            <div class="alert {{session('global_message')['status'] == 200 ? 'alert-info' : 'alert-warning' }}">
                                {{session('global_message')['message']}}
                            </div>
                        @endif
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Taxonomy Term<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                        </div>
                        <div class="col-md-8">
                            <select name="term_id" class="form-control m-input m-input--solid">
                                <option value="" selected disabled>-- Choose One --</option>
                                @foreach ($terms as $term)
                                    @if(old('term_id'))
                                        <option value="{{$term->getKey()}}" {{old('term_id') && old('term_id') == $term->getKey() ? 'selected' : ''}}>-- {{ucfirst($term->name)}} --</option>
                                    @else
                                        <option value="{{$term->getKey()}}" {{!empty($taxonomy) && $taxonomy->term->getKey() == $term->getKey() ? 'selected' : ''}}>-- {{ucfirst($term->name)}} --</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Taxonomy Description</label>
                        </div>
                        <div class="col-md-8">
                            <textarea type="text" class="form-control m-input autosize" name="description" placeholder="Taxonomy Description">{{old('description') ? old('description') : (!empty($taxonomy) ? $taxonomy->description : '')}}</textarea>
                        </div>
                    </div>
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Taxonomy Name<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                        </div>
                        <div class="col-md-8">
                            <div class="m-typeahead" id="m-typeahead">
                                <input type="text" class="form-control m-input typeahead" name="taxonomy" dir="ltr" placeholder="Taxonomy Name" value="{{old('taxonomy') ? old('taxonomy') : (!empty($taxonomy) ? $taxonomy->taxonomy : '')}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Taxonomy Parent</label>
                        </div>
                        <div class="col-md-8">
                            <select name="parent_id" class="form-control m-input m-input--solid select2">
                                <option value="" selected>-- Non Parent --</option>
                                @foreach ($parents as $parent)
                                    @if(old('parent_id'))
                                        <option value="{{$parent->getKey()}}" {{old('parent_id') && old('parent_id') == $parent->getKey() ? 'selected' : ''}}>-- {{ucfirst($parent->term->name)}} | {{$parent->taxonomy}} --</option>
                                    @else
                                        <option value="{{$parent->getKey()}}" {{!empty($taxonomy->parent) && $taxonomy->parent->getKey() == $parent->getKey() ? 'selected' : ''}}>-- {{ucfirst($parent->term->name)}} | {{$parent->taxonomy}} --</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                {{csrf_field()}}
                @if(isset($_GET['code']))
                    <input type="hidden" name="{{\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getPrimaryKey()}}" value="{{$_GET['code']}}">
                @endif
                {{$method}}
                <div class="m-portlet__foot m-portlet__foot--fit">
                    <div class="m-form__actions">
                        <div class="offset-md-4">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </form>

            <!--end::Form-->
        </div>

        <!--end::Portlet-->

    </div>
</div>
{{-- End of Row --}}

@endsection

@section('page_level_js')
    {{Html::script(module_asset_url('Core:assets/js/autosize.min.js'))}}
@endsection

@section('page_script_js')
    <script type="text/javascript">
        var states = {!!($suggestion_name)!!};
    </script>
    {{Html::script(module_asset_url('Taxonomy:resources/views/admin/'.$theme_cms->value.'/js/taxonomy.js').'?id='.filemtime(module_asset_path('Taxonomy:resources/views/admin/'.$theme_cms->value.'/js/taxonomy.js')))}}
@endsection