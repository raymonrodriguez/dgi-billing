<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ReceivedEcf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EcfReceiverController extends Controller
{
    /**
     * Endpoint para recibir e-CF de suplidores.
     */
    public function receive(Request $request)
    {
        $xmlContent = $request->getContent();

        if (empty($xmlContent)) {
            return response()->json(['error' => 'XML vacío'], 400);
        }

        try {
            // 1. Parsear XML básico para identificar al receptor y emisor
            // Usamos simplexml para una lectura rápida
            $xml = simplexml_load_string($xmlContent);
            
            // Estructura DGII: Encabezado -> Receptor -> RNCReceptor
            $rncReceptor = (string) ($xml->Encabezado->Receptor->RNCReceptor ?? '');
            $rncEmisor = (string) ($xml->Encabezado->Emisor->RNCEmisor ?? '');
            $encf = (string) ($xml->Encabezado->IdDoc->eNCF ?? '');
            $montoTotal = (float) ($xml->Encabezado->Totales->MontoTotal ?? 0);

            // 2. Buscar la empresa (Tenant) en nuestra base de datos
            $company = Company::where('tax_id', $rncReceptor)->first();

            if (!$company) {
                return response()->json(['error' => 'Receptor no registrado en nuestra plataforma'], 404);
            }

            // 3. Guardar el XML en el Storage
            $fileName = "received_ecfs/{$company->tax_id}/{$encf}_" . Str::random(5) . ".xml";
            Storage::put($fileName, $xmlContent);

            // 4. Registrar en la base de datos
            $receivedEcf = ReceivedEcf::create([
                'company_id' => $company->id,
                'rnc_emisor' => $rncEmisor,
                'encf' => $encf,
                'total_amount' => $montoTotal,
                'received_xml_path' => $fileName,
                'arecf_sent' => false,
                'acecf_sent' => false,
            ]);

            // 5. TODO: Despachar Job para enviar el ARECF (Acuse de Recibo)
            // SendArecfJob::dispatch($receivedEcf->id);

            return response()->json([
                'message' => 'e-CF recibido correctamente',
                'track_id' => Str::uuid(), // Opcional: ID interno de seguimiento
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar el XML: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint para recibir aprobaciones comerciales.
     */
    public function commercialApproval(Request $request)
    {
        // Lógica similar para procesar el ACECF recibido
        return response()->json(['message' => 'Aprobación comercial recibida'], 200);
    }
}
