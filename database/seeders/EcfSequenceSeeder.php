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
                'expiration_date' => now()->addYears(2),
            ],
            [
                'type' => '32',
                'description' => 'Facturas de Consumo Electrónicas',
                'start_range' => 1,
                'end_range' => 50000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYears(2),
            ],
            [
                'type' => '33',
                'description' => 'Notas de Débito Electrónicas',
                'start_range' => 1,
                'end_range' => 1000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYears(2),
            ],
            [
                'type' => '34',
                'description' => 'Notas de Crédito Electrónicas',
                'start_range' => 1,
                'end_range' => 1000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYears(2),
            ],
            [
                'type' => '41',
                'description' => 'Comprobante de Compras Electrónico',
                'start_range' => 1,
                'end_range' => 1000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYears(2),
            ],
            [
                'type' => '43',
                'description' => 'Comprobante para Gastos Menores Electrónico',
                'start_range' => 1,
                'end_range' => 1000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYears(2),
            ],
            [
                'type' => '44',
                'description' => 'Comprobante de Regímenes Especiales',
                'start_range' => 1,
                'end_range' => 1000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYears(2),
            ],
            [
                'type' => '45',
                'description' => 'Comprobante Gubernamental Electrónico',
                'start_range' => 1,
                'end_range' => 1000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYears(2),
            ],
            [
                'type' => '46',
                'description' => 'Comprobante para Exportaciones Electrónico',
                'start_range' => 1,
                'end_range' => 1000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYears(2),
            ],
            [
                'type' => '47',
                'description' => 'Comprobante para Pagos al Exterior Electrónico',
                'start_range' => 1,
                'end_range' => 1000,
                'current_sequence' => 1,
                'expiration_date' => now()->addYears(2),
            ],
        ];

        foreach ($sequences as $seqData) {
            $company->ecfSequences()->create($seqData);
        }
    }
}
