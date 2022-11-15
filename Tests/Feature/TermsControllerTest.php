<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TermsControllerTest extends TestCase
{
	use RefreshDatabase, \Gdevilbat\SpardaCMS\Modules\Core\Tests\ManualRegisterProvider;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testReadTerms()
    {
        $response = $this->get(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'));

        $response->assertStatus(302)
        		 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); // Return Not Valid, User Not Login

        $user = \App\Models\User::find(1);

        $response = $this->actingAs($user)
        				 ->from(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'))
                         ->json('GET',action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@serviceMaster'))
                         ->assertSuccessful()
                         ->assertJsonStructure(['data', 'draw', 'recordsTotal', 'recordsFiltered']); // Return Valid user Login
    }

    public function testFormCreateDataTerms()
    {
    	$response = $this->get(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@create'));

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); // Return Not Valid, User Not Login

        $user = \App\Models\User::find(1);

        $response = $this->actingAs($user)
        				 ->get(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@create'))
        				 ->assertSuccessful(); // Return Valid user Login
    }

    public function testCreateDataTerms()
    {
    	$response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@store'));

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login

        $user = \App\Models\User::find(1);

        $response = $this->actingAs($user)
        				 ->from(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@create'))
        				 ->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@store'))
        				 ->assertStatus(302)
        				 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@create'))
        				 ->assertSessionHasErrors(); //Return Not Valid, Data Not Complete

	    $faker = \Faker\Factory::create();
	    $name = $faker->word;
	    $slug = $faker->word;

        $group = \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::first();

		$response = $this->actingAs($user)
        				 ->from(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@create'))
        				 ->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@store'), [
								'name' => $name,
								'slug' => $slug,
                                'term_group' => $group->getKey()
        				 	])
        				 ->assertStatus(302)
        				 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'))
        				 ->assertSessionHas('global_message.status', 200)
        				 ->assertSessionHasNoErrors(); //Return Valid, Data Complete

	 	$this->assertDatabaseHas(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getTableName(), ['slug' => $slug, 'term_group' => $group->getKey()]);
    }

    public function testUpdateDataTerms()
    {
        $response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@store'), [
                        '_method' => 'PUT'
                    ]);

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login


        $user = \Gdevilbat\SpardaCMS\Modules\Core\Entities\User::with('role')->find(1);

        $faker = \Faker\Factory::create();

        $terms = \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::first();

        $response = $this->actingAs($user)
                        ->from(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@create').'?code='.encrypt($terms->getKey()))
                        ->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@store'), [
                            'name' => empty($terms->name) ? $faker->word : $terms->name,
                            'slug' => empty($terms->word) ? $faker->word : $terms->word,
                            $terms->getKeyName() => encrypt($terms->getKey()),
                            '_method' => 'PUT'
                        ])
                        ->assertStatus(302)
                        ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'))
                        ->assertSessionHas('global_message.status', 200)
                        ->assertSessionHasNoErrors(); //Return Valid, Data Complete
    }

    public function testDeleteDataTerms()
    {
        $response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@destroy'), [
                        '_method' => 'DELETE'
                    ]);

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login


        $user = \App\Models\User::find(1);

        $terms = \Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::first();

        $response = $this->actingAs($user)
                        ->from(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'))
                        ->post(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@destroy'), [
                            $terms->getKeyName() => encrypt($terms->getKey()),
                            '_method' => 'DELETE'
                        ])
                        ->assertStatus(302)
                        ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'))
                        ->assertSessionHas('global_message.status', 200);

        $this->assertDatabaseMissing(\Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\Terms::getTableName(), [$terms->getKeyName() => $terms->getKey()]);
    }
}
