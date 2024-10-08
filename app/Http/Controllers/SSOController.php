<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class SSOController extends Controller
{
    public function redirect(Request $request)
    {
        $request->session()->put('state', $state = str()->random(40));

        $query = http_build_query([
            'client_id' => env('LARAVEL_HOST_CLIENT_ID'),
            'redirect_uri' => route('sso.callback'),
            'response_type' => 'code',
            'scope' => 'view-user',
            'state' => $state,
        ]);

        return redirect(env('LARAVEL_HOST_AUTHORIZE') . '?' . $query);
    }

    public function callback(Request $request)
    {
        $state = $request->session()->pull('state');

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class,
            'Invalid state value.'
        );

        $response = Http::asForm()->post(env('LARAVEL_HOST_REQUEST_TOKEN'), [
            'grant_type' => 'authorization_code',
            'client_id' => env('LARAVEL_HOST_CLIENT_ID'),
            'client_secret' => env('LARAVEL_HOST_CLIENT_SECRET'),
            'redirect_uri' => route('sso.callback'),
            'code' => $request->code,
        ]);

        $request->session()->put($response->json());
        return redirect()->route('sso.authenticate');
    }

    public function authenticate(Request $request)
    {
        $token_type = $request->session()->get('token_type');
        $access_token = $request->session()->get('access_token');

        $response = Http::withHeaders([
            "accept" => "application/json",
            "Authorization" => "$token_type $access_token",
        ])->get(env('LARAVEL_HOST_GET_USER'));

        $getUser = $response->object();

        $checkUser = User::where('email', $getUser->email)->first();

        if ($checkUser) {
            // Update user data
            $checkUser->update([
                'provider_id' => $getUser->id,
                'access_token' => $access_token,
            ]);

            // Login user
            auth()->login($checkUser);
        } else {
            $user = User::create([
                'name' => $getUser->name,
                'email' => $getUser->email,
                'email_verified_at' => $getUser->email_verified_at,
                'provider' => 'LARAVEL_HOST',
                'provider_id' => $getUser->id,
                'access_token' => $access_token,
            ]);

            // Login user
            auth()->login($user);
        }

        return redirect()->route('home');
    }
}
