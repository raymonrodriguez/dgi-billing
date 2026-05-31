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
            'company_name' => 'DGII Soluciones Tecnológicas S.R.L.',
            'trade_name' => 'DGII Billing Pro',
            'tax_id' => '131560341',
            'environment' => DgiiEnvironment::TEST,
            'is_active' => true,
        ]);

        $user = User::where('email', 'admin@admin.com')->first();
        if ($user) {
            $user->companies()->syncWithoutDetaching([$company->id]);
        }
    }
}
