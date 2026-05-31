<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Ecf;
use Illuminate\Database\Seeder;

class EcfSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) return;

        $contact = $company->contacts()->where('is_electronic_receiver', true)->first();
        $finalConsumer = $company->contacts()->where('is_electronic_receiver', false)->first();

        // Factura 1: Crédito Fiscal (31)
        if ($contact) {
            $invoice1 = Ecf::create([
                'company_id' => $company->id,
                'contact_id' => $contact->id,
                'encf' => 'E310000000001',
                'type' => '31',
                'total_amount' => 5900.00,
                'total_tax' => 900.00,
                'dgii_status' => 'Aceptado',
                'issued_at' => now()->subDays(2),
            ]);

            $invoice1->items()->create([
                'description' => 'Licencia de Software SaaS (Anual)',
                'quantity' => 1,
                'price' => 5000.00,
                'subtotal' => 5000.00,
            ]);

            $invoice1->taxes()->create([
                'type' => 'ITBIS',
                'rate' => 18.00,
                'amount' => 900.00,
            ]);

            $invoice1->payments()->create([
                'method' => '04', // Transferencia
                'amount' => 5900.00,
            ]);
        }

        // Factura 2: Consumo (32)
        if ($finalConsumer) {
            $invoice2 = Ecf::create([
                'company_id' => $company->id,
                'contact_id' => $finalConsumer->id,
                'encf' => 'E320000000001',
                'type' => '32',
                'total_amount' => 1180.00,
                'total_tax' => 180.00,
                'dgii_status' => 'En Proceso',
                'issued_at' => now(),
            ]);

            $invoice2->items()->create([
                'description' => 'Soporte Técnico Remoto',
                'quantity' => 2,
                'price' => 500.00,
                'subtotal' => 1000.00,
            ]);

            $invoice2->taxes()->create([
                'type' => 'ITBIS',
                'rate' => 18.00,
                'amount' => 180.00,
            ]);

            $invoice2->payments()->create([
                'method' => '01', // Efectivo
                'amount' => 1180.00,
            ]);
        }
    }
}
