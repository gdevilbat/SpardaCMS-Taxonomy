<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxonomyControllerTest extends TestCase
{
	use RefreshDatabase, \Gdevilbat\SpardaCMS\Modules\Core\Tests\ManualRegisterProvider;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testReadTaxonomy()
    {
        $response = $this->get(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'));

        $response->assertStatus(302)
        		 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); // Return Not Valid, User Not Login

        $user = \App\Models\User::find(1);

        $response = $this->actingAs($user)
        				 ->from(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'))
                         ->json('GET',action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@serviceMaster'))
                         ->assertSuccessful()
                         ->assertJsonStructure(['data', 'draw', 'recordsTotal', 'recordsFiltered']); // Return Valid user Login
    }

    public function testCreateDataTaxonomy()
    {
        $response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@store'));

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login

        $user = \App\Models\User::find(1);

        $response = $this->actingAs($user)
                         ->from(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@create'))
                         ->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@store'))
                         ->assertStatus(302)
                         ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@create'))
                         ->assertSessionHasErrors(); //Return Not Valid, Data Not Complete

        $faker = \Faker\Factory::create();
        $name = $faker->word;

        $term = \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::first();

        $response = $this->actingAs($user)
                         ->from(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@create'))
                         ->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@store'), [
                                'term_id' => $term->getKey(),
                                'taxonomy' => $name,
                                //'parent_id' => $term->getKey() disable because parent id must be same
                            ])
                         ->assertStatus(302)
                         ->assertSessionHasNoErrors() //Return Valid, Data Complete
                         ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'))
                         ->assertSessionHas('global_message.status', 200);

        $this->assertDatabaseHas(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getTableName(), ['taxonomy' => $name, 'term_id' => $term->getKey()/*, 'parent_id' => $term->getKey()*/]);
    }

    public function testUpdateDataTaxonomy()
    {
        $response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@store'), [
                        '_method' => 'PUT'
                    ]);

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login


        $user = \Gdevilbat\SpardaCMS\Modules\Core\Entities\User::with('role')->find(1);

        $faker = \Faker\Factory::create();

        $taxonomy = \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::first();

        $response = $this->actingAs($user)
                        ->from(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@create').'?code='.encrypt($taxonomy->getKey()))
                        ->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@store'), [
                            'taxonomy' => empty($taxonomy->taxonomy) ? $faker->word : $taxonomy->taxonomy,
                            'term_id' => $taxonomy->term_id,
                            'parent_id' => $taxonomy->parent_id,
                            $taxonomy->getKeyName() => encrypt($taxonomy->getKey()),
                            '_method' => 'PUT'
                        ])
                        ->assertStatus(302)
                        ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'))
                        ->assertSessionHas('global_message.status', 200)
                        ->assertSessionHasNoErrors(); //Return Valid, Data Complete
    }

    public function testDeleteDataTaxonomy()
    {
        $response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@destroy'), [
                        '_method' => 'DELETE'
                    ]);

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login


        $user = \App\Models\User::find(1);

        $taxonomy = \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::first();

        $response = $this->actingAs($user)
                        ->from(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'))
                        ->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@destroy'), [
                            $taxonomy->getKeyName() => encrypt($taxonomy->getKey()),
                            '_method' => 'DELETE'
                        ])
                        ->assertStatus(302)
                        ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'))
                        ->assertSessionHas('global_message.status', 200);

        $this->assertDatabaseMissing(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy::getTableName(), [$taxonomy->getKeyName() => $taxonomy->getKey()]);
    }
}
