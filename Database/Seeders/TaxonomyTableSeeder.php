<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use DB;

use Gdevilbat\SpardaCMS\Modules\Taxonomy\Entities\TermTaxonomy;

class TaxonomyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        TermTaxonomy::firstOrCreate(
            [ 'taxonomy' => 'category'],
            [
                'term_id' => 1,
                'created_by' => 1,
                'modified_by' => 1,
                'created_at' => \Carbon\Carbon::now()
            ],
        );

        TermTaxonomy::firstOrCreate(
            [ 'taxonomy' => 'tag'],
            [
                'term_id' => 1,
                'created_by' => 1,
                'modified_by' => 1,
                'created_at' => \Carbon\Carbon::now()
            ]
        );
    }
}
