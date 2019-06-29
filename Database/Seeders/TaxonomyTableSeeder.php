<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use DB;

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

        DB::table('term_taxonomy')->insert([
            [
                'term_id' => 1,
                'taxonomy' => 'category',
                'created_by' => 1,
                'modified_by' => 1,
                'created_at' => \Carbon\Carbon::now()
            ],
            [
                'term_id' => 1,
                'taxonomy' => 'tag',
                'created_by' => 1,
                'modified_by' => 1,
                'created_at' => \Carbon\Carbon::now()
            ]
        ]);
    }
}
