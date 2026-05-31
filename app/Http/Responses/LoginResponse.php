<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Si el usuario tiene al menos una empresa, redirigir a la primera
            if ($user->companies()->exists()) {
                $firstCompany = $user->companies()->first();
                return redirect()->route('filament.admin.pages.dashboard', ['tenant' => $firstCompany->id]);
            }
        }

        // Si no tiene empresas o algo falla, usar la lógica por defecto de Filament
        return parent::toResponse($request);
    }
}
