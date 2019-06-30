<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TaxonomyTest extends DuskTestCase
{
    use DatabaseMigrations, \Gdevilbat\SpardaCMS\Modules\Core\Tests\ManualRegisterProvider;
    
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testCreateTaxonomy()
    {
        $user = \App\User::find(1);
        $faker = \Faker\Factory::create();

        $this->browse(function (Browser $browser) use ($user, $faker) {
            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'))
                    ->assertSee('Master Data of Taxonomy')
                    ->clickLink('Add New Taxonomy')
                    ->waitForText('Taxonomy Form')
                    ->AssertSee('Taxonomy Form')
                    ->type('taxonomy', $faker->word)
                    ->type('description', $faker->text);

            $browser->script('document.getElementsByName("term_id")[0].selectedIndex = 1');
            $browser->script('document.getElementsByName("parent_id")[0].selectedIndex = 1');

            $browser->press('Submit')
                    ->waitForText('Master Data of Taxonomy')
                    ->assertSee('Successfully Add Taxonomy!');
        });
    }

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testEditTaxonomy()
    {
        $user = \App\User::find(1);
        $faker = \Faker\Factory::create();

        $this->browse(function (Browser $browser) use ($user, $faker) {

            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'))
                    ->assertSee('Master Data of Taxonomy')
                    ->waitForText('Actions')
                    ->clickLink('Actions')
                    ->clickLink('Edit')
                    ->AssertSee('Taxonomy Form')
                    ->type('taxonomy', $faker->word)
                    ->type('description', $faker->text);

            $browser->script('document.getElementsByName("term_id")[0].selectedIndex = 1');
            $browser->script('document.getElementsByName("parent_id")[0].selectedIndex = 1');
            
            $browser->press('Submit')
                    ->waitForText('Master Data of Taxonomy')
                    ->assertSee('Successfully Update Taxonomy!');
        });
    }

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testDeleteTaxonomy()
    {
        $user = \App\User::find(1);

        $faker = \Faker\Factory::create();

        $this->browse(function (Browser $browser) use ($user) {

            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\Taxonomy\Http\Controllers\TaxonomyController@index'))
                    ->assertSee('Master Data of Taxonomy')
                    ->waitForText('Actions')
                    ->clickLink('Actions')
                    ->clickLink('Delete')
                    ->waitForText('Delete Confirmation')
                    ->press('Delete')
                    ->waitForText('Master Data of Taxonomy')
                    ->assertSee('Successfully Delete Taxonomy!');
        });
    }
}
