<?php

namespace Gdevilbat\SpardaCMS\Modules\Taxonomy\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use DB;

class TaxonomyModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::table('module')->insert([
            [
                'name' => 'Taxonomy',
                'slug' => 'taxonomy',
                'scope' => json_encode(array('menu', 'create', 'read', 'update', 'delete')),
                'created_at' => \Carbon\Carbon::now()
            ]
        ]);
    }
}
