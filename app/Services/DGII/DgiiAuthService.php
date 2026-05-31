<?php

namespace App\Services\DGII;

use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\DgiiTokenRepositoryInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Exception;
use RuntimeException;

class DgiiAuthService
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepo,
        protected DgiiTokenRepositoryInterface $tokenRepo,
        protected EcfSigningService $signingService
    ) {
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function getToken(): string
    {
        $company = $this->companyRepo->getActiveCompany();
        if (!$company) {
            throw new RuntimeException("No hay una empresa emisora configurada activa.");
        }

        // 1. Validar si ya existe un token almacenado en base de datos
        $existingToken = $this->tokenRepo->getValidToken($company->tax_id);
        if ($existingToken) {
            return $existingToken;
        }

        // Determinar ambiente (asumimos que la empresa lo tiene)
        $environment = $company->environment;

        // 2. Solicitar semilla a la DGII
        $semillaUrl = "{$environment->url()}/api/autenticacion/semilla";
        $response = Http::get($semillaUrl);

        if (!$response->successful()) {
            throw new Exception("Error obteniendo la semilla de autenticación desde la DGII.");
        }

        $semillaXml = $response->body();

        // 3. Firmar la semilla usando el servicio de firmado
        $certData = $this->companyRepo->getCertificateData($company);
        $semillaFirmada = $this->signingService->signXml($semillaXml, $certData['path'], $certData['password']);

        // 4. Intercambiar semilla firmada por el Token de acceso
        $tokenUrl = "{$environment->url()}/api/autenticacion/token";
        $tokenResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/xml',
        ])->withBody($semillaFirmada, 'application/xml')->post($tokenUrl);

        if (!$tokenResponse->successful()) {
            throw new Exception("Error al intercambiar la semilla firmada por el token de la DGII: " . $tokenResponse->body());
        }

        $tokenData = $tokenResponse->json();

        // 5. Guardar token en el repositorio
        $this->tokenRepo->saveToken(
            $company->tax_id,
            $tokenData['token'],
            $tokenData['expira']
        );

        return $tokenData['token'];
    }
}
