<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class EcfSequenceSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            return;
        }

        $sequences = [
            [
                'type' => '31',
                'description' => 'Facturas de Crédito Fiscal Electrónicas',
                'start_range' => 1,
                'end_range' => 10000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYear(),
            ],
            [
                'type' => '32',
                'description' => 'Facturas de Consumo Electrónicas',
                'start_range' => 1,
                'end_range' => 50000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYear(),
            ],
            [
                'type' => '34',
                'description' => 'Notas de Crédito Electrónicas',
                'start_range' => 1,
                'end_range' => 1000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYear(),
            ],
        ];

        foreach ($sequences as $seqData) {
            $company->ecfSequences()->create($seqData);
        }
    }
}
