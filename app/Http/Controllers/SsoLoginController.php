<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SsoLoginController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sso_token' => ['required', 'string'],
        ]);

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->withHeaders([
                    'X-Portal-Client' => config('services.portal.client_key'),
                    'X-Portal-Secret' => config('services.portal.shared_secret'),
                ])
                ->post(rtrim(config('services.portal.base_url'), '/').'/api/sso/consume', [
                    'token' => $validated['sso_token'],
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            report($exception);

            return redirect('/admin/login')
                ->withErrors([
                    'sso' => 'Nao foi possivel validar o acesso pelo portal.',
                ]);
        }

        $portalUser = data_get($response, 'user');

        if (! is_array($portalUser) || blank($portalUser['central_user_id'] ?? null) || blank($portalUser['email'] ?? null)) {
            return redirect('/admin/login')
                ->withErrors([
                    'sso' => 'Resposta SSO invalida.',
                ]);
        }

        $user = User::query()
            ->where('central_user_id', $portalUser['central_user_id'])
            ->orWhere('email', $portalUser['email'])
            ->first();

        if (! $user) {
            $user = new User();
            $user->password = Str::password(32);
            $user->perfil = 'coordenador_polo';
            $user->ativo = true;
        }

        $user->fill([
            'central_user_id' => $portalUser['central_user_id'],
            'name' => $portalUser['name'],
            'email' => $portalUser['email'],
        ]);

        if (($portalUser['is_admin'] ?? false) === true) {
            $user->perfil = 'super_admin';
        } elseif (blank($user->perfil)) {
            $user->perfil = 'coordenador_polo';
        }

        $user->ativo = true;
        $user->save();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }
}
