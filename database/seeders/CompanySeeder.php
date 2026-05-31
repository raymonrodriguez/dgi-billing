<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Enums\DgiiEnvironment;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::create([
            'company_name' => 'RS code capital tecnologies',
            'trade_name' => 'RS Code SaaS',
            'tax_id' => '000000000',
            'environment' => DgiiEnvironment::TEST,
            'is_active' => true,
        ]);

        $user = User::where('email', 'admin@admin.com')->first();
        if ($user) {
            $user->companies()->syncWithoutDetaching([$company->id]);
        }
    }
}
