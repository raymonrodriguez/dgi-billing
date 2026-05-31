<?php

namespace App\Services\DGII;

use Exception;
use DOMDocument;

class EcfSigningService
{
    /**
     * Firma digitalmente un string XML siguiendo el estándar XMLDSIG exigido por la DGII.
     */
    public function signXml(string $xmlContent, string $p12Path, string $password): string
    {
        if (!file_exists($p12Path)) {
            throw new \RuntimeException("Archivo de certificado digital no encontrado en la ruta indicada.");
        }

        $p12Cert = file_get_contents($p12Path);
        $certs = [];

        if (!openssl_pkcs12_read($p12Cert, $certs, $password)) {
            throw new Exception("No se pudo leer el certificado digital P12. Verifique la contraseña.");
        }

        $privateKey = $certs['pkey'];
        $publicKey = $certs['cert'];

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = true;
        $doc->formatOutput = false;
        $doc->loadXML($xmlContent);

        return $this->executeXmlDsig($doc, $privateKey, $publicKey);
    }

    protected function executeXmlDsig(DOMDocument $doc, string $privateKey, string $publicKey): string
    {
        // En un escenario real, aquí se inyectaría la firma XMLDSIG.
        // Por ahora retornamos el XML base según la plantilla.
        return $doc->saveXML();
    }
}
