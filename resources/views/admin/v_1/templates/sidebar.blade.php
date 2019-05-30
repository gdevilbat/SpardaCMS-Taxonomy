<li class="m-menu__item m-menu__item--submenu {{in_array(Route::current()->getName(), ['terms', 'taxonomy']) ? 'm-menu__item--expanded m-menu__item--open' : ''}}" aria-haspopup="true" m-menu-submenu-toggle="hover">
    <a href="javascript:void(0)" class="m-menu__link m-menu__toggle">
        <i class="m-menu__link-icon fa fa-project-diagram"></i>
            <span class="m-menu__link-text">Taxonomies</span>
        <i class="m-menu__ver-arrow la la-angle-right"></i>
     </a>
    <div class="m-menu__submenu "><span class="m-menu__arrow"></span>
        <ul class="m-menu__subnav">
            <li class="m-menu__item  {{Route::current()->getName() ==  'terms' ? 'm-menu__item--active' : ''}}" aria-haspopup="true"><a href="{{action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index')}}" class="m-menu__link "><i class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span class="m-menu__link-text">Terms</span></a></li>
        </ul>
    </div>
    <div class="m-menu__submenu "><span class="m-menu__arrow"></span>
        <ul class="m-menu__subnav">
            <li class="m-menu__item  {{Route::current()->getName() ==  'taxonomy' ? 'm-menu__item--active' : ''}}" aria-haspopup="true"><a href="{{action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index')}}" class="m-menu__link "><i class="m-menu__link-bullet m-menu__link-bullet--dot"><span></span></i><span class="m-menu__link-text">Taxonomy</span></a></li>
        </ul>
    </div>
</li>