<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TermsTest extends DuskTestCase
{
    use DatabaseMigrations, \Gdevilbat\SpardaCMS\Modules\Core\Tests\ManualRegisterProvider;
    
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testCreateTerms()
    {
        $user = \App\User::find(1);
        $faker = \Faker\Factory::create();

        $this->browse(function (Browser $browser) use ($user, $faker) {
            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'))
                    ->assertSee('Master Data of Terms')
                    ->clickLink('Add New Terms')
                    ->waitForText('Terms Form')
                    ->AssertSee('Terms Form')
                    ->type('name', $faker->word)
                    ->type('slug', $faker->word)
                    ->script('document.getElementsByName("term_group")[0].selectedIndex = 1');

            $browser->press('Submit')
                    ->waitForText('Master Data of Terms')
                    ->assertSee('Successfully Add Term!');
        });
    }

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testEditTerms()
    {
        $user = \App\User::find(1);

        $this->browse(function (Browser $browser) use ($user) {

            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'))
                    ->assertSee('Master Data of Terms')
                    ->waitForText('Action')
                    ->clickLink('Action')
                    ->clickLink('Edit')
                    ->AssertSee('Terms Form')
                    ->press('Submit')
                    ->waitForText('Master Data of Terms')
                    ->assertSee('Successfully Update Term!');
        });
    }

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testDeleteTerms()
    {
        $user = \App\User::find(1);

        $faker = \Faker\Factory::create();

        $this->browse(function (Browser $browser) use ($user) {

            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TermsController@index'))
                    ->assertSee('Master Data of Terms')
                    ->waitForText('Action')
                    ->clickLink('Action')
                    ->clickLink('Delete')
                    ->waitForText('Delete Confirmation')
                    ->press('Delete')
                    ->waitForText('Master Data of Terms')
                    ->assertSee('Successfully Delete Term!');
        });
    }
}
