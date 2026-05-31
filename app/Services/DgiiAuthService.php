<?php

namespace App\Services;

use App\Models\Company;
use App\Models\DgiiToken;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PlatinumPlace\LaravelDgii\Facades\Dgii;

class DgiiAuthService
{
    /**
     * Get a valid access token for the given company ID.
     *
     * @param string $companyId
     * @return string
     * @throws \Exception
     */
    public function getToken(string $companyId): string
    {
        $company = Company::findOrFail($companyId);

        $storedToken = $company->dgiiTokens()
            ->where('expires_at', '>', now()->addMinutes(5))
            ->latest()
            ->first();

        if ($storedToken) {
            return $storedToken->token;
        }

        // 2. Si no existe o expiró, iniciar flujo de autenticación con la DGII
        return $this->requestNewToken($company);
    }

    /**
     * Perform the Seed -> Sign -> Verify flow to obtain a new JWT token.
     *
     * @param Company $company
     * @return string
     * @throws \Exception
     */
    protected function requestNewToken(Company $company): string
    {
        if (!$company->certificate || !$company->cert_password) {
            throw new \Exception("La empresa no tiene configurado un certificado digital o contraseña.");
        }

        $env = $company->environment;

        // A. Obtener Semilla (Seed)
        $seedXml = Dgii::getSeed($env);

        // B. Firmar la semilla usando el certificado de la empresa
        // El paquete requiere el contenido del certificado P12
        $certContent = Storage::get($company->certificate);
        $certPassword = $company->cert_password;

        // Firmamos la semilla (render invoice se usa para XMLs generales o específicos de firma)
        // Según el flujo del paquete, firmamos el XML de la semilla.
        $signedSeedXml = Dgii::signXmlContent($certContent, $certPassword, $seedXml);

        // Guardamos temporalmente para verificar (el paquete suele pedir un path o content)
        $tempPath = storage_path('app/temp_signed_seed_' . Str::uuid() . '.xml');
        file_put_contents($tempPath, $signedSeedXml);

        try {
            // C. Verificar Semilla y obtener Token
            $authInfo = Dgii::verifySeed($env, $tempPath);

            // 3. Guardar el nuevo token en la base de datos
            // El token suele durar 1 hora (3600 seg), ajustamos expires_at
            $token = $company->dgiiTokens()->create([
                'token' => $authInfo['token'],
                'expires_at' => now()->addSeconds($authInfo['expires_in'] ?? 3600),
            ]);

            return $token->token;

        } finally {
            // Limpiar archivo temporal
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }
}
