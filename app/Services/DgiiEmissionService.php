<?php

namespace App\Services;

use App\Models\Ecf;
use App\Jobs\SendEcfToDgiiJob;
use Illuminate\Support\Facades\Storage;
use PlatinumPlace\LaravelDgii\Facades\Dgii;
use Illuminate\Support\Str;

class DgiiEmissionService
{
    public function __construct(
        protected EcfTransformer $transformer
    ) {}

    /**
     * Orchestrate the process of rendering, signing, and preparing an Ecf for sending.
     *
     * @param Ecf $ecf
     * @return void
     * @throws \Exception
     */
    public function emit(Ecf $ecf): void
    {
        $ecf->load('company');
        $company = $ecf->company;

        if (!$company->certificate || !$company->cert_password) {
            throw new \Exception("La empresa no tiene configurado un certificado digital para firmar.");
        }

        // 1. Usar el Transformer (DTO) para formatear los datos
        $invoiceData = $this->transformer->transform($ecf);

        // 2. Recuperar el contenido del certificado y la contraseña
        $certContent = Storage::get($company->certificate);
        $certPassword = $company->cert_password;

        // 3. Llamar a Dgii::renderInvoice() para generar el XML firmado
        // El paquete devuelve un array con ['xml' => '...', 'hash' => '...']
        $result = Dgii::renderInvoice($certContent, $certPassword, $invoiceData);

        // 4. Guardar el XML firmado en el Storage
        $fileName = "signed_ecfs/{$company->tax_id}/{$ecf->encf}.xml";
        Storage::put($fileName, $result['xml']);

        // 5. Actualizar el modelo Ecf con la ruta y el código de seguridad
        $ecf->update([
            'signed_xml_path' => $fileName,
            // Guardar los primeros 6 caracteres del hash de la firma
            'security_code' => substr($result['hash'] ?? Str::random(12), 0, 6),
            'dgii_status' => 'Pending',
        ]);

        // 6. Despachar el Job en segundo plano para el envío real
        SendEcfToDgiiJob::dispatch($ecf->id);
    }
}
