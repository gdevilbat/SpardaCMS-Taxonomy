<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TaxonomyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(TaxonomyModuleTableSeeder::class);
        $this->call(TermsTableSeeder::class);
        $this->call(TaxonomyTableSeeder::class);
    }
}
