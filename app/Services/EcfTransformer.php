<?php

namespace App\Services;

use App\Models\Ecf;
use App\Models\EcfItem;
use App\Enums\AdditionalTaxCode;
use Illuminate\Support\Collection;

class EcfTransformer
{
    /**
     * Transform an Ecf model into the array structure required by Dgii::renderInvoice().
     *
     * @param Ecf $ecf
     * @return array
     */
    public function transform(Ecf $ecf): array
    {
        $ecf->load(['company', 'contact', 'items', 'taxes', 'payments']);

        return [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoeCF' => $ecf->type,
                    'eNCF' => $ecf->encf,
                    'FechaEmision' => $ecf->issued_at->format('d-m-Y'),
                ],
                'Emisor' => [
                    'RNCEmisor' => $ecf->company->tax_id,
                    'RazonSocialEmisor' => $ecf->company->company_name,
                    'NombreComercial' => $ecf->company->trade_name,
                    'DireccionEmisor' => $ecf->company->address ?? 'Santo Domingo, RD',
                ],
                'Receptor' => [
                    'RNCReceptor' => $ecf->contact->tax_id,
                    'RazonSocialReceptor' => $ecf->contact->name,
                    'ContactoReceptor' => $ecf->contact->email,
                    'DireccionReceptor' => $ecf->contact->address,
                ],
                'Totales' => [
                    'MontoTotal' => (float) $ecf->total_amount,
                ],
            ],
            'DetallesItems' => $this->transformItems($ecf->items),
            'SubTotalesVerticales' => $this->transformTaxes($ecf->taxes),
            'Pagos' => $this->transformPayments($ecf->payments),
            'Paginacion' => [
                'Pagina' => 1,
                'TotalPaginas' => 1,
            ],
        ];
    }

    /**
     * Map line items to the DGII detail structure.
     */
    protected function transformItems(Collection $items): array
    {
        return $items->map(function (EcfItem $item, $index) {
            $itemData = [
                'NumeroLinea' => $index + 1,
                'IndicadorFacturacion' => $item->billing_indicator->value, 
                'NombreItem' => $item->description,
                'CantidadItem' => (float) $item->quantity,
                'PrecioUnitarioItem' => (float) $item->price,
                'DescuentoMonto' => (float) $item->discount,
                'MontoItem' => (float) $item->subtotal,
            ];

            // Si tiene impuestos adicionales, construir la estructura TablaImpuestoAdicional
            if (!empty($item->additional_taxes)) {
                $itemData['TablaImpuestoAdicional'] = collect($item->additional_taxes)->map(function ($taxCode) use ($item) {
                    return [
                        'TipoImpuesto' => $taxCode,
                        'TasaImpuesto' => $this->getAdditionalTaxRate($taxCode),
                        'MontoImpuesto' => $this->calculateAdditionalTax($item->subtotal, $taxCode),
                    ];
                })->toArray();
            }

            return $itemData;
        })->toArray();
    }

    /**
     * Get the fixed rate for an additional tax code.
     */
    protected function getAdditionalTaxRate(string $code): float
    {
        return match ($code) {
            '001' => 10.00, // Propina Legal
            '002' => 2.00,  // CDT
            '003' => 16.00, // Seguros
            default => 0.00,
        };
    }

    /**
     * Calculate additional tax amount based on subtotal and code.
     */
    protected function calculateAdditionalTax(float $subtotal, string $code): float
    {
        return round($subtotal * ($this->getAdditionalTaxRate($code) / 100), 2);
    }

    /**
     * Map taxes to the vertical subtotals structure.
     */
    protected function transformTaxes(Collection $taxes): array
    {
        return $taxes->map(function ($tax) {
            return [
                'IndicadorImpuesto' => 1, // 1 = ITBIS
                'TasaImpuesto' => (float) $tax->rate,
                'MontoImpuesto' => (float) $tax->amount,
            ];
        })->toArray();
    }

    /**
     * Map payments to the DGII payment structure.
     */
    protected function transformPayments(Collection $payments): array
    {
        return $payments->map(function ($payment) {
            return [
                'FormaPago' => $payment->method,
                'MontoPago' => (float) $payment->amount,
            ];
        })->toArray();
    }
}
