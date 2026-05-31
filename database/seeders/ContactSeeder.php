<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            return;
        }

        $contacts = [
            [
                'name' => 'Supermercados Nacional S.A.',
                'document_type' => 'rnc',
                'tax_id' => '101010632',
                'email' => 'compras@nacional.com.do',
                'phone' => '809-541-2411',
                'address' => 'Av. 27 de Febrero, Santo Domingo',
                'is_electronic_receiver' => true,
            ],
            [
                'name' => 'Ferretería Americana S.A.S.',
                'document_type' => 'rnc',
                'tax_id' => '101011329',
                'email' => 'contabilidad@americana.com.do',
                'phone' => '809-548-8111',
                'address' => 'Av. John F. Kennedy, Santo Domingo',
                'is_electronic_receiver' => true,
            ],
            [
                'name' => 'Juan Pérez (Cliente Final)',
                'document_type' => 'cedula',
                'tax_id' => '00100000001',
                'email' => 'juan.perez@email.com',
                'phone' => '829-555-1234',
                'address' => 'Residencial Alameda, SD',
                'is_electronic_receiver' => false,
            ],
        ];

        foreach ($contacts as $contactData) {
            $company->contacts()->create($contactData);
        }
    }
}
