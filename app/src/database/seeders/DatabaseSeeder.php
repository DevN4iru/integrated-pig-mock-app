<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\GuardsProductionSeeding;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use GuardsProductionSeeding;

    public function run(): void
    {
        $this->guardAgainstProductionSeeding('DatabaseSeeder');

        $this->call([
            ProtocolTemplateSeeder::class,
            ProtocolRuleSeeder::class,
        ]);
    }
}
