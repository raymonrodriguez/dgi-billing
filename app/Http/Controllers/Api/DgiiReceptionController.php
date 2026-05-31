<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Ecf;
use App\Models\ReceivedEcf;
use App\Enums\CommercialApprovalStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PlatinumPlace\LaravelDgii\Facades\Dgii;

class DgiiReceptionController extends Controller
{
    /**
     * Recibir e-CF de un suplidor y retornar el Acuse de Recibo (ARECF) firmado.
     */
    public function receiveEcf(Request $request)
    {
        if (!$request->has('xml')) {
            return response()->json(['error' => 'El parámetro xml es requerido'], 400);
        }

        $xmlContent = $request->input('xml');

        try {
            $xml = simplexml_load_string($xmlContent);
            if (!$xml) {
                return response()->json(['error' => 'XML inválido'], 400);
            }

            $rncEmisor = (string) ($xml->Encabezado->Emisor->RNCEmisor ?? '');
            $rncComprador = (string) ($xml->Encabezado->Receptor->RNCReceptor ?? '');
            $encf = (string) ($xml->Encabezado->IdDoc->eNCF ?? '');
            $montoTotal = (float) ($xml->Encabezado->Totales->MontoTotal ?? 0);

            $company = Company::where('tax_id', $rncComprador)->first();

            if (!$company) {
                return response()->json(['error' => 'Receptor no registrado'], 404);
            }

            $certContent = Storage::get($company->certificate);
            $certPassword = $company->cert_password;

            $xmlFirmado = Dgii::renderAcknowledgment(
                $certContent, 
                $certPassword, 
                $rncEmisor, 
                $rncComprador, 
                $encf, 
                '0'
            );

            $path = "received_ecfs/{$company->tax_id}/{$encf}_" . now()->timestamp . ".xml";
            Storage::put($path, $xmlContent);

            ReceivedEcf::create([
                'company_id' => $company->id,
                'rnc_emisor' => $rncEmisor,
                'encf' => $encf,
                'total_amount' => $montoTotal,
                'received_xml_path' => $path,
            ]);

            return response($xmlFirmado, 200)->header('Content-Type', 'text/xml');

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Recibir Aprobación Comercial (ACECF) de un receptor externo.
     */
    public function receiveCommercialApproval(Request $request)
    {
        $xmlContent = $request->input('xml') ?: $request->getContent();

        if (empty($xmlContent)) {
            return response()->json(['error' => 'XML vacío'], 400);
        }

        try {
            $xml = simplexml_load_string($xmlContent);
            if (!$xml) return response()->json(['error' => 'XML inválido'], 400);

            $encf = (string) ($xml->AprobacionComercial->eNCF ?? ''); 
            $rawStatus = (string) ($xml->AprobacionComercial->Estado ?? '1'); 
            $status = ($rawStatus === '1') ? CommercialApprovalStatus::APROBADO : CommercialApprovalStatus::RECHAZADO;

            $ecf = Ecf::where('encf', $encf)->first();

            if ($ecf) {
                $ecf->update(['commercial_approval_status' => $status]);
            }

            return response()->json(['message' => 'Aprobación comercial procesada'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
