<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'control', 'middleware' => 'core.auth'], function() {
    
	Route::group(['prefix' => 'taxonomy'], function() {

        /*=============================================
        =            Taxonomy CMS            =
        =============================================*/

        Route::group(['middleware' => 'core.menu'], function() {
	        Route::get('master', 'TaxonomyController@index')->name('taxonomy')->middleware('can:menu-taxonomy');
		    Route::get('form', 'TaxonomyController@create')->name('taxonomy');
		    Route::post('form', 'TaxonomyController@store')->name('taxonomy')->middleware('can:create-taxonomy');
		    Route::put('form', 'TaxonomyController@store')->name('taxonomy');
		    Route::delete('form', 'TaxonomyController@destroy')->name('taxonomy');
        });

	    Route::group(['prefix' => 'api'], function() {
		    Route::get('master', 'TaxonomyController@serviceMaster');
		    Route::get('suggestion-tag', 'TaxonomyController@getSuggestionTag');
	    });
        
        /*=====  End of Taxonomy CMS  ======*/
        
	});

	Route::group(['prefix' => 'terms'], function() {

        /*=============================================
        =            Taxonomy CMS            =
        =============================================*/
        
			Route::group(['middleware' => 'core.menu'], function() {
			    Route::get('master', 'TermsController@index')->name('terms')->middleware('can:menu-taxonomy');
			    Route::get('form', 'TermsController@create')->name('terms');
			    Route::post('form', 'TermsController@store')->name('terms')->middleware('can:create-taxonomy');
			    Route::put('form', 'TermsController@store')->name('terms');
			    Route::delete('form', 'TermsController@destroy')->name('terms');
			});

		    Route::group(['prefix' => 'api'], function() {
			    Route::get('master', 'TermsController@serviceMaster');
		    });
        
        /*=====  End of Taxonomy CMS  ======*/
	});
	
});
