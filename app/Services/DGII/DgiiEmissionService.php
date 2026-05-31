<?php

namespace App\Services\DGII;

use App\Repositories\Contracts\EcfRepositoryInterface;
use App\Enums\EcfStatus;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;
use Exception;

class DgiiEmissionService
{
    public function __construct(
        protected EcfRepositoryInterface $ecfRepo,
        protected DgiiAuthService $authService,
        protected EcfSigningService $signingService
    ) {
    }

    /**
     * Procesa, transforma, firma y emite un e-CF hacia los Web Services de la DGII.
     * @throws ConnectionException
     */
    public function emit(int $ecfId): array
    {
        $ecf = $this->ecfRepo->findById($ecfId);
        if (!$ecf) {
            throw new Exception("El comprobante electrónico con ID {$ecfId} no existe.");
        }

        try {
            // 1. Obtener Token de sesión vigente
            $token = $this->authService->getToken();

            // 2. Renderizar la plantilla XML correspondiente según el tipo de e-CF (Blade template)
            // Usamos nuestra vista estandarizada resources/views/xml/acecf/xml.blade.php o similar
            // Para este ejemplo, buscamos una genérica invoices/ecf_{tipo}
            $viewName = "xml.invoices.ecf_" . $ecf->type;
            if (!View::exists($viewName)) {
                // TODO: Crear plantillas para cada tipo o usar una base
                $viewName = "xml.approvals.xml";
            }

            $rawXml = View::make($viewName, ['ecf' => $ecf])->render();

            // 3. Firmar el XML digitalmente
            $company = $ecf->company;
            $certData = [
                'path' => Storage::path($company->certificate),
                'password' => $company->cert_password
            ];

            $signedXml = $this->signingService->signXml($rawXml, $certData['path'], $certData['password']);

            // 4. Guardar archivo firmado localmente
            $fileName = "ecf_signed/{$company->tax_id}/{$ecf->encf}.xml";
            Storage::put($fileName, $signedXml);
            $this->ecfRepo->updateStatus($ecf, EcfStatus::FIRMADO, null, $fileName);

            // 5. Enviar el XML a la DGII mediante la API de Recepción
            $environment = $company->environment;

            // Determinar endpoint correcto según tipo
            $endpoint = "{$environment->url()}/api/recepcion/ecf";
            if ($ecf->type === '32' && $ecf->total_amount < 250000) {
                $endpoint = "{$environment->url()}/api/recepcion/rfce";
            }

            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->withBody($signedXml, 'application/xml')
                ->post($endpoint);

            $this->ecfRepo->saveDgiiResponse($ecf, [
                'status_code' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $trackId = $responseData['trackId'] ?? null;

                $this->ecfRepo->updateStatus($ecf, EcfStatus::ENVIADO, $trackId);

                return [
                    'success' => true,
                    'trackId' => $trackId,
                    'message' => 'Comprobante recibido de forma conforme por la DGII.'
                ];
            }

            $this->ecfRepo->updateStatus($ecf, EcfStatus::ERROR);
            throw new Exception("La DGII rechazó la estructura o transporte: " . $response->body());

        } catch (Exception $e) {
            $this->ecfRepo->updateStatus($ecf, EcfStatus::ERROR);
            throw $e;
        }
    }
}
