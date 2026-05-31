<?php

namespace App\Services;

use App\Models\Company;
use App\Models\DgiiToken;
use Illuminate\Support\Facades\Storage;
use PlatinumPlace\LaravelDgii\Facades\Dgii;

class DgiiService
{
    /**
     * Get a valid access token for the given company.
     *
     * @param Company $company
     * @return string
     * @throws \Exception
     */
    public function getAccessToken(Company $company): string
    {
        // 1. Check if we have a valid token in the database
        $storedToken = $company->dgiiTokens()
            ->where('expires_at', '>', now()->addMinutes(5))
            ->latest()
            ->first();

        if ($storedToken) {
            return $storedToken->token;
        }

        // 2. Request a new token from DGII
        $certPath = Storage::path($company->certificate);
        $certPassword = $company->cert_password;
        $env = $company->environment;

        // Step A: Get Seed
        $seedXml = Dgii::getSeed($env);

        // Step B: Sign Seed (The package expects cert content or path depending on version, 
        // usually it provides a way to sign using the cert info)
        // Note: We follow the workflow in PROJECT_GUIDE.md
        
        // We assume we have a helper to sign the seed XML
        $signedSeedPath = $this->signSeed($seedXml, $certPath, $certPassword);

        // Step C: Verify Seed and Get Token
        $authInfo = Dgii::verifySeed($env, $signedSeedPath);

        // 3. Store and return the token
        $company->dgiiTokens()->create([
            'token' => $authInfo['token'],
            'expires_at' => now()->addSeconds($authInfo['expires_in'] ?? 3600),
        ]);

        return $authInfo['token'];
    }

    /**
     * Sign the seed XML using the company's certificate.
     */
    protected function signSeed(string $seedXml, string $certPath, string $certPassword): string
    {
        $tempSeedPath = storage_path('app/temp_seed_' . uniqid() . '.xml');
        file_put_contents($tempSeedPath, $seedXml);

        $signedPath = storage_path('app/signed_seed_' . uniqid() . '.xml');

        // Use the package to sign the file
        Dgii::signXml($certPath, $certPassword, $tempSeedPath, $signedPath);

        unlink($tempSeedPath);

        return $signedPath;
    }
}
